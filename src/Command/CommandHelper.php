<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Composer\XdebugHandler\XdebugHandler;
use Nette\DI\Config\Adapters\NeonAdapter;
use Nette\DI\Config\Adapters\PhpAdapter;
use Nette\DI\Helpers;
use Nette\Schema\Context as SchemaContext;
use Nette\Schema\Processor;
use Nette\Utils\Strings;
use Nette\Utils\Validators;
use PHPStan\DependencyInjection\Container;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\DependencyInjection\LoaderFactory;
use PHPStan\File\FileFinder;
use PHPStan\File\FileHelper;
use PHPStan\Type\TypeCombinator;
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
		?string $level,
		bool $allowXdebug
	): InceptionResult
	{
		if (!$allowXdebug) {
			$xdebug = new XdebugHandler('phpstan', '--ansi');
			$xdebug->check();
			unset($xdebug);
		}
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

		if (count($paths) === 0 && $pathsFile !== null) {
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
			$additionalConfigFiles[] = $fileHelper->absolutizePath($projectConfigFile);
		}

		self::detectDuplicateIncludedFiles(
			$errorOutput,
			$fileHelper,
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
		$paths = array_map(static function (string $path) use ($fileHelper): string {
			return $fileHelper->absolutizePath($path);
		}, $paths);

		try {
			$netteContainer = $containerFactory->create($tmpDir, $additionalConfigFiles, $paths);
		} catch (\Nette\DI\InvalidConfigurationException | \Nette\Utils\AssertionException $e) {
			$errorOutput->writeln('<error>Invalid configuration:</error>');
			$errorOutput->writeln($e->getMessage());
			throw new \PHPStan\Command\InceptionNotSuccessfulException();
		}

		TypeCombinator::$enableSubtractableTypes = (bool) $netteContainer->parameters['featureToggles']['subtractableTypes'];
		$memoryLimitFile = $netteContainer->parameters['memoryLimitFile'];
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
		if (!isset($netteContainer->parameters['customRulesetUsed'])) {
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
		} elseif ((bool) $netteContainer->parameters['customRulesetUsed']) {
			$defaultLevelUsed = false;
		}

		if ($netteContainer->parameters['featureToggles']['validateParameters']) {
			$schema = $netteContainer->parameters['__parametersSchema'];
			$processor = new Processor();
			$processor->onNewContext[] = static function (SchemaContext $context): void {
				$context->path = ['parameters'];
			};

			try {
				$processor->process($schema, $netteContainer->parameters);
			} catch (\Nette\Schema\ValidationException $e) {
				foreach ($e->getMessages() as $message) {
					$errorOutput->writeln('<error>Invalid configuration:</error>');
					$errorOutput->writeln($message);
				}
				throw new \PHPStan\Command\InceptionNotSuccessfulException();
			}
		}

		$container = $netteContainer->getByType(Container::class);
		foreach ($netteContainer->parameters['autoload_files'] as $parameterAutoloadFile) {
			(static function (string $file) use ($container): void {
				require_once $file;
			})($fileHelper->normalizePath($parameterAutoloadFile));
		}

		if (count($netteContainer->parameters['autoload_directories']) > 0) {
			$robotLoader = new \Nette\Loaders\RobotLoader();
			$robotLoader->acceptFiles = array_map(static function (string $extension): string {
				return sprintf('*.%s', $extension);
			}, $netteContainer->parameters['fileExtensions']);

			$robotLoader->setTempDirectory($tmpDir);
			foreach ($netteContainer->parameters['autoload_directories'] as $directory) {
				$robotLoader->addDirectory($fileHelper->normalizePath($directory));
			}

			foreach ($netteContainer->parameters['excludes_analyse'] as $directory) {
				$robotLoader->excludeDirectory($fileHelper->normalizePath($directory));
			}

			$robotLoader->register();
		}

		$bootstrapFile = $netteContainer->parameters['bootstrap'];
		if ($bootstrapFile !== null) {
			$bootstrapFile = $fileHelper->normalizePath($bootstrapFile);
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
		$fileFinder = $netteContainer->getByType(FileFinder::class);

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
			$netteContainer,
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
			return $fileHelper->absolutizePath($fileHelper->normalizePath($file));
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
