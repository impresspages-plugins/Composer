<?php
/**
 * @package   ImpressPages
 */

namespace Plugin\Composer;
namespace Plugin\Composer;

/**
 * Created by PhpStorm.
 * User: maskas
 * Date: 16.2.26
 * Time: 16.20
 */
class Event
{

    public static function ipInitFinished_1()
    {
        $autoloadFile = ipFile('file/secure/Composer/vendor/autoload.php');
        if (is_file($autoloadFile)) {
            require_once($autoloadFile);
        }
    }
}
