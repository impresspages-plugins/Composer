<?php
/**
 * @package   ImpressPages
 */


/**
 * Created by PhpStorm.
 * User: maskas
 * Date: 16.2.27
 * Time: 18.31
 */

namespace Plugin\Composer;


use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

class Model
{
    protected $dir;
    protected $composerFile;
    public function __construct()
    {
        $this->dir = ipFile('file/secure/Composer/');
        $this->composerFile = $this->dir . 'composer.json';
    }

    public function init()
    {
        if (!is_dir($this->dir)) {
            mkdir($this->dir);
        }
        if (!is_file($this->composerFile)) {
            $this->setConfig("{\n\n}");
        }
    }

    public function setConfig($configuration)
    {
        $f = fopen($this->composerFile, "w");
        fwrite($f, $configuration);
        fclose($f);
    }

    public function getConfig()
    {
        if (!is_file($this->composerFile)) {
            return '';
        }
        return file_get_contents($this->composerFile);
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
        putenv('COMPOSER_HOME=' . ipFile('file/secure/Composer') . '');
        chdir(ipFile('file/secure/Composer'));
        $stream = fopen('php://temp', 'w+');
        $output = new StreamOutput($stream);
        $application = new Application();
        $application->setAutoExit(false);
        $input = new ArrayInput(array('command' => $command));
        $code = $application->run($input, $output);
        rewind($stream);
        return stream_get_contents($stream);
    }
}
