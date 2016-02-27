<?php
/**
 * @package   ImpressPages
 */


/**
 * Created by PhpStorm.
 * User: maskas
 * Date: 16.2.26
 * Time: 16.29
 */

namespace Plugin\Composer;

use Composer\Console\Application;
use Composer\Installer;
use Ip\Form;
use Ip\Form\Field;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;


class AdminController
{
    public function index()
    {
        ipAddCss('assets/php.css');
        ipAddCss('assets/codeEditorField.css');
        ipAddJs('assets/src-noconflict/ace.js');
        ipAddJs('assets/initCodeEditorField.js');

        $form = new Form();
        $form->addClass('composerConfigForm');
        $field = new CodeEditor([
            'name' => 'rawCode',
            'layout' => Field::LAYOUT_NO_LABEL,
            'value' => '',
            'mode' => 'json',
            'css' => 'ipPluginPhp-editor',
        ]);
        $form->addField($field);

        $params = [
            'form' => $form
        ];
        return ipView('view/admin.php', $params);
    }



}

