<?php











namespace Composer\Command;

use Composer\DependencyResolver\Pool;
use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Composer\Repository\ArrayRepository;
use Composer\Repository\CompositeRepository;
use Composer\Repository\PlatformRepository;
use Composer\Plugin\CommandEvent;
use Composer\Plugin\PluginEvents;
use Composer\Package\Version\VersionParser;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;






class BaseDependencyCommand extends BaseCommand
{
const ARGUMENT_PACKAGE = 'package';
const ARGUMENT_CONSTRAINT = 'constraint';
const OPTION_RECURSIVE = 'recursive';
const OPTION_TREE = 'tree';




protected function configure()
{
$this->setDefinition(array(
new InputArgument(self::ARGUMENT_PACKAGE, InputArgument::REQUIRED, 'Package to inspect'),
new InputArgument(self::ARGUMENT_CONSTRAINT, InputArgument::OPTIONAL, 'Optional version constraint', '*'),
new InputOption(self::OPTION_RECURSIVE, 'r', InputOption::VALUE_NONE, 'Recursively resolves up to the root package'),
new InputOption(self::OPTION_TREE, 't', InputOption::VALUE_NONE, 'Prints the results as a nested tree'),
));
}









protected function doExecute(InputInterface $input, OutputInterface $output, $inverted = false)
{

 $composer = $this->getComposer();
$commandEvent = new CommandEvent(PluginEvents::COMMAND, $this->getName(), $input, $output);
$composer->getEventDispatcher()->dispatch($commandEvent->getName(), $commandEvent);


 $platformOverrides = $composer->getConfig()->get('platform') ?: array();
$repository = new CompositeRepository(array(
new ArrayRepository(array($composer->getPackage())),
$composer->getRepositoryManager()->getLocalRepository(),
new PlatformRepository(array(), $platformOverrides),
));
$pool = new Pool();
$pool->addRepository($repository);


 list($needle, $textConstraint) = array_pad(
explode(':', $input->getArgument(self::ARGUMENT_PACKAGE)),
2,
$input->getArgument(self::ARGUMENT_CONSTRAINT)
);


 $packages = $pool->whatProvides($needle);
if (empty($packages)) {
throw new \InvalidArgumentException(sprintf('Could not find package "%s" in your project', $needle));
}


 $needles = array($needle);
if ($inverted) {
foreach ($packages as $package) {
$needles = array_merge($needles, array_map(function (Link $link) {
return $link->getTarget();
}, $package->getReplaces()));
}
}


 if ('*' !== $textConstraint) {
$versionParser = new VersionParser();
$constraint = $versionParser->parseConstraints($textConstraint);
} else {
$constraint = null;
}


 $renderTree = $input->getOption(self::OPTION_TREE);
$recursive = $renderTree || $input->getOption(self::OPTION_RECURSIVE);


 $results = $repository->getDependents($needles, $constraint, $inverted, $recursive);
if (empty($results)) {
$extra = (null !== $constraint) ? sprintf(' in versions %smatching %s', $inverted ? 'not ' : '', $textConstraint) : '';
$this->getIO()->writeError(sprintf('<info>There is no installed package depending on "%s"%s</info>',
$needle, $extra));
} elseif ($renderTree) {
$root = $packages[0];
$this->getIO()->write(sprintf('<info>%s</info> %s %s', $root->getPrettyName(), $root->getPrettyVersion(), $root->getDescription()));
$this->printTree($results);
} else {
$this->printTable($output, $results);
}

return 0;
}







protected function printTable(OutputInterface $output, $results)
{
$table = array();
$doubles = array();
do {
$queue = array();
$rows = array();
foreach ($results as $result) {




list($package, $link, $children) = $result;
$unique = (string) $link;
if (isset($doubles[$unique])) {
continue;
}
$doubles[$unique] = true;
$version = (strpos($package->getPrettyVersion(), 'No version set') === 0) ? '-' : $package->getPrettyVersion();
$rows[] = array($package->getPrettyName(), $version, $link->getDescription(), sprintf('%s (%s)', $link->getTarget(), $link->getPrettyConstraint()));
$queue = array_merge($queue, $children);
}
$results = $queue;
$table = array_merge($rows, $table);
} while (!empty($results));


 $renderer = new Table($output);
$renderer->setStyle('compact');
$renderer->getStyle()->setVerticalBorderChar('');
$renderer->getStyle()->setCellRowContentFormat('%s  ');
$renderer->setRows($table)->render();
}







protected function printTree($results, $prefix = '')
{
$count = count($results);
$idx = 0;
foreach ($results as $key => $result) {




list($package, $link, $children) = $result;
$isLast = (++$idx == $count);
$versionText = (strpos($package->getPrettyVersion(), 'No version set') === 0) ? '' : $package->getPrettyVersion();
$packageText = rtrim(sprintf('%s %s', $package->getPrettyName(), $versionText));
$linkText = implode(' ', array($link->getDescription(), $link->getTarget(), $link->getPrettyConstraint()));
$this->writeTreeLine(sprintf("%s%s%s (%s)", $prefix, $isLast ? '└──' : '├──', $packageText, $linkText));
$this->printTree($children, $prefix . ($isLast ? '   ' : '│  '));
}
}

private function writeTreeLine($line)
{
$io = $this->getIO();
if (!$io->isDecorated()) {
$line = str_replace(array('└', '├', '──', '│'), array('`-', '|-', '-', '|'), $line);
}

$io->write($line);
}
}
