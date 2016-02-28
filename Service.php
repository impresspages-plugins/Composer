<?php
/**
 * @package   ImpressPages
 */


/**
 * Created by PhpStorm.
 * User: maskas
 * Date: 16.2.27
 * Time: 18.34
 */

namespace Plugin\Composer;


class Service
{
    public static function install()
    {
        $model = new Model();
        $model->install();
    }

    public static function clearCache()
    {
        $model = new Model();
        $model->clearCache();
    }

    public static function update()
    {
        $model = new Model();
        $model->update();
    }

    public static function getConfig()
    {
        $model = new Model();
        return $model->getConfig();
    }
}
