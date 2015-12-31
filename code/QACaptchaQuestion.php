<?php

class QACaptchaQuestion extends DataObject
{
    public static $db = array(
        'Question' => 'Varchar(1024)',
        'Answers' => 'Text',
        'WrongAnswers' => 'Int',
        'CorrectAnswers' => 'Int'
    );

    public static $summary_fields = array(
        'Question',
        'Answers',
        'WrongAnswers',
        'CorrectAnswers'
    );

    public static $searchable_fields = array(
        'Question',
        'Answers'
    );

    public static $singular_name = 'Question';
    public static $plural_name = 'Questions';

    /**
     * Check the answer against this question.
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

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('WrongAnswers');
        $fields->removeByName('CorrectAnswers');

        $fields->addFieldToTab('Root.Main', new TextAreaField('Answers', 'Answers (comma separated, case and space insensitive)'));
        $fields->addFieldToTab('Root.Statistics', new ReadonlyField('WrongAnswers', 'Wrong answers count'));
        $fields->addFieldToTab('Root.Statistics', new ReadonlyField('CorrectAnswers', 'Correct answers count'));

        return $fields;
    }
}
