<?php
namespace Mateusz\QACaptcha\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\TextareaField;

class QACaptchaQuestion extends DataObject
{
    /**
     * @var array
     */
    private static $db = [
        'Question' => 'Varchar(1024)',
        'Answers' => 'Text',
        'WrongAnswers' => 'Int',
        'CorrectAnswers' => 'Int',
    ];

    /**
     * @var string
     */
    private static $table_name = "QACaptchaQuestion";

    /**
     * @var array
     */
    private static $summary_fields = array(
        'Question',
        'Answers',
        'WrongAnswers',
        'CorrectAnswers'
    );

    /**
     * @var array
     */
    private static $searchable_fields = array(
        'Question',
        'Answers'
    );

    /**
     * @var string
     */
    private static $singular_name = 'Question';

    /**
     * @var string
     */
    private static $plural_name = 'Questions';

    /**
     * Check the answer against this question.
     *
     * @param string $answerToCheck
     * @return boolean
     */
    public function checkAnswer($answerToCheck)
    {
        // Normalise answer
        $answerToCheck = preg_replace('/\s+/', ' ', strtolower(trim($answerToCheck)));

        if ($answerToCheck==='') {
            $this->WrongAnswers++;
            $this->write();
            return false;
        }

        // Compare with stored answers
        $answers = explode(",", $this->Answers);
        foreach ($answers as $answer) {
            if ($answer==$answerToCheck) {
                $this->CorrectAnswers++;
                $this->write();
                return true;
            }
        }

        $this->WrongAnswers++;
        $this->write();
        return false;
    }

    /**
     * Parse the answers to ignore case and spaces.
     *
     * @return void
     */
    public function onBeforeWrite()
    {
        $parsedAnswers = array();

        $answers = explode(",", $this->Answers);
        foreach ($answers as $answer) {
            $parsedAnswer = preg_replace('/\s+/', ' ', strtolower(trim($answer)));
            if ($parsedAnswer) {
                $parsedAnswers[] = $parsedAnswer;
            }
        }

        $this->Answers = implode(',', $parsedAnswers);

        return parent::onBeforeWrite();
    }

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('WrongAnswers');
        $fields->removeByName('CorrectAnswers');

        $fields->addFieldToTab('Root.Main', new TextareaField('Answers', 'Answers (comma separated, case and space insensitive)'));
        $fields->addFieldToTab('Root.Statistics', new ReadonlyField('WrongAnswers', 'Wrong answers count'));
        $fields->addFieldToTab('Root.Statistics', new ReadonlyField('CorrectAnswers', 'Correct answers count'));

        return $fields;
    }
}
