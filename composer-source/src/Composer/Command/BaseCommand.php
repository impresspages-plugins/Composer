<?php











namespace Composer\Command;

use Composer\Composer;
use Composer\Console\Application;
use Composer\IO\IOInterface;
use Composer\IO\NullIO;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;







abstract class BaseCommand extends Command
{



private $composer;




private $io;







public function getComposer($required = true, $disablePlugins = false)
{
if (null === $this->composer) {
$application = $this->getApplication();
if ($application instanceof Application) {

$this->composer = $application->getComposer($required, $disablePlugins);
} elseif ($required) {
throw new \RuntimeException(
'Could not create a Composer\Composer instance, you must inject '.
'one if this command is not used with a Composer\Console\Application instance'
);
}
}

return $this->composer;
}




public function setComposer(Composer $composer)
{
$this->composer = $composer;
}




public function resetComposer()
{
$this->composer = null;
$this->getApplication()->resetComposer();
}




public function getIO()
{
if (null === $this->io) {
$application = $this->getApplication();
if ($application instanceof Application) {

$this->io = $application->getIO();
} else {
$this->io = new NullIO();
}
}

return $this->io;
}




public function setIO(IOInterface $io)
{
$this->io = $io;
}




protected function initialize(InputInterface $input, OutputInterface $output)
{
if (true === $input->hasParameterOption(array('--no-ansi')) && $input->hasOption('no-progress')) {
$input->setOption('no-progress', true);
}

parent::initialize($input, $output);
}
}
