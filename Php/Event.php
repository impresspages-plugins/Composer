<?php
/**
 * @package ImpressPages
 *
 */
namespace Plugin\Php;


class Event
{

    public static function ipBeforeController()
    {

        if (ipIsManagementState()) {
            $plugin = explode('.', ipRequest()->getQuery('aa'));

            if ($plugin[0] == 'Pages') {
                ipAddCss('assets/php.css');
                ipAddCss('assets/codeEditorField.css');
                ipAddJs('assets/src-noconflict/ace.js');
                ipAddJs('assets/initCodeEditorField.js');
            }
        }
    }

    public static function ipPageUpdated($data)
    {

        if (ipAdminPermission('Php')) {

            if (!isset($data['rawCode'])) {
                return;
            }

            Model::updateRawCode($data);
        }
    }

    public static function ipBeforeResponseSent($response)
    {

        $page = ipContent()->getCurrentPage();

        if ($page) {
            $rawCode = Model::getRawCode($page->getId());

            if ($rawCode) {
//                ob_start();
                eval($rawCode);
//                $content = ob_get_contents();
//                ob_end_clean();
            }
        }
    }

    // Clean up storage on Page Removal
    public static function ipPageRemoved($pageId)
    {

        Model::removeRawCode($pageId);
    }

}
