<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Nette\DI\Helpers;
use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\DependencyInjection\LoaderFactory;
use PHPStan\File\FileHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;

class AnalyseCommand extends \Symfony\Component\Console\Command\Command
{

	private const NAME = 'analyse';

	public const OPTION_LEVEL = 'level';

	public const DEFAULT_LEVEL = 0;

	protected function configure(): void
	{
		$this->setName(self::NAME)
			->setDescription('Analyses source code')
			->setDefinition([
				new InputArgument('paths', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Paths with source code to run analysis on'),
				new InputOption('configuration', 'c', InputOption::VALUE_REQUIRED, 'Path to project configuration file'),
				new InputOption(self::OPTION_LEVEL, 'l', InputOption::VALUE_REQUIRED, 'Level of rule options - the higher the stricter'),
				new InputOption(ErrorsConsoleStyle::OPTION_NO_PROGRESS, null, InputOption::VALUE_NONE, 'Do not show progress bar, only results'),
				new InputOption('debug', null, InputOption::VALUE_NONE, 'Show debug information - which file is analysed, do not catch internal errors'),
				new InputOption('autoload-file', 'a', InputOption::VALUE_REQUIRED, 'Project\'s additional autoload file path'),
				new InputOption('error-format', null, InputOption::VALUE_REQUIRED, 'Format in which to print the result of the analysis', 'table'),
				new InputOption('errorFormat', null, InputOption::VALUE_REQUIRED, '[deprecated] Use --error-format instead'),
				new InputOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for analysis'),
			]);
	}

	/**
	 * @return string[]
	 */
	public function getAliases(): array
	{
		return ['analyze'];
	}

	protected function initialize(InputInterface $input, OutputInterface $output): void
	{
		if ((bool) $input->getOption('debug')) {
			$this->getApplication()->setCatchExceptions(false);
			return;
		}
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$errOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

		$consoleStyle = new ErrorsConsoleStyle($input, $output);

		$memoryLimit = $input->getOption('memory-limit');
		if ($memoryLimit !== null) {

			if (\Nette\Utils\Strings::match($memoryLimit, '#^-?\d+[kMG]?$#i') === null) {
				$errOutput->writeln(sprintf('Invalid memory limit format "%s".', $memoryLimit));
				return 1;
			}
			if (ini_set('memory_limit', $memoryLimit) === false) {
				$errOutput->writeln(sprintf('Memory limit "%s" cannot be set.', $memoryLimit));
				return 1;
			}
		}

		$currentWorkingDirectory = getcwd();
		if ($currentWorkingDirectory === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		$fileHelper = new FileHelper($currentWorkingDirectory);

		$autoloadFile = $input->getOption('autoload-file');
		if ($autoloadFile !== null && is_file($autoloadFile)) {
			$autoloadFile = $fileHelper->absolutizePath($autoloadFile);
			if (is_file($autoloadFile)) {
				(function (string $file): void {
					require_once $file;
				})($autoloadFile);
			}
		}

		$paths = $input->getArgument('paths');
		$projectConfigFile = $input->getOption('configuration');
		if ($projectConfigFile === null) {
			foreach (['phpstan.neon', 'phpstan.neon.dist'] as $discoverableConfigName) {
				$discoverableConfigFile = $currentWorkingDirectory . DIRECTORY_SEPARATOR . $discoverableConfigName;
				if (is_file($discoverableConfigFile)) {
					$projectConfigFile = $discoverableConfigFile;
					$errOutput->writeln(sprintf('Note: Using configuration file %s.', $projectConfigFile));
					break;
				}
			}
		}

		$levelOption = $input->getOption(self::OPTION_LEVEL);
		$defaultLevelUsed = false;
		if ($projectConfigFile === null && $levelOption === null) {
			$levelOption = self::DEFAULT_LEVEL;
			$defaultLevelUsed = true;
		}

		$containerFactory = new ContainerFactory($currentWorkingDirectory);

		if ($projectConfigFile !== null) {
			if (!is_file($projectConfigFile)) {
				$errOutput->writeln(sprintf('Project config file at path %s does not exist.', $projectConfigFile));
				return 1;
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
			if ($levelOption === null && isset($projectConfig['parameters']['level'])) {
				$levelOption = $projectConfig['parameters']['level'];
			}
			if (count($paths) === 0 && isset($projectConfig['parameters']['paths'])) {
				$paths = Helpers::expand($projectConfig['parameters']['paths'], $defaultParameters);
			}
		}

		$additionalConfigFiles = [];
		if ($levelOption !== null) {
			$levelConfigFile = sprintf('%s/config.level%s.neon', $containerFactory->getConfigDirectory(), $levelOption);
			if (!is_file($levelConfigFile)) {
				$errOutput->writeln(sprintf('Level config file %s was not found.', $levelConfigFile));
				return 1;
			}

			$additionalConfigFiles[] = $levelConfigFile;
		}

		if ($projectConfigFile !== null) {
			$additionalConfigFiles[] = $projectConfigFile;
		}

		if (!isset($tmpDir)) {
			$tmpDir = sys_get_temp_dir() . '/phpstan';
			if (!@mkdir($tmpDir, 0777, true) && !is_dir($tmpDir)) {
				$errOutput->writeln(sprintf('Cannot create a temp directory %s', $tmpDir));
				return 1;
			}
		}

		$container = $containerFactory->create($tmpDir, $additionalConfigFiles);
		$memoryLimitFile = $container->parameters['memoryLimitFile'];
		if (file_exists($memoryLimitFile)) {
			$memoryLimitFileContents = file_get_contents($memoryLimitFile);
			if ($memoryLimitFileContents === false) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			$errOutput->writeln('PHPStan crashed in the previous run probably because of excessive memory consumption.');
			$errOutput->writeln(sprintf('It consumed around %s of memory.', $memoryLimitFileContents));
			$errOutput->writeln('');
			$errOutput->writeln('');
			$errOutput->writeln('To avoid this issue, allow to use more memory with the --memory-limit option.');
			@unlink($memoryLimitFile);
		}

		$errorFormat = $input->getOption('error-format');
		$oldErrorFormat = $input->getOption('errorFormat');

		if ($oldErrorFormat !== null) {
			$errOutput->writeln('Note: Using the option --errorFormat is deprecated. Use --error-format instead.');

			$errorFormat = $oldErrorFormat;
		}

		$errorFormatterServiceName = sprintf('errorFormatter.%s', $errorFormat);
		if (!$container->hasService($errorFormatterServiceName)) {
			$errOutput->writeln(sprintf(
				'Error formatter "%s" not found. Available error formatters are: %s',
				$errorFormat,
				implode(', ', array_map(function (string $name) {
					return substr($name, strlen('errorFormatter.'));
				}, $container->findByType(ErrorFormatter::class)))
			));
			return 1;
		}
		/** @var ErrorFormatter $errorFormatter */
		$errorFormatter = $container->getService($errorFormatterServiceName);
		$this->setUpSignalHandler($consoleStyle, $memoryLimitFile);
		if (!isset($container->parameters['customRulesetUsed'])) {
			$errOutput->writeln('');
			$errOutput->writeln('<comment>No rules detected</comment>');
			$errOutput->writeln('');
			$errOutput->writeln('You have the following choices:');
			$errOutput->writeln('');
			$errOutput->writeln('* while running the analyse option, use the <info>--level</info> option to adjust your rule level - the higher the stricter');
			$errOutput->writeln('');
			$errOutput->writeln(sprintf('* create your own <info>custom ruleset</info> by selecting which rules you want to check by copying the service definitions from the built-in config level files in <options=bold>%s</>.', $fileHelper->normalizePath(__DIR__ . '/../../conf')));
			$errOutput->writeln('  * in this case, don\'t forget to define parameter <options=bold>customRulesetUsed</> in your config file.');
			$errOutput->writeln('');
			return 1;
		} elseif ((bool) $container->parameters['customRulesetUsed']) {
			$defaultLevelUsed = false;
		}

		foreach ($container->parameters['autoload_files'] as $autoloadFile) {
			(function (string $file): void {
				require_once $file;
			})($fileHelper->normalizePath($autoloadFile));
		}

		if (count($container->parameters['autoload_directories']) > 0) {
			$robotLoader = new \Nette\Loaders\RobotLoader();
			$robotLoader->acceptFiles = array_map(function (string $extension): string {
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

		/** @var \PHPStan\Command\AnalyseApplication $application */
		$application = $container->getByType(AnalyseApplication::class);
		return $this->handleReturn(
			$application->analyse(
				$paths,
				$consoleStyle,
				$errorFormatter,
				$defaultLevelUsed,
				$input->getOption('debug')
			),
			$memoryLimitFile
		);
	}

	private function handleReturn(int $code, string $memoryLimitFile): int
	{
		@unlink($memoryLimitFile);
		return $code;
	}

	private function setUpSignalHandler(StyleInterface $consoleStyle, string $memoryLimitFile): void
	{
		if (!function_exists('pcntl_signal')) {
			return;
		}

		pcntl_signal(SIGINT, function () use ($consoleStyle, $memoryLimitFile): void {
			if (file_exists($memoryLimitFile)) {
				@unlink($memoryLimitFile);
			}
			$consoleStyle->newLine();
			exit(1);
		});
	}

}
