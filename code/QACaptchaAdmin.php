<?php
class QACaptchaAdmin extends ModelAdmin
{
    
    public static $managed_models = array('QACaptchaQuestion');

    public static $url_segment = 'qacaptcha';
    
    public static $menu_title = 'Captcha Questions';
}
