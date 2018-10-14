<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Nette\DI\Helpers;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\DependencyInjection\LoaderFactory;
use PHPStan\File\FileExcluder;
use PHPStan\File\FileHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Finder\Finder;

class CommandHelper
{

	public const DEFAULT_LEVEL = 0;

	public static function begin(
		InputInterface $input,
		OutputInterface $output,
		array $paths,
		?string $memoryLimit,
		?string $autoloadFile,
		?string $projectConfigFile,
		?string $level
	): InceptionResult
	{
		$errorOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
		$consoleStyle = new ErrorsConsoleStyle($input, $output);
		if ($memoryLimit !== null) {
			if (\Nette\Utils\Strings::match($memoryLimit, '#^-?\d+[kMG]?$#i') === null) {
				$errorOutput->writeln(sprintf('Invalid memory limit format "%s".', $memoryLimit));
				throw new \PHPStan\Command\InceptionNotSuccessfulException();
			}
			if (ini_set('memory_limit', $memoryLimit) === false) {
				$errorOutput->writeln(sprintf('Memory limit "%s" cannot be set.', $memoryLimit));
				throw new \PHPStan\Command\InceptionNotSuccessfulException();
			}
		}

		$currentWorkingDirectory = getcwd();
		if ($currentWorkingDirectory === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		$fileHelper = new FileHelper($currentWorkingDirectory);
		if ($autoloadFile !== null && is_file($autoloadFile)) {
			$autoloadFile = $fileHelper->absolutizePath($autoloadFile);
			if (is_file($autoloadFile)) {
				(static function (string $file): void {
					require_once $file;
				})($autoloadFile);
			}
		}
		if ($projectConfigFile === null) {
			foreach (['phpstan.neon', 'phpstan.neon.dist'] as $discoverableConfigName) {
				$discoverableConfigFile = $currentWorkingDirectory . DIRECTORY_SEPARATOR . $discoverableConfigName;
				if (is_file($discoverableConfigFile)) {
					$projectConfigFile = $discoverableConfigFile;
					$errorOutput->writeln(sprintf('Note: Using configuration file %s.', $projectConfigFile));
					break;
				}
			}
		}
		$defaultLevelUsed = false;
		if ($projectConfigFile === null && $level === null) {
			$level = self::DEFAULT_LEVEL;
			$defaultLevelUsed = true;
		}

		$containerFactory = new ContainerFactory($currentWorkingDirectory);
		if ($projectConfigFile !== null) {
			if (!is_file($projectConfigFile)) {
				$errorOutput->writeln(sprintf('Project config file at path %s does not exist.', $projectConfigFile));
				throw new \PHPStan\Command\InceptionNotSuccessfulException();
			}

			$loader = (new LoaderFactory())->createLoader();
			$projectConfig = $loader->load($projectConfigFile, null);
			$defaultParameters = [
				'rootDir' => $containerFactory->getRootDirectory(),
				'currentWorkingDirectory' => $containerFactory->getCurrentWorkingDirectory(),
			];

			if (isset($projectConfig['parameters']['tmpDir'])) {
				$tmpDir = Helpers::expand($projectConfig['parameters']['tmpDir'], $defaultParameters);
			}
			if ($level === null && isset($projectConfig['parameters']['level'])) {
				$level = $projectConfig['parameters']['level'];
			}
			if (count($paths) === 0 && isset($projectConfig['parameters']['paths'])) {
				$paths = Helpers::expand($projectConfig['parameters']['paths'], $defaultParameters);
			}
		}

		if (count($paths) === 0) {
			$errorOutput->writeln('At least one path must be specified to analyse.');
			throw new \PHPStan\Command\InceptionNotSuccessfulException();
		}

		$additionalConfigFiles = [];
		if ($level !== null) {
			$levelConfigFile = sprintf('%s/config.level%s.neon', $containerFactory->getConfigDirectory(), $level);
			if (!is_file($levelConfigFile)) {
				$errorOutput->writeln(sprintf('Level config file %s was not found.', $levelConfigFile));
				throw new \PHPStan\Command\InceptionNotSuccessfulException();
			}

			$additionalConfigFiles[] = $levelConfigFile;
		}

		if ($projectConfigFile !== null) {
			$additionalConfigFiles[] = $projectConfigFile;
		}

		if (!isset($tmpDir)) {
			$tmpDir = sys_get_temp_dir() . '/phpstan';
			if (!@mkdir($tmpDir, 0777, true) && !is_dir($tmpDir)) {
				$errorOutput->writeln(sprintf('Cannot create a temp directory %s', $tmpDir));
				throw new \PHPStan\Command\InceptionNotSuccessfulException();
			}
		}
		$paths = array_map(static function (string $path) use ($fileHelper): string {
			return $fileHelper->absolutizePath($path);
		}, $paths);
		$container = $containerFactory->create($tmpDir, $additionalConfigFiles, $paths);
		$memoryLimitFile = $container->parameters['memoryLimitFile'];
		if (file_exists($memoryLimitFile)) {
			$memoryLimitFileContents = file_get_contents($memoryLimitFile);
			if ($memoryLimitFileContents === false) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			$errorOutput->writeln('PHPStan crashed in the previous run probably because of excessive memory consumption.');
			$errorOutput->writeln(sprintf('It consumed around %s of memory.', $memoryLimitFileContents));
			$errorOutput->writeln('');
			$errorOutput->writeln('');
			$errorOutput->writeln('To avoid this issue, allow to use more memory with the --memory-limit option.');
			@unlink($memoryLimitFile);
		}

		self::setUpSignalHandler($consoleStyle, $memoryLimitFile);
		if (!isset($container->parameters['customRulesetUsed'])) {
			$errorOutput->writeln('');
			$errorOutput->writeln('<comment>No rules detected</comment>');
			$errorOutput->writeln('');
			$errorOutput->writeln('You have the following choices:');
			$errorOutput->writeln('');
			$errorOutput->writeln('* while running the analyse option, use the <info>--level</info> option to adjust your rule level - the higher the stricter');
			$errorOutput->writeln('');
			$errorOutput->writeln(sprintf('* create your own <info>custom ruleset</info> by selecting which rules you want to check by copying the service definitions from the built-in config level files in <options=bold>%s</>.', $fileHelper->normalizePath(__DIR__ . '/../../conf')));
			$errorOutput->writeln('  * in this case, don\'t forget to define parameter <options=bold>customRulesetUsed</> in your config file.');
			$errorOutput->writeln('');
			throw new \PHPStan\Command\InceptionNotSuccessfulException();
		} elseif ((bool) $container->parameters['customRulesetUsed']) {
			$defaultLevelUsed = false;
		}

		foreach ($container->parameters['autoload_files'] as $autoloadFile) {
			(static function (string $file): void {
				require_once $file;
			})($fileHelper->normalizePath($autoloadFile));
		}

		if (count($container->parameters['autoload_directories']) > 0) {
			$robotLoader = new \Nette\Loaders\RobotLoader();
			$robotLoader->acceptFiles = array_map(static function (string $extension): string {
				return sprintf('*.%s', $extension);
			}, $container->parameters['fileExtensions']);

			$robotLoader->setTempDirectory($tmpDir);
			foreach ($container->parameters['autoload_directories'] as $directory) {
				$robotLoader->addDirectory($fileHelper->normalizePath($directory));
			}

			foreach ($container->parameters['excludes_analyse'] as $directory) {
				$robotLoader->excludeDirectory($fileHelper->normalizePath($directory));
			}

			$robotLoader->register();
		}

		$bootstrapFile = $container->parameters['bootstrap'];
		if ($bootstrapFile !== null) {
			$bootstrapFile = $fileHelper->normalizePath($bootstrapFile);
			if (!is_file($bootstrapFile)) {
				$errorOutput->writeln(sprintf('Bootstrap file %s does not exist.', $bootstrapFile));
				throw new \PHPStan\Command\InceptionNotSuccessfulException();
			}
			try {
				(static function (string $file): void {
					require_once $file;
				})($bootstrapFile);
			} catch (\Throwable $e) {
				$errorOutput->writeln($e->getMessage());
				throw new \PHPStan\Command\InceptionNotSuccessfulException();
			}
		}

		$onlyFiles = true;
		$files = [];
		$fileExtensions = $container->parameters['fileExtensions'];
		foreach ($paths as $path) {
			if (!file_exists($path)) {
				$errorOutput->writeln(sprintf('<error>Path %s does not exist</error>', $path));
				throw new \PHPStan\Command\InceptionNotSuccessfulException();
			} elseif (is_file($path)) {
				$files[] = $fileHelper->normalizePath($path);
			} else {
				$finder = new Finder();
				$finder->followLinks();
				foreach ($finder->files()->name('*.{' . implode(',', $fileExtensions) . '}')->in($path) as $fileInfo) {
					$files[] = $fileHelper->normalizePath($fileInfo->getPathname());
					$onlyFiles = false;
				}
			}
		}

		$fileExcluder = $container->getByType(FileExcluder::class);
		$files = array_filter($files, static function (string $file) use ($fileExcluder): bool {
			return !$fileExcluder->isExcludedFromAnalysing($file);
		});

		return new InceptionResult(
			$files,
			$onlyFiles,
			$consoleStyle,
			$errorOutput,
			$container,
			$defaultLevelUsed,
			$memoryLimitFile
		);
	}

	private static function setUpSignalHandler(OutputStyle $consoleStyle, string $memoryLimitFile): void
	{
		if (!function_exists('pcntl_signal')) {
			return;
		}

		pcntl_signal(SIGINT, static function () use ($consoleStyle, $memoryLimitFile): void {
			if (file_exists($memoryLimitFile)) {
				@unlink($memoryLimitFile);
			}
			$consoleStyle->newLine();
			exit(1);
		});
	}

}
