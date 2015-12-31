<?php

class QACaptchaProtector implements SpamProtector
{
    /**
     * Just provide the field
     */
    public function getFormField($name = "QACaptchaField", $title = "Captcha", $value = null, $form = null, $rightTitle = null)
    {
        return new QACaptchaField($name, $title, $value, $form, $rightTitle);
    }
    
    /**
     * No feedback loop
     */
    public function sendFeedback($object = null, $feedback = "")
    {
        return true;
    }
}
