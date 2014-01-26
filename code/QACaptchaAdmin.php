<?php
class QACaptchaAdmin extends ModelAdmin {
	
	static $managed_models = array('QACaptchaQuestion');

	static $url_segment = 'qacaptcha';
	
	static $menu_title = 'Captcha Questions';
}