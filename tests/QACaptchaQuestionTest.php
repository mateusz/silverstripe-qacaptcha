<?php

class QACaptchaQuestionTest extends SapphireTest
{
    public static $fixture_file = 'qacaptcha/tests/QACaptchaQuestionTest.yml';

    public function testAnswer()
    {
        $q = $this->objFromFixture('QACaptchaQuestion', 'question1');
        $this->assertTrue($q->checkAnswer('four'), 'Case is ignored');
        $this->assertTrue($q->checkAnswer('about four'), 'Second answer is picked up');
        $this->assertTrue($q->checkAnswer('about     four'), 'White space is ignored');
        $this->assertFalse($q->checkAnswer(' '), 'Empty space is not accepted as an answer');
        $this->assertEquals($q->CorrectAnswers, 3);
        $this->assertEquals($q->WrongAnswers, 1);
    }

    public function testRandomQuestion()
    {
        $field = new QACaptchaField('qa', 'qa');
        $q = $field->getRandomQuestion();

        // Pretend we want to retry this question
        Session::set('QACaptchaField.Retry', $q->ID);
        // Check we are getting the same question as long as the retry flag is set.
        for ($i = 0; $i<10; $i++) {
            $this->assertEquals($field->getRandomQuestion()->ID, $q->ID);
        }

        // Release the retry flag, check we get the other question
        Session::clear('QACaptchaField.Retry');
        $this->assertNotEquals($field->getRandomQuestion()->ID, $q->ID);
    }
}
