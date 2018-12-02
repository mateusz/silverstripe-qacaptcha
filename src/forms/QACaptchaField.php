<?php
namespace Mateusz\QACaptcha\Forms;

use Exception;
use SilverStripe\ORM\DB;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Session;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\Validator;
use SilverStripe\Control\Director;
use SilverStripe\View\Requirements;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use Mateusz\QACaptcha\Models\QACaptchaQuestion;

class QACaptchaField extends FormField
{
    private static $allowed_actions = [
        'otherquestion',
    ];

    /**
     * @return QACaptchaQuestion
     */
    public function getRandomQuestion()
    {
        $controller = Controller::curr();
        $request = $controller->getRequest();
        $session = $request->getSession();

        $qid = $session->get('QACaptchaField.Retry');
        if (isset($qid) && $qid) {
            // Retry the same question
            return DataObject::get_by_id(QACaptchaQuestion::class, $qid);
        }

        // Provide empty answer field - the question has not been answered yet
        $this->setValue('');

        // Get a comma separated list of past questions
        $backlog = $session->get('QACaptchaField.Backlog');
        if (!$backlog) {
            $backlog = array();
        }
        $sqlBacklog = implode($backlog, ', ');

        // Get questions that have not been used before
        $random = DataObject::get(QACaptchaQuestion::class, $sqlBacklog ? "\"QACaptchaQuestion\".\"ID\" NOT IN ($sqlBacklog)" : '', DB::get_conn()->random());
        if (!($random && $random->exists())) {
            // We have ran out of questions - reset the list
            $backlog = array();
            $session->clear('QACaptchaField.Backlog');
            $random = DataObject::get(QACaptchaQuestion::class, '', DB::get_conn()->random());
        }

        if ($random && $random->exists()) {
            $q = $random->First();
            // Add the question to backlog
            $backlog[] = $q->ID;
            $session->set('QACaptchaField.Backlog', $backlog);

            return $q;
        }
    }

    /**
     * @return string|null
     */
    public function getFormName()
    {
        if ($this->form) {
            return $this->form->FormName();
        }

        return null;
    }

    /**
     * @param array $properties
     * @return DBHTMLText
     */
    public function FieldHolder($properties = array())
    {
        Requirements::javascript('mateusz/silverstripe-qacaptcha:client/js/qacaptcha.js');
        return $this->renderWith(static::class);
    }

    /**
     * Skip the question via AJAX.
     *
     * @return DBHTMLText
     */
    public function otherquestion()
    {
        if (Director::is_ajax()) {
            $request = Injector::inst()->get(HTTPRequest::class);
            $session = $request->getSession();
            $session->clear('QACaptchaField.Retry');
            return $this->renderWith(QACaptchaField::class);
        }
    }

    /**
     * Check the answer against the database.
     *
     * @param Validator $validator
     * @return boolean
     */
    public function validate($validator)
    {
        if (!DataObject::get_one(QACaptchaQuestion::class)) {
            // No questions, so no CAPTCHA.
            return true;
        }

        $data = Controller::curr()->request->postVars();
        $request = Injector::inst()->get(HTTPRequest::class);
        $session = $request->getSession();

        try {
            // Basic check for parameters
            if (!isset($data['QACaptchaQuestionID']) || !$data['QACaptchaQuestionID']) {
                throw new Exception('Validation failed');
            }

            // Clean up
            $qid = (int)$data['QACaptchaQuestionID'];
            $answer = Convert::raw2sql($data[$this->getName()]);

            // Verify the answer
            $question = DataObject::get_by_id(QACaptchaQuestion::class, $qid);
            if (!($question && $question->exists()) || !$answer) {
                throw new Exception('Validation failed');
            }

            $correct = $question->checkAnswer($answer);
            if (!$correct) {
                throw new Exception('Validation failed');
            }
        } catch (Exception $e) {
            if ($session->get('QACaptchaField.Retry')) {
                // This was a second try, next time round we will show different question.
                $session->clear('QACaptchaField.Retry');

                $validator->validationError(
                    $this->name,
                    'Please try again with a new question.',
                    'required',
                    false
                );

                $validator->validationError(
                    'global',
                    "Security question response is incorrect - please try again.",
                    'global',
                    false
                );
            } elseif (isset($qid)) {
                // Allow second try with the same question
                $session->set('QACaptchaField.Retry', $qid);

                $validator->validationError(
                    $this->name,
                    'Please check your answer, spelling and resubmit.',
                    'required',
                    false
                );

                $validator->validationError(
                    'global',
                    "Security question response is incorrect - please try again.",
                    'global',
                    false
                );
            }

            return false;
        }

        $session->clear('QACaptchaField.Retry');
        $session->clear('QACaptchaField.Backlog');
        return true;
    }
}
