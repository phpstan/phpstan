<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Nette\Configurator;
use PhpParser\Node\Stmt\Catch_;
use PHPStan\FileHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;

class AnalyseCommand extends \Symfony\Component\Console\Command\Command
{

	const NAME = 'analyse';

	const OPTION_LEVEL = 'level';

	const DEFAULT_LEVEL = 0;

	protected function configure()
	{
		$this->setName(self::NAME)
			->setDescription('Analyses source code')
			->setDefinition([
				new InputArgument('paths', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Paths with source code to run analysis on'),
				new InputOption('configuration', 'c', InputOption::VALUE_REQUIRED, 'Path to project configuration file'),
				new InputOption(self::OPTION_LEVEL, 'l', InputOption::VALUE_REQUIRED, 'Level of rule options - the higher the stricter'),
				new InputOption(ErrorsConsoleStyle::OPTION_NO_PROGRESS, null, InputOption::VALUE_NONE, 'Do not show progress bar, only results'),
				new InputOption('autoload-file', 'a', InputOption::VALUE_OPTIONAL, 'Project\'s additional autoload file path'),
			]);
	}


	public function getAliases(): array
	{
		return ['analyze'];
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$autoloadFile = $input->getOption('autoload-file');
		if ($autoloadFile !== null && is_file($autoloadFile)) {
			require_once $autoloadFile;
		}

		$currentWorkingDirectory = getcwd();

		$fileHelper = new FileHelper($currentWorkingDirectory);

		$rootDir = $fileHelper->normalizePath(__DIR__ . '/../..');
		$tmpDir = $rootDir . '/tmp';
		$confDir = $rootDir . '/conf';

		$configurator = new Configurator();
		$configurator->defaultExtensions = [];
		$configurator->setDebugMode(true);
		$configurator->setTempDirectory($tmpDir);

		$projectConfigFile = $input->getOption('configuration');
		$levelOption = $input->getOption(self::OPTION_LEVEL);
		$defaultLevelUsed = false;
		if ($projectConfigFile === null && $levelOption === null) {
			$levelOption = self::DEFAULT_LEVEL;
			$defaultLevelUsed = true;
		}

		$configFiles = [$confDir . '/config.neon'];
		if ($levelOption !== null) {
			$levelConfigFile = sprintf('%s/config.level%s.neon', $confDir, $levelOption);
			if (!is_file($levelConfigFile)) {
				$output->writeln(sprintf('Level config file %s was not found.', $levelConfigFile));
				return 1;
			}

			$configFiles[] = $levelConfigFile;
		}

		if ($projectConfigFile !== null) {
			if (!is_file($projectConfigFile)) {
				$output->writeln(sprintf('Project config file at path %s does not exist.', $projectConfigFile));
				return 1;
			}

			$configFiles[] = $projectConfigFile;
		}

		foreach ($configFiles as $configFile) {
			$configurator->addConfig($configFile);
		}

		$parameters = [
			'rootDir' => $rootDir,
			'tmpDir' => $tmpDir,
			'currentWorkingDirectory' => $currentWorkingDirectory,
		];

		$configurator->addParameters($parameters);
		$container = $configurator->createContainer();
		$consoleStyle = new ErrorsConsoleStyle($input, $output);
		$memoryLimitFile = $container->parameters['memoryLimitFile'];
		if (file_exists($memoryLimitFile)) {
			$consoleStyle->note(sprintf(
				"PHPStan crashed in the previous run probably because of excessive memory consumption.\nIt consumed around %s of memory.\n\nTo avoid this issue, increase the memory_limit directive in your php.ini file here:\n%s\n\nIf you can't or don't want to change the system-wide memory limit, run PHPStan like this:\n%s",
				file_get_contents($memoryLimitFile),
				php_ini_loaded_file(),
				sprintf('php -d memory_limit=XX %s', implode(' ', $_SERVER['argv']))
			));
			unlink($memoryLimitFile);
		}
		if (PHP_VERSION_ID >= 70100 && !property_exists(Catch_::class, 'types')) {
			$consoleStyle->note(
				'You\'re running PHP >= 7.1, but you still have PHP-Parser version 2.x. This will lead to parse errors in case you use PHP 7.1 syntax like nullable parameters, iterable and void typehints, union exception types, or class constant visibility. Update to PHP-Parser 3.x to dismiss this message.'
			);
		}
		$this->setUpSignalHandler($consoleStyle, $memoryLimitFile);
		if (!isset($container->parameters['customRulesetUsed'])) {
			$output->writeln('');
			$output->writeln('<comment>No rules detected</comment>');
			$output->writeln('');
			$output->writeln('You have the following choices:');
			$output->writeln('');
			$output->writeln('* while running the analyse option, use the <info>--level</info> option to adjust your rule level - the higher the stricter');
			$output->writeln('');
			$output->writeln(sprintf('* create your own <info>custom ruleset</info> by selecting which rules you want to check by copying the service definitions from the built-in config level files in <options=bold>%s</>.', $fileHelper->normalizePath(__DIR__ . '/../../conf')));
			$output->writeln('  * in this case, don\'t forget to define parameter <options=bold>customRulesetUsed</> in your config file.');
			$output->writeln('');
			return $this->handleReturn(1, $memoryLimitFile);
		} elseif ($container->parameters['customRulesetUsed']) {
			$defaultLevelUsed = false;
		}

		foreach ($container->parameters['autoload_files'] as $autoloadFile) {
			require_once $autoloadFile;
		}

		if (count($container->parameters['autoload_directories']) > 0) {
			$robotLoader = new \Nette\Loaders\RobotLoader();
			$robotLoader->setCacheStorage(new \Nette\Caching\Storages\MemoryStorage());
			foreach ($container->parameters['autoload_directories'] as $directory) {
				$robotLoader->addDirectory($directory);
			}

			$robotLoader->register();
		}

		$application = $container->getByType(AnalyseApplication::class);
		return $this->handleReturn(
			$application->analyse(
				$input->getArgument('paths'),
				$consoleStyle,
				$defaultLevelUsed
			),
			$memoryLimitFile
		);
	}

	private function handleReturn(int $code, string $memoryLimitFile): int
	{
		unlink($memoryLimitFile);
		return $code;
	}

	private function setUpSignalHandler(StyleInterface $consoleStyle, string $memoryLimitFile)
	{
		if (function_exists('pcntl_signal')) {
			pcntl_signal(SIGINT, function () use ($consoleStyle, $memoryLimitFile) {
				if (file_exists($memoryLimitFile)) {
					unlink($memoryLimitFile);
				}
				$consoleStyle->newLine();
				exit(1);
			});
		}
	}

}
