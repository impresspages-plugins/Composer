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


class AdminController
{

    public function install()
    {
        error_reporting(1);
//        require_once (__DIR__ . '/comopser.phar');
        require_once 'composer-source/vendor/autoload.php';

        // Composer\Factory::getHomeDir() method
        // needs COMPOSER_HOME environment variable set
        putenv('COMPOSER_HOME=' . __DIR__ .'/composer-source/bin/composer');
//        putenv('COMPOSER_HOME=' . __DIR__ . '/');

        // call `composer install` command programmatically
        $input = new ArrayInput(array('command' => 'install'));
        echo $input->getFirstArgument();
        $application = new Application();
        $application->setCatchExceptions(true);
        $application->setAutoExit(false); // prevent `$application->run` method from exitting the script
//        $output = new OutputInterface();
        $output = null;
        $result = $application->run($input, $output);
        echo $result;
        echo $output . 'a';
        echo 'tset';exit;
    }



    public function install2()
    {
        error_reporting(1);
//        require_once (__DIR__ . '/comopser.phar');
        require_once 'phar://' . __DIR__ . '/composer.phar/vendor/autoload.php';

        // Composer\Factory::getHomeDir() method
        // needs COMPOSER_HOME environment variable set
        putenv('COMPOSER_HOME=' . __DIR__ . '/composer.phar/bin/composer');
//        putenv('COMPOSER_HOME=' . __DIR__ . '/');

        // call `composer install` command programmatically
        $input = new ArrayInput(array('command' => 'install'));
        echo $input->getFirstArgument();
        $application = new Application();
        $application->setCatchExceptions(true);
        $application->setAutoExit(false); // prevent `$application->run` method from exitting the script
//        $output = new OutputInterface();
        $output = null;
        $result = $application->run($input, $output);
        echo $result;
        echo $output . 'a';
        echo 'tset';exit;
    }

}
