<?php declare(strict_types = 1);

// Mimics a program that includes phpstan.phar instead of running inside it, the way Rector does:
// the main process restarts into the turbo extension, then spawns itself as a "worker" with the -d flags
// findExtensionForWorkers() resolves, and each worker must run with the extension active, without a restart of its own.

use PHPStan\Turbo\TurboExtensionEnabler;
use PHPStan\Turbo\TurboExtensionSelector;
use PHPStan\Turbo\TurboProcessRestarter;

require __DIR__ . '/vendor/autoload.php';

$pharPath = __DIR__ . '/vendor/phpstan/phpstan/phpstan.phar';
$isWorker = in_array('worker', $argv, true);
if ($isWorker) {
	// printed before the restart call: a worker that restarts itself prints it twice
	echo "worker boot\n";
}

TurboProcessRestarter::restartIfSuitable($argv, $pharPath, 'turbo-e2e');
TurboExtensionEnabler::activateIfCompatible($pharPath);

$state = sprintf(
	'%s pid=%d loaded=%d active=%d',
	$isWorker ? 'worker' : 'main',
	getmypid(),
	(int) extension_loaded('phpstan_turbo'),
	(int) TurboExtensionEnabler::isActive(),
);

if ($isWorker) {
	echo $state, "\n";
	exit(0);
}

echo $state, "\n";
if (!TurboExtensionEnabler::isActive()) {
	fwrite(STDERR, "The main process did not restart into the turbo extension.\n");
	fwrite(STDERR, sprintf(
		"php=%s os=%s machine=%s zts=%d debug=%d binary=%s restarted=%s pcntl_fork=%d pcntl_exec=%d posix_kill=%d proc_open=%d incompatible=%s disabled=%s\n",
		PHP_VERSION,
		PHP_OS_FAMILY,
		php_uname('m'),
		(int) PHP_ZTS,
		(int) PHP_DEBUG,
		TurboExtensionSelector::findExtension($pharPath) ?? 'none',
		var_export(getenv('PHPSTAN_RESTART'), true),
		(int) function_exists('pcntl_fork'),
		(int) function_exists('pcntl_exec'),
		(int) function_exists('posix_kill'),
		(int) function_exists('proc_open'),
		TurboExtensionEnabler::getIncompatibleLoadedVersion() ?? 'none',
		(string) ini_get('disable_functions'),
	));
	exit(1);
}

$extensionPath = TurboExtensionSelector::findExtensionForWorkers();
if ($extensionPath === null) {
	fwrite(STDERR, "findExtensionForWorkers() returned null in the restarted main process.\n");
	exit(1);
}

$command = [
	PHP_BINARY,
	'-d',
	"extension='" . $extensionPath . "'",
	'-d',
	TurboExtensionSelector::EXTENSION_PATH_INI . "='" . $extensionPath . "'",
	__FILE__,
	'worker',
];
$process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
if ($process === false) {
	fwrite(STDERR, "The worker could not be started.\n");
	exit(1);
}

$output = stream_get_contents($pipes[1]);
$errorOutput = stream_get_contents($pipes[2]);
$exitCode = proc_close($process);
echo $output, $errorOutput;

if ($exitCode !== 0 || preg_match('~^worker pid=\d+ loaded=1 active=1$~m', (string) $output) !== 1) {
	fwrite(STDERR, "The worker did not run with the turbo extension active.\n");
	exit(1);
}

if (substr_count((string) $output, 'worker boot') !== 1) {
	fwrite(STDERR, "The worker restarted itself.\n");
	exit(1);
}

echo "OK\n";
