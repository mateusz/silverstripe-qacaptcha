<?php
namespace Mateusz\QACaptcha\Admins;

use SilverStripe\Admin\ModelAdmin;
use Mateusz\QACaptcha\Models\QACaptchaQuestion;

class QACaptchaAdmin extends ModelAdmin
{

    /**
     * @var array
     */
    private static $managed_models = [
        QACaptchaQuestion::class,
    ];

    /**
     * @var string
     */
    private static $url_segment = 'qacaptcha';

    /**
     * @var string
     */
    private static $menu_title = 'Captcha Questions';
}
