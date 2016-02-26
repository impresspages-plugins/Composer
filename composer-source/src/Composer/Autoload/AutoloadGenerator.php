<?php











namespace Composer\Autoload;

use Composer\Config;
use Composer\EventDispatcher\EventDispatcher;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\Package\AliasPackage;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Util\Filesystem;
use Composer\Script\ScriptEvents;





class AutoloadGenerator
{



private $eventDispatcher;




private $io;




private $devMode = false;




private $classMapAuthoritative = false;




private $runScripts = false;

public function __construct(EventDispatcher $eventDispatcher, IOInterface $io = null)
{
$this->eventDispatcher = $eventDispatcher;
$this->io = $io;
}

public function setDevMode($devMode = true)
{
$this->devMode = (boolean) $devMode;
}







public function setClassMapAuthoritative($classMapAuthoritative)
{
$this->classMapAuthoritative = (boolean) $classMapAuthoritative;
}






public function setRunScripts($runScripts = true)
{
$this->runScripts = (boolean) $runScripts;
}

public function dump(Config $config, InstalledRepositoryInterface $localRepo, PackageInterface $mainPackage, InstallationManager $installationManager, $targetDir, $scanPsr0Packages = false, $suffix = '')
{
if ($this->classMapAuthoritative) {

 $scanPsr0Packages = true;
}
if ($this->runScripts) {
$this->eventDispatcher->dispatchScript(ScriptEvents::PRE_AUTOLOAD_DUMP, $this->devMode, array(), array(
'optimize' => (bool) $scanPsr0Packages,
));
}

$filesystem = new Filesystem();
$filesystem->ensureDirectoryExists($config->get('vendor-dir'));
$basePath = $filesystem->normalizePath(realpath(getcwd()));
$vendorPath = $filesystem->normalizePath(realpath($config->get('vendor-dir')));
$useGlobalIncludePath = (bool) $config->get('use-include-path');
$prependAutoloader = $config->get('prepend-autoloader') === false ? 'false' : 'true';
$targetDir = $vendorPath.'/'.$targetDir;
$filesystem->ensureDirectoryExists($targetDir);

$vendorPathCode = $filesystem->findShortestPathCode(realpath($targetDir), $vendorPath, true);
$vendorPathCode52 = str_replace('__DIR__', 'dirname(__FILE__)', $vendorPathCode);
$vendorPathToTargetDirCode = $filesystem->findShortestPathCode($vendorPath, realpath($targetDir), true);

$appBaseDirCode = $filesystem->findShortestPathCode($vendorPath, $basePath, true);
$appBaseDirCode = str_replace('__DIR__', '$vendorDir', $appBaseDirCode);

$namespacesFile = <<<EOF
<?php

// autoload_namespaces.php @generated by Composer

\$vendorDir = $vendorPathCode52;
\$baseDir = $appBaseDirCode;

return array(

EOF;

$psr4File = <<<EOF
<?php

// autoload_psr4.php @generated by Composer

\$vendorDir = $vendorPathCode52;
\$baseDir = $appBaseDirCode;

return array(

EOF;


 $packageMap = $this->buildPackageMap($installationManager, $mainPackage, $localRepo->getCanonicalPackages());
$autoloads = $this->parseAutoloads($packageMap, $mainPackage);


 foreach ($autoloads['psr-0'] as $namespace => $paths) {
$exportedPaths = array();
foreach ($paths as $path) {
$exportedPaths[] = $this->getPathCode($filesystem, $basePath, $vendorPath, $path);
}
$exportedPrefix = var_export($namespace, true);
$namespacesFile .= "    $exportedPrefix => ";
$namespacesFile .= "array(".implode(', ', $exportedPaths)."),\n";
}
$namespacesFile .= ");\n";


 foreach ($autoloads['psr-4'] as $namespace => $paths) {
$exportedPaths = array();
foreach ($paths as $path) {
$exportedPaths[] = $this->getPathCode($filesystem, $basePath, $vendorPath, $path);
}
$exportedPrefix = var_export($namespace, true);
$psr4File .= "    $exportedPrefix => ";
$psr4File .= "array(".implode(', ', $exportedPaths)."),\n";
}
$psr4File .= ");\n";

$classmapFile = <<<EOF
<?php

// autoload_classmap.php @generated by Composer

\$vendorDir = $vendorPathCode52;
\$baseDir = $appBaseDirCode;

return array(

EOF;


 $targetDirLoader = null;
$mainAutoload = $mainPackage->getAutoload();
if ($mainPackage->getTargetDir() && !empty($mainAutoload['psr-0'])) {
$levels = count(explode('/', $filesystem->normalizePath($mainPackage->getTargetDir())));
$prefixes = implode(', ', array_map(function ($prefix) {
return var_export($prefix, true);
}, array_keys($mainAutoload['psr-0'])));
$baseDirFromTargetDirCode = $filesystem->findShortestPathCode($targetDir, $basePath, true);

$targetDirLoader = <<<EOF

    public static function autoload(\$class)
    {
        \$dir = $baseDirFromTargetDirCode . '/';
        \$prefixes = array($prefixes);
        foreach (\$prefixes as \$prefix) {
            if (0 !== strpos(\$class, \$prefix)) {
                continue;
            }
            \$path = \$dir . implode('/', array_slice(explode('\\\\', \$class), $levels)).'.php';
            if (!\$path = stream_resolve_include_path(\$path)) {
                return false;
            }
            require \$path;

            return true;
        }
    }

EOF;
}

$blacklist = null;
if (!empty($autoloads['exclude-from-classmap'])) {
$blacklist = '{(' . implode('|', $autoloads['exclude-from-classmap']) . ')}';
}


 $classMap = array();
if ($scanPsr0Packages) {
$namespacesToScan = array();


 foreach (array('psr-0', 'psr-4') as $psrType) {
foreach ($autoloads[$psrType] as $namespace => $paths) {
$namespacesToScan[$namespace][] = array('paths' => $paths, 'type' => $psrType);
}
}

krsort($namespacesToScan);

foreach ($namespacesToScan as $namespace => $groups) {
foreach ($groups as $group) {
$psrType = $group['type'];
foreach ($group['paths'] as $dir) {
$dir = $filesystem->normalizePath($filesystem->isAbsolutePath($dir) ? $dir : $basePath.'/'.$dir);
if (!is_dir($dir)) {
continue;
}

$namespaceFilter = $namespace === '' ? null : $namespace;
$classMap = $this->addClassMapCode($filesystem, $basePath, $vendorPath, $dir, $blacklist, $namespaceFilter, $classMap);
}
}
}
}

foreach ($autoloads['classmap'] as $dir) {
$classMap = $this->addClassMapCode($filesystem, $basePath, $vendorPath, $dir, $blacklist, null, $classMap);
}

ksort($classMap);
foreach ($classMap as $class => $code) {
$classmapFile .= '    '.var_export($class, true).' => '.$code;
}
$classmapFile .= ");\n";

if (!$suffix) {
if (!$config->get('autoloader-suffix') && is_readable($vendorPath.'/autoload.php')) {
$content = file_get_contents($vendorPath.'/autoload.php');
if (preg_match('{ComposerAutoloaderInit([^:\s]+)::}', $content, $match)) {
$suffix = $match[1];
}
}

if (!$suffix) {
$suffix = $config->get('autoloader-suffix') ?: md5(uniqid('', true));
}
}

file_put_contents($targetDir.'/autoload_namespaces.php', $namespacesFile);
file_put_contents($targetDir.'/autoload_psr4.php', $psr4File);
file_put_contents($targetDir.'/autoload_classmap.php', $classmapFile);
$includePathFilePath = $targetDir.'/include_paths.php';
if ($includePathFileContents = $this->getIncludePathsFile($packageMap, $filesystem, $basePath, $vendorPath, $vendorPathCode52, $appBaseDirCode)) {
file_put_contents($includePathFilePath, $includePathFileContents);
} elseif (file_exists($includePathFilePath)) {
unlink($includePathFilePath);
}
$includeFilesFilePath = $targetDir.'/autoload_files.php';
if ($includeFilesFileContents = $this->getIncludeFilesFile($autoloads['files'], $filesystem, $basePath, $vendorPath, $vendorPathCode52, $appBaseDirCode)) {
file_put_contents($includeFilesFilePath, $includeFilesFileContents);
} elseif (file_exists($includeFilesFilePath)) {
unlink($includeFilesFilePath);
}
file_put_contents($vendorPath.'/autoload.php', $this->getAutoloadFile($vendorPathToTargetDirCode, $suffix));
file_put_contents($targetDir.'/autoload_real.php', $this->getAutoloadRealFile(true, (bool) $includePathFileContents, $targetDirLoader, (bool) $includeFilesFileContents, $vendorPathCode, $appBaseDirCode, $suffix, $useGlobalIncludePath, $prependAutoloader));

$this->safeCopy(__DIR__.'/ClassLoader.php', $targetDir.'/ClassLoader.php');
$this->safeCopy(__DIR__.'/../../../LICENSE', $targetDir.'/LICENSE');

if ($this->runScripts) {
$this->eventDispatcher->dispatchScript(ScriptEvents::POST_AUTOLOAD_DUMP, $this->devMode, array(), array(
'optimize' => (bool) $scanPsr0Packages,
));
}
}

private function addClassMapCode($filesystem, $basePath, $vendorPath, $dir, $blacklist = null, $namespaceFilter = null, array $classMap = array())
{
foreach ($this->generateClassMap($dir, $blacklist, $namespaceFilter) as $class => $path) {
$pathCode = $this->getPathCode($filesystem, $basePath, $vendorPath, $path).",\n";
if (!isset($classMap[$class])) {
$classMap[$class] = $pathCode;
} elseif ($this->io && $classMap[$class] !== $pathCode && !preg_match('{/(test|fixture|example|stub)s?/}i', strtr($classMap[$class].' '.$path, '\\', '/'))) {
$this->io->writeError(
'<warning>Warning: Ambiguous class resolution, "'.$class.'"'.
' was found in both "'.str_replace(array('$vendorDir . \'', "',\n"), array($vendorPath, ''), $classMap[$class]).'" and "'.$path.'", the first will be used.</warning>'
);
}
}

return $classMap;
}

private function generateClassMap($dir, $blacklist = null, $namespaceFilter = null, $showAmbiguousWarning = true)
{
return ClassMapGenerator::createMap($dir, $blacklist, $showAmbiguousWarning ? $this->io : null, $namespaceFilter);
}

public function buildPackageMap(InstallationManager $installationManager, PackageInterface $mainPackage, array $packages)
{

 $packageMap = array(array($mainPackage, ''));

foreach ($packages as $package) {
if ($package instanceof AliasPackage) {
continue;
}
$this->validatePackage($package);

$packageMap[] = array(
$package,
$installationManager->getInstallPath($package),
);
}

return $packageMap;
}






protected function validatePackage(PackageInterface $package)
{
$autoload = $package->getAutoload();
if (!empty($autoload['psr-4']) && null !== $package->getTargetDir()) {
$name = $package->getName();
$package->getTargetDir();
throw new \InvalidArgumentException("PSR-4 autoloading is incompatible with the target-dir property, remove the target-dir in package '$name'.");
}
if (!empty($autoload['psr-4'])) {
foreach ($autoload['psr-4'] as $namespace => $dirs) {
if ($namespace !== '' && '\\' !== substr($namespace, -1)) {
throw new \InvalidArgumentException("psr-4 namespaces must end with a namespace separator, '$namespace' does not, use '$namespace\\'.");
}
}
}
}








public function parseAutoloads(array $packageMap, PackageInterface $mainPackage)
{
$mainPackageMap = array_shift($packageMap);
$sortedPackageMap = $this->sortPackageMap($packageMap);
$sortedPackageMap[] = $mainPackageMap;
array_unshift($packageMap, $mainPackageMap);

$psr0 = $this->parseAutoloadsType($packageMap, 'psr-0', $mainPackage);
$psr4 = $this->parseAutoloadsType($packageMap, 'psr-4', $mainPackage);
$classmap = $this->parseAutoloadsType(array_reverse($sortedPackageMap), 'classmap', $mainPackage);
$files = $this->parseAutoloadsType($sortedPackageMap, 'files', $mainPackage);
$exclude = $this->parseAutoloadsType($sortedPackageMap, 'exclude-from-classmap', $mainPackage);

krsort($psr0);
krsort($psr4);

return array(
'psr-0' => $psr0,
'psr-4' => $psr4,
'classmap' => $classmap,
'files' => $files,
'exclude-from-classmap' => $exclude,
);
}







public function createLoader(array $autoloads)
{
$loader = new ClassLoader();

if (isset($autoloads['psr-0'])) {
foreach ($autoloads['psr-0'] as $namespace => $path) {
$loader->add($namespace, $path);
}
}

if (isset($autoloads['psr-4'])) {
foreach ($autoloads['psr-4'] as $namespace => $path) {
$loader->addPsr4($namespace, $path);
}
}

if (isset($autoloads['classmap'])) {
foreach ($autoloads['classmap'] as $dir) {
try {
$loader->addClassMap($this->generateClassMap($dir, null, null, false));
} catch (\RuntimeException $e) {
$this->io->writeError('<warning>'.$e->getMessage().'</warning>');
}
}
}

return $loader;
}

protected function getIncludePathsFile(array $packageMap, Filesystem $filesystem, $basePath, $vendorPath, $vendorPathCode, $appBaseDirCode)
{
$includePaths = array();

foreach ($packageMap as $item) {
list($package, $installPath) = $item;

if (null !== $package->getTargetDir() && strlen($package->getTargetDir()) > 0) {
$installPath = substr($installPath, 0, -strlen('/'.$package->getTargetDir()));
}

foreach ($package->getIncludePaths() as $includePath) {
$includePath = trim($includePath, '/');
$includePaths[] = empty($installPath) ? $includePath : $installPath.'/'.$includePath;
}
}

if (!$includePaths) {
return;
}

$includePathsCode = '';
foreach ($includePaths as $path) {
$includePathsCode .= "    " . $this->getPathCode($filesystem, $basePath, $vendorPath, $path) . ",\n";
}

return <<<EOF
<?php

// include_paths.php @generated by Composer

\$vendorDir = $vendorPathCode;
\$baseDir = $appBaseDirCode;

return array(
$includePathsCode);

EOF;
}

protected function getIncludeFilesFile(array $files, Filesystem $filesystem, $basePath, $vendorPath, $vendorPathCode, $appBaseDirCode)
{
$filesCode = '';
foreach ($files as $fileIdentifier => $functionFile) {
$filesCode .= '    ' . var_export($fileIdentifier, true) . ' => '
. $this->getPathCode($filesystem, $basePath, $vendorPath, $functionFile) . ",\n";
}

if (!$filesCode) {
return false;
}

return <<<EOF
<?php

// autoload_files.php @generated by Composer

\$vendorDir = $vendorPathCode;
\$baseDir = $appBaseDirCode;

return array(
$filesCode);

EOF;
}

protected function getPathCode(Filesystem $filesystem, $basePath, $vendorPath, $path)
{
if (!$filesystem->isAbsolutePath($path)) {
$path = $basePath . '/' . $path;
}
$path = $filesystem->normalizePath($path);

$baseDir = '';
if (strpos($path.'/', $vendorPath.'/') === 0) {
$path = substr($path, strlen($vendorPath));
$baseDir = '$vendorDir';

if ($path !== false) {
$baseDir .= " . ";
}
} else {
$path = $filesystem->normalizePath($filesystem->findShortestPath($basePath, $path, true));
if (!$filesystem->isAbsolutePath($path)) {
$baseDir = '$baseDir . ';
$path = '/' . $path;
}
}

if (preg_match('/\.phar$/', $path)) {
$baseDir = "'phar://' . " . $baseDir;
}

return $baseDir . (($path !== false) ? var_export($path, true) : "");
}

protected function getAutoloadFile($vendorPathToTargetDirCode, $suffix)
{
return <<<AUTOLOAD
<?php

// autoload.php @generated by Composer

require_once $vendorPathToTargetDirCode . '/autoload_real.php';

return ComposerAutoloaderInit$suffix::getLoader();

AUTOLOAD;
}

protected function getAutoloadRealFile($useClassMap, $useIncludePath, $targetDirLoader, $useIncludeFiles, $vendorPathCode, $appBaseDirCode, $suffix, $useGlobalIncludePath, $prependAutoloader)
{
$file = <<<HEADER
<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit$suffix
{
    private static \$loader;

    public static function loadClassLoader(\$class)
    {
        if ('Composer\\Autoload\\ClassLoader' === \$class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    public static function getLoader()
    {
        if (null !== self::\$loader) {
            return self::\$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit$suffix', 'loadClassLoader'), true, $prependAutoloader);
        self::\$loader = \$loader = new \\Composer\\Autoload\\ClassLoader();
        spl_autoload_unregister(array('ComposerAutoloaderInit$suffix', 'loadClassLoader'));


HEADER;

if ($useIncludePath) {
$file .= <<<'INCLUDE_PATH'
        $includePaths = require __DIR__ . '/include_paths.php';
        array_push($includePaths, get_include_path());
        set_include_path(join(PATH_SEPARATOR, $includePaths));


INCLUDE_PATH;
}

if (!$this->classMapAuthoritative) {
$file .= <<<'PSR04'
        $map = require __DIR__ . '/autoload_namespaces.php';
        foreach ($map as $namespace => $path) {
            $loader->set($namespace, $path);
        }

        $map = require __DIR__ . '/autoload_psr4.php';
        foreach ($map as $namespace => $path) {
            $loader->setPsr4($namespace, $path);
        }


PSR04;
}

if ($useClassMap) {
$file .= <<<'CLASSMAP'
        $classMap = require __DIR__ . '/autoload_classmap.php';
        if ($classMap) {
            $loader->addClassMap($classMap);
        }


CLASSMAP;
}

if ($this->classMapAuthoritative) {
$file .= <<<'CLASSMAPAUTHORITATIVE'
        $loader->setClassMapAuthoritative(true);

CLASSMAPAUTHORITATIVE;
}

if ($useGlobalIncludePath) {
$file .= <<<'INCLUDEPATH'
        $loader->setUseIncludePath(true);

INCLUDEPATH;
}

if ($targetDirLoader) {
$file .= <<<REGISTER_TARGET_DIR_AUTOLOAD
        spl_autoload_register(array('ComposerAutoloaderInit$suffix', 'autoload'), true, true);


REGISTER_TARGET_DIR_AUTOLOAD;
}

$file .= <<<REGISTER_LOADER
        \$loader->register($prependAutoloader);


REGISTER_LOADER;

if ($useIncludeFiles) {
$file .= <<<INCLUDE_FILES
        \$includeFiles = require __DIR__ . '/autoload_files.php';
        foreach (\$includeFiles as \$fileIdentifier => \$file) {
            composerRequire$suffix(\$fileIdentifier, \$file);
        }


INCLUDE_FILES;
}

$file .= <<<METHOD_FOOTER
        return \$loader;
    }

METHOD_FOOTER;

$file .= $targetDirLoader;

if ($useIncludeFiles) {
return $file . <<<FOOTER
}

function composerRequire$suffix(\$fileIdentifier, \$file)
{
    if (empty(\$GLOBALS['__composer_autoload_files'][\$fileIdentifier])) {
        require \$file;

        \$GLOBALS['__composer_autoload_files'][\$fileIdentifier] = true;
    }
}

FOOTER;
}

return $file . <<<FOOTER
}

FOOTER;
}

protected function parseAutoloadsType(array $packageMap, $type, PackageInterface $mainPackage)
{
$autoloads = array();

foreach ($packageMap as $item) {
list($package, $installPath) = $item;

$autoload = $package->getAutoload();
if ($this->devMode && $package === $mainPackage) {
$autoload = array_merge_recursive($autoload, $package->getDevAutoload());
}


 if (!isset($autoload[$type]) || !is_array($autoload[$type])) {
continue;
}
if (null !== $package->getTargetDir() && $package !== $mainPackage) {
$installPath = substr($installPath, 0, -strlen('/'.$package->getTargetDir()));
}

foreach ($autoload[$type] as $namespace => $paths) {
foreach ((array) $paths as $path) {
if (($type === 'files' || $type === 'classmap' || $type === 'exclude-from-classmap') && $package->getTargetDir() && !is_readable($installPath.'/'.$path)) {

 if ($package === $mainPackage) {
$targetDir = str_replace('\\<dirsep\\>', '[\\\\/]', preg_quote(str_replace(array('/', '\\'), '<dirsep>', $package->getTargetDir())));
$path = ltrim(preg_replace('{^'.$targetDir.'}', '', ltrim($path, '\\/')), '\\/');
} else {

 $path = $package->getTargetDir() . '/' . $path;
}
}

if ($type === 'exclude-from-classmap') {

 $path = preg_quote(trim(strtr($path, '\\', '/'), '/'));


 $path = str_replace('\\*\\*', '.+?', $path);
$path = str_replace('\\*', '[^/]+?', $path);


 $updir = null;
$path = preg_replace_callback(
'{^((?:(?:\\\\\\.){1,2}+/)+)}',
function ($matches) use (&$updir) {
if (isset($matches[1])) {

 $updir = str_replace('\\.', '.', $matches[1]);
}

return '';
},
$path
);
if (empty($installPath)) {
$installPath = strtr(getcwd(), '\\', '/');
}

$resolvedPath = realpath($installPath . '/' . $updir);
$autoloads[] = preg_quote(strtr($resolvedPath, '\\', '/')) . '/' . $path;
continue;
}

$relativePath = empty($installPath) ? (empty($path) ? '.' : $path) : $installPath.'/'.$path;

if ($type === 'files') {
$autoloads[$this->getFileIdentifier($package, $path)] = $relativePath;
continue;
} elseif ($type === 'classmap') {
$autoloads[] = $relativePath;
continue;
}

$autoloads[$namespace][] = $relativePath;
}
}
}

return $autoloads;
}

protected function getFileIdentifier(PackageInterface $package, $path)
{
return md5($package->getName() . ':' . $path);
}









protected function sortPackageMap(array $packageMap)
{
$packages = array();
$paths = array();
$usageList = array();

foreach ($packageMap as $item) {
list($package, $path) = $item;
$name = $package->getName();
$packages[$name] = $package;
$paths[$name] = $path;

foreach (array_merge($package->getRequires(), $package->getDevRequires()) as $link) {
$target = $link->getTarget();
$usageList[$target][] = $name;
}
}

$computing = array();
$computed = array();
$computeImportance = function ($name) use (&$computeImportance, &$computing, &$computed, $usageList) {

 if (isset($computed[$name])) {
return $computed[$name];
}


 if (isset($computing[$name])) {
return 0;
}

$computing[$name] = true;
$weight = 0;

if (isset($usageList[$name])) {
foreach ($usageList[$name] as $user) {
$weight -= 1 - $computeImportance($user);
}
}

unset($computing[$name]);
$computed[$name] = $weight;

return $weight;
};

$weightList = array();

foreach ($packages as $name => $package) {
$weight = $computeImportance($name);
$weightList[$name] = $weight;
}

$stable_sort = function (&$array) {
static $transform, $restore;

$i = 0;

if (!$transform) {
$transform = function (&$v, $k) use (&$i) {
$v = array($v, ++$i, $k, $v);
};

$restore = function (&$v, $k) {
$v = $v[3];
};
}

array_walk($array, $transform);
asort($array);
array_walk($array, $restore);
};

$stable_sort($weightList);

$sortedPackageMap = array();

foreach (array_keys($weightList) as $name) {
$sortedPackageMap[] = array($packages[$name], $paths[$name]);
}

return $sortedPackageMap;
}







protected function safeCopy($source, $target)
{
$source = fopen($source, 'r');
$target = fopen($target, 'w+');

stream_copy_to_stream($source, $target);
fclose($source);
fclose($target);
}
}
