<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Nette\DI\Config\Adapters\PhpAdapter;
use Nette\DI\Helpers;
use Nette\Schema\Context as SchemaContext;
use Nette\Schema\Processor;
use Nette\Utils\Strings;
use Nette\Utils\Validators;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\DependencyInjection\LoaderFactory;
use PHPStan\DependencyInjection\NeonAdapter;
use PHPStan\File\FileFinder;
use PHPStan\File\FileHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;

class CommandHelper
{

	public const DEFAULT_LEVEL = 0;

	public static function begin(
		InputInterface $input,
		OutputInterface $output,
		array $paths,
		?string $pathsFile,
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
		$currentWorkingDirectoryFileHelper = new FileHelper($currentWorkingDirectory);
		if ($autoloadFile !== null) {
			if (!is_file($autoloadFile)) {
				$errorOutput->writeln(sprintf('Autoload file "%s" not found.', $autoloadFile));
				throw new \PHPStan\Command\InceptionNotSuccessfulException();
			}

			(static function (string $file): void {
				require_once $file;
			})($currentWorkingDirectoryFileHelper->absolutizePath($autoloadFile));
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

		$paths = array_map(static function (string $path) use ($currentWorkingDirectoryFileHelper): string {
			return $currentWorkingDirectoryFileHelper->absolutizePath($path);
		}, $paths);

		if (count($paths) === 0 && $pathsFile !== null) {
			$pathsFile = $currentWorkingDirectoryFileHelper->absolutizePath($pathsFile);
			if (!file_exists($pathsFile)) {
				$errorOutput->writeln(sprintf('Paths file %s does not exist.', $pathsFile));
				throw new \PHPStan\Command\InceptionNotSuccessfulException();
			}

			$pathsString = file_get_contents($pathsFile);
			if ($pathsString === false) {
				throw new \PHPStan\ShouldNotHappenException();
			}

			$paths = array_values(array_filter(explode("\n", $pathsString), static function (string $path): bool {
				return trim($path) !== '';
			}));

			$pathsFileFileHelper = new FileHelper(dirname($pathsFile));
			$paths = array_map(static function (string $path) use ($pathsFileFileHelper): string {
				return $pathsFileFileHelper->absolutizePath($path);
			}, $paths);
		}

		$containerFactory = new ContainerFactory($currentWorkingDirectory);
		if ($projectConfigFile !== null) {
			if (!is_file($projectConfigFile)) {
				$errorOutput->writeln(sprintf('Project config file at path %s does not exist.', $projectConfigFile));
				throw new \PHPStan\Command\InceptionNotSuccessfulException();
			}

			$loader = (new LoaderFactory(
				$containerFactory->getRootDirectory(),
				$containerFactory->getCurrentWorkingDirectory()
			))->createLoader();

			try {
				$projectConfig = $loader->load($projectConfigFile, null);
			} catch (\Nette\InvalidStateException | \Nette\FileNotFoundException $e) {
				$errorOutput->writeln($e->getMessage());
				throw new \PHPStan\Command\InceptionNotSuccessfulException();
			}
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

		$additionalConfigFiles = [];
		if ($level !== null) {
			$levelConfigFile = sprintf('%s/config.level%s.neon', $containerFactory->getConfigDirectory(), $level);
			if (!is_file($levelConfigFile)) {
				$errorOutput->writeln(sprintf('Level config file %s was not found.', $levelConfigFile));
				throw new \PHPStan\Command\InceptionNotSuccessfulException();
			}

			$additionalConfigFiles[] = $levelConfigFile;
		}

		if (class_exists('PHPStan\ExtensionInstaller\GeneratedConfig')) {
			foreach (\PHPStan\ExtensionInstaller\GeneratedConfig::EXTENSIONS as $name => $extensionConfig) {
				foreach ($extensionConfig['extra']['includes'] ?? [] as $includedFile) {
					if (!is_string($includedFile)) {
						$errorOutput->writeln(sprintf('Cannot include config from package %s, expecting string file path but got %s', $name, gettype($includedFile)));
						throw new \PHPStan\Command\InceptionNotSuccessfulException();
					}
					$includedFilePath = sprintf('%s/%s', $extensionConfig['install_path'], $includedFile);
					if (!file_exists($includedFilePath) || !is_readable($includedFilePath)) {
						$errorOutput->writeln(sprintf('Config file %s does not exists or isn\'t readable', $includedFilePath));
						throw new \PHPStan\Command\InceptionNotSuccessfulException();
					}
					$additionalConfigFiles[] = $includedFilePath;
				}
			}
		}

		if ($projectConfigFile !== null) {
			$additionalConfigFiles[] = $currentWorkingDirectoryFileHelper->absolutizePath($projectConfigFile);
		}

		self::detectDuplicateIncludedFiles(
			$errorOutput,
			$currentWorkingDirectoryFileHelper,
			$additionalConfigFiles,
			[
				'rootDir' => $containerFactory->getRootDirectory(),
				'currentWorkingDirectory' => $containerFactory->getCurrentWorkingDirectory(),
			]
		);

		if (!isset($tmpDir)) {
			$tmpDir = sys_get_temp_dir() . '/phpstan';
			if (!@mkdir($tmpDir, 0777, true) && !is_dir($tmpDir)) {
				$errorOutput->writeln(sprintf('Cannot create a temp directory %s', $tmpDir));
				throw new \PHPStan\Command\InceptionNotSuccessfulException();
			}
		}

		try {
			$container = $containerFactory->create($tmpDir, $additionalConfigFiles, $paths);
		} catch (\Nette\DI\InvalidConfigurationException | \Nette\Utils\AssertionException $e) {
			$errorOutput->writeln('<error>Invalid configuration:</error>');
			$errorOutput->writeln($e->getMessage());
			throw new \PHPStan\Command\InceptionNotSuccessfulException();
		}

		if (count($paths) === 0) {
			$errorOutput->writeln('At least one path must be specified to analyse.');
			throw new \PHPStan\Command\InceptionNotSuccessfulException();
		}

		$memoryLimitFile = $container->getParameter('memoryLimitFile');
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
		if ($container->getParameter('customRulesetUsed') === null) {
			$errorOutput->writeln('');
			$errorOutput->writeln('<comment>No rules detected</comment>');
			$errorOutput->writeln('');
			$errorOutput->writeln('You have the following choices:');
			$errorOutput->writeln('');
			$errorOutput->writeln('* while running the analyse option, use the <info>--level</info> option to adjust your rule level - the higher the stricter');
			$errorOutput->writeln('');
			$errorOutput->writeln(sprintf('* create your own <info>custom ruleset</info> by selecting which rules you want to check by copying the service definitions from the built-in config level files in <options=bold>%s</>.', $currentWorkingDirectoryFileHelper->normalizePath(__DIR__ . '/../../conf')));
			$errorOutput->writeln('  * in this case, don\'t forget to define parameter <options=bold>customRulesetUsed</> in your config file.');
			$errorOutput->writeln('');
			throw new \PHPStan\Command\InceptionNotSuccessfulException();
		} elseif ((bool) $container->getParameter('customRulesetUsed')) {
			$defaultLevelUsed = false;
		}

		$schema = $container->getParameter('__parametersSchema');
		$processor = new Processor();
		$processor->onNewContext[] = static function (SchemaContext $context): void {
			$context->path = ['parameters'];
		};

		try {
			$processor->process($schema, $container->getParameters());
		} catch (\Nette\Schema\ValidationException $e) {
			foreach ($e->getMessages() as $message) {
				$errorOutput->writeln('<error>Invalid configuration:</error>');
				$errorOutput->writeln($message);
			}
			throw new \PHPStan\Command\InceptionNotSuccessfulException();
		}

		foreach ($container->getParameter('autoload_files') as $parameterAutoloadFile) {
			if (!file_exists($parameterAutoloadFile)) {
				$errorOutput->writeln(sprintf('Autoload file %s does not exist.', $parameterAutoloadFile));
				throw new \PHPStan\Command\InceptionNotSuccessfulException();
			}
			(static function (string $file) use ($container): void {
				require_once $file;
			})($parameterAutoloadFile);
		}

		$autoloadDirectories = $container->getParameter('autoload_directories');
		if (count($autoloadDirectories) > 0) {
			$robotLoader = new \Nette\Loaders\RobotLoader();
			$robotLoader->acceptFiles = array_map(static function (string $extension): string {
				return sprintf('*.%s', $extension);
			}, $container->getParameter('fileExtensions'));

			$robotLoader->setTempDirectory($tmpDir);
			foreach ($autoloadDirectories as $directory) {
				if (!file_exists($directory)) {
					$errorOutput->writeln(sprintf('Autoload directory %s does not exist.', $directory));
					throw new \PHPStan\Command\InceptionNotSuccessfulException();
				}
				$robotLoader->addDirectory($directory);
			}

			foreach ($container->getParameter('excludes_analyse') as $directory) {
				$robotLoader->excludeDirectory($directory);
			}

			$robotLoader->register();
		}

		$bootstrapFile = $container->getParameter('bootstrap');
		if ($bootstrapFile !== null) {
			if (!is_file($bootstrapFile)) {
				$errorOutput->writeln(sprintf('Bootstrap file %s does not exist.', $bootstrapFile));
				throw new \PHPStan\Command\InceptionNotSuccessfulException();
			}
			try {
				(static function (string $file) use ($container): void {
					require_once $file;
				})($bootstrapFile);
			} catch (\Throwable $e) {
				$errorOutput->writeln($e->getMessage());
				throw new \PHPStan\Command\InceptionNotSuccessfulException();
			}
		}

		/** @var FileFinder $fileFinder */
		$fileFinder = $container->getByType(FileFinder::class);

		try {
			$fileFinderResult = $fileFinder->findFiles($paths);
		} catch (\PHPStan\File\PathNotFoundException $e) {
			$errorOutput->writeln(sprintf('<error>%s</error>', $e->getMessage()));
			throw new \PHPStan\Command\InceptionNotSuccessfulException($e->getMessage(), 0, $e);
		}

		return new InceptionResult(
			$fileFinderResult->getFiles(),
			$fileFinderResult->isOnlyFiles(),
			$consoleStyle,
			$errorOutput,
			$container,
			$defaultLevelUsed,
			$memoryLimitFile,
			$projectConfigFile
		);
	}

	private static function setUpSignalHandler(OutputStyle $consoleStyle, string $memoryLimitFile): void
	{
		if (!function_exists('pcntl_signal')) {
			return;
		}

		pcntl_async_signals(true);
		pcntl_signal(SIGINT, static function () use ($consoleStyle, $memoryLimitFile): void {
			if (file_exists($memoryLimitFile)) {
				@unlink($memoryLimitFile);
			}
			$consoleStyle->newLine();
			exit(1);
		});
	}

	private static function detectDuplicateIncludedFiles(
		OutputInterface $output,
		FileHelper $fileHelper,
		array $configFiles,
		array $loaderParameters
	): void
	{
		$neonAdapter = new NeonAdapter();
		$phpAdapter = new PhpAdapter();
		$allConfigFiles = $configFiles;
		foreach ($configFiles as $configFile) {
			$allConfigFiles = array_merge($allConfigFiles, self::getConfigFiles($neonAdapter, $phpAdapter, $configFile, $loaderParameters));
		}

		$normalized = array_map(static function (string $file) use ($fileHelper): string {
			return $fileHelper->normalizePath($file);
		}, $allConfigFiles);

		$deduplicated = array_unique($normalized);
		if (count($normalized) > count($deduplicated)) {
			$duplicateFiles = array_unique(array_diff_key($normalized, $deduplicated));

			$format = "<error>These files are included multiple times:</error>\n- %s";
			if (count($duplicateFiles) === 1) {
				$format = "<error>This file is included multiple times:</error>\n- %s";
			}
			$output->writeln(sprintf($format, implode("\n- ", $duplicateFiles)));

			if (class_exists('PHPStan\ExtensionInstaller\GeneratedConfig')) {
				$output->writeln('');
				$output->writeln('It can lead to unexpected results. If you\'re using phpstan/extension-installer, make sure you have removed corresponding neon files from your project config file.');
			}
			throw new \PHPStan\Command\InceptionNotSuccessfulException();
		}
	}

	private static function getConfigFiles(
		NeonAdapter $neonAdapter,
		PhpAdapter $phpAdapter,
		string $configFile,
		array $loaderParameters
	): array
	{
		if (!is_file($configFile) || !is_readable($configFile)) {
			return [];
		}

		if (Strings::endsWith($configFile, '.php')) {
			$data = $phpAdapter->load($configFile);
		} else {
			$data = $neonAdapter->load($configFile);
		}
		$allConfigFiles = [];
		if (isset($data['includes'])) {
			Validators::assert($data['includes'], 'list', sprintf("section 'includes' in file '%s'", $configFile));
			$includes = Helpers::expand($data['includes'], $loaderParameters);
			foreach ($includes as $include) {
				$include = self::expandIncludedFile($include, $configFile);
				$allConfigFiles[] = $include;
				$allConfigFiles = array_merge($allConfigFiles, self::getConfigFiles($neonAdapter, $phpAdapter, $include, $loaderParameters));
			}
		}

		return $allConfigFiles;
	}

	private static function expandIncludedFile(string $includedFile, string $mainFile): string
	{
		return Strings::match($includedFile, '#([a-z]+:)?[/\\\\]#Ai') !== null // is absolute
			? $includedFile
			: dirname($mainFile) . '/' . $includedFile;
	}

}
