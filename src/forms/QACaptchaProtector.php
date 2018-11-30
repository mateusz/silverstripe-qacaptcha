<?php
namespace Mateusz\QACaptcha\Forms;

use SilverStripe\Forms\Form;
use Mateusz\QACaptcha\Forms\QACaptchaField;
use SilverStripe\SpamProtection\SpamProtector;

class QACaptchaProtector implements SpamProtector
{
    /**
     * Just provide the field
     *
     * @param string $name
     * @param string $title
     * @param string $value
     * @param Form $form
     * @param string title
     * @return QACaptchField
     */
    public function getFormField($name = "QACaptchaField", $title = "Captcha", $value = null, $form = null, $rightTitle = null)
    {
        return new QACaptchaField($name, $title, $value, $form, $rightTitle);
    }

    /**
     * No feedback loop
     *
     * @param mixed $object
     * @param string $feedback
     * @return boolean
     */
    public function sendFeedback($object = null, $feedback = "")
    {
        return true;
    }

    public function setFieldMapping($fieldMapping)
    {
        return [];
    }
}
