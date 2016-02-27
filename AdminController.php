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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;


class AdminController
{
    public function index()
    {

    }

    public function clearCache()
    {
        return $this->executeComposer('clear-cache');
    }

    public function install()
    {
        return $this->executeComposer('install');
    }


    public function update()
    {
        return $this->executeComposer('update');
    }


    protected function executeComposer($command)
    {
        //create composer.json with some content
        require_once 'composer-source/vendor/autoload.php';
        putenv('COMPOSER_HOME=' . __DIR__ . '');
        chdir(ipFile('Plugin/Composer'));
        $stream = fopen('php://temp', 'w+');
        $output = new StreamOutput($stream);
        $application = new Application();
        $application->setAutoExit(false);
        $code = $application->run(new ArrayInput(array('command' => $command)), $output);
        rewind($stream);
        return nl2br(stream_get_contents($stream));
    }

}

