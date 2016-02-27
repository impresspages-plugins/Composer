<?php
/**
 * @package ImpressPages
 *
 */
namespace Plugin\Php;


class Model
{

    public static function updateRawCode($page)
    {
        ipStorage()->set('Php', $page['id'] . '-rawCode', $page['rawCode']);
    }

    public static function getRawCode($pageId)
    {
        return ipStorage()->get('Php', $pageId . '-rawCode');
    }

    public static function removeRawCode($pageId)
    {
        ipStorage()->remove('Php', $pageId . '-rawCode');
    }

}
