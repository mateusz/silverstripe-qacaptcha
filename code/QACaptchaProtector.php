<?php

class QACaptchaProtector implements SpamProtector {
	/**
	 * Just provide the field
	 */
	function getFormField($name = "QACaptchaField", $title = "Captcha", $value = null, $form = null, $rightTitle = null) {
		return new QACaptchaField($name, $title, $value, $form, $rightTitle);
	}
	
	/**
	 * No feedback loop
	 */
	function sendFeedback($object = null, $feedback = "") {
		return true;
	}
}
