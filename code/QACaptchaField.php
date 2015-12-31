<?php

class QACaptchaField extends SpamProtectorField
{
    public function getRandomQuestion()
    {
        $qid = Session::get('QACaptchaField.Retry');
        if (isset($qid) && $qid) {
            // Retry the same question
            return DataObject::get_by_id('QACaptchaQuestion', $qid);
        }

        // Provide empty answer field - the question has not been answered yet
        $this->setValue('');

        // Get a comma separated list of past questions
        $backlog = Session::get('QACaptchaField.Backlog');
        if (!$backlog) {
            $backlog = array();
        }
        $sqlBacklog = implode($backlog, ', ');

        // Get questions that have not been used before
        $random = DataObject::get('QACaptchaQuestion', $sqlBacklog ? "\"QACaptchaQuestion\".\"ID\" NOT IN ($sqlBacklog)" : '', DB::getConn()->random());
        if (!($random && $random->exists())) {
            // We have ran out of questions - reset the list
            $backlog = array();
            Session::clear('QACaptchaField.Backlog');
            $random = DataObject::get('QACaptchaQuestion', '', DB::getConn()->random());
        }

        if ($random && $random->exists()) {
            $q = $random->First();
            // Add the question to backlog
            $backlog[] = $q->ID;
            Session::set('QACaptchaField.Backlog', $backlog);

            return $q;
        }
    }

    public function getFormName()
    {
        if ($this->form) {
            return $this->form->FormName();
        }
    }

    public function FieldHolder($properties = array())
    {
        Requirements::javascript('qacaptcha/javascript/qacaptcha.js');
        return $this->renderWith('QACaptchaField');
    }

    /**
     * Skip the question via AJAX.
     */
    public function otherquestion()
    {
        if (Director::is_ajax()) {
            Session::clear('QACaptchaField.Retry');
            return $this->renderWith('QACaptchaField');
        }
    }

    /**
     * Check the answer against the database.
     */
    public function validate($validator)
    {
        if (!DataObject::get_one('QACaptchaQuestion')) {
            // No questions, so no CAPTCHA.
            return true;
        }

        $data = Controller::curr()->request->postVars();

        try {
            // Basic check for parameters
            if (!isset($data['QACaptchaQuestionID']) || !$data['QACaptchaQuestionID']) {
                throw new Exception('Validation failed');
            }
            
            // Clean up
            $qid = (int)$data['QACaptchaQuestionID'];
            $answer = Convert::raw2sql($data[$this->name()]);

            // Verify the answer
            $question = DataObject::get_by_id('QACaptchaQuestion', $qid);
            if (!($question && $question->exists()) || !$answer) {
                throw new Exception('Validation failed');
            }

            $correct = $question->checkAnswer($answer);
            if (!$correct) {
                throw new Exception('Validation failed');
            }
        } catch (Exception $e) {
            if (Session::get('QACaptchaField.Retry')) {
                // This was a second try, next time round we will show different question.
                Session::clear('QACaptchaField.Retry');

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
                Session::set('QACaptchaField.Retry', $qid);

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

        Session::clear('QACaptchaField.Retry');
        Session::clear('QACaptchaField.Backlog');
        return true;
    }
}
