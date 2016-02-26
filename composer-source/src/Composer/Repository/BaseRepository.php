<?php











namespace Composer\Repository;

use Composer\Package\RootPackageInterface;
use Composer\Semver\Constraint\ConstraintInterface;






abstract class BaseRepository implements RepositoryInterface
{












public function getDependents($needle, $constraint = null, $invert = false, $recurse = true)
{
$needles = (array) $needle;
$results = array();


 foreach ($this->getPackages() as $package) {
$links = $package->getRequires();


 if (!$invert) {
$links += $package->getReplaces();
}


 if ($package instanceof RootPackageInterface) {
$links += $package->getDevRequires();
}


 foreach ($links as $link) {
foreach ($needles as $needle) {
if ($link->getTarget() === $needle) {
if (is_null($constraint) || (($link->getConstraint()->matches($constraint) === !$invert))) {
$dependents = $recurse ? $this->getDependents($link->getSource(), null, false, true) : array();
$results[$link->getSource()] = array($package, $link, $dependents);
}
}
}
}
}

ksort($results);

return $results;
}
}
