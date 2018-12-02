<?php

use SilverStripe\Control\Controller;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use Mateusz\QACaptcha\Forms\QACaptchaField;
use Mateusz\QACaptcha\Models\QACaptchaQuestion;

class QACaptchaQuestionTest extends FunctionalTest
{

    /**
     * @var string
     */
    protected static $fixture_file = './QACaptchaQuestionTest.yml';

    public function testAnswer()
    {
        $q = $this->objFromFixture(QACaptchaQuestion::class, 'question1');
        $this->assertTrue($q->checkAnswer('four'), 'Case is ignored');
        $this->assertTrue($q->checkAnswer('about four'), 'Second answer is picked up');
        $this->assertTrue($q->checkAnswer('about     four'), 'White space is ignored');
        $this->assertFalse($q->checkAnswer(' '), 'Empty space is not accepted as an answer');
        $this->assertEquals($q->CorrectAnswers, 3);
        $this->assertEquals($q->WrongAnswers, 1);
    }

    public function testRandomQuestion()
    {
        $field = QACaptchaField::create('qa', 'qa');
        $q = $field->getRandomQuestion();

        $controller = Controller::curr();
        $request = $controller->getRequest();
        $session = $request->getSession();

        // Pretend we want to retry this question
        $session->set('QACaptchaField.Retry', $q->ID);
        // Check we are getting the same question as long as the retry flag is set.
        for ($i = 0; $i<10; $i++) {
            $this->assertEquals($field->getRandomQuestion()->ID, $q->ID);
        }

        // Release the retry flag, check we get the other question
        $session->clear('QACaptchaField.Retry');
        $this->assertNotEquals($field->getRandomQuestion()->ID, $q->ID);
    }
}
