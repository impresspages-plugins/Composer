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

use Composer\Installer;
use Ip\Form;
use Ip\Form\Field;
use Ip\Response\Json;


class AdminController
{
    public function index()
    {
        ipAddCss('assets/php.css');
        ipAddCss('assets/codeEditorField.css');
        ipAddJs('assets/src-noconflict/ace.js');
        ipAddJs('assets/initCodeEditorField.js');

        ipAddCss('assets/admin.css');
        ipAddJs('assets/admin.js');

        $form = new Form();
        $form->addClass('composerJsonForm');
        $form->addClass('ipsComposerJsonForm');
        $form->addClass('hidden');
        $field = new CodeEditor([
            'name' => 'rawCode',
            'layout' => Field::LAYOUT_NO_LABEL,
            'value' => Service::getConfig(),
            'mode' => 'json',
            'css' => 'ipPluginPhp-editor',
        ]);
        $form->addField($field);

        $params = [
            'composerJson' => Service::getConfig(),
            'form' => $form
        ];
        return ipView('view/admin.php', $params);
    }

    public function saveConfig()
    {
        ipRequest()->mustBePost();
        $config = ipRequest()->getPost('config');
        Service::setConfig($config);
    }


    public function executeComposerCommand()
    {
        ipRequest()->mustBePost();
        $command = ipRequest()->getPost('command');

        switch ($command) {
            case 'install':
                $answer = Service::install();
                break;
            case 'update':
                $answer = Service::update();
                break;
            case 'clearcache':
                $answer = Service::clearCache();
                break;
            case 'version':
                $answer = Service::version();
                break;
            default:
                throw new \Ip\Exception('Unknown Composer command');

        }

        $answer = nl2br(esc($answer));
        $result = [
            'result' => $answer
        ];
        return new Json($result);
    }


}

