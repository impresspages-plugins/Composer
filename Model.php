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
        putenv('COMPOSER_HOME=' . __DIR__ . '');
        chdir(ipFile('file/Composer'));
        $stream = fopen('php://temp', 'w+');
        $output = new StreamOutput($stream);
        $application = new Application();
        $application->setAutoExit(false);
        $code = $application->run(new ArrayInput(array('command' => $command)), $output);
        rewind($stream);
        return nl2br(stream_get_contents($stream));
    }
}
