<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Nette\Configurator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\Debugger;

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
			]);
	}


	public function getAliases(): array
	{
		return ['analyze'];
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$rootDir = realpath(__DIR__ . '/../..');
		$tmpDir = $rootDir . '/tmp';
		$confDir = $rootDir . '/conf';

		$configurator = new Configurator();
		$configurator->defaultExtensions = [];
		$configurator->setDebugMode(true);
		$configurator->setTempDirectory($tmpDir);
		$configurator->enableDebugger($tmpDir . '/log');

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
			$projectConfigRealFilePath = realpath($projectConfigFile);
			if (!is_file($projectConfigFile)) {
				$output->writeln(sprintf('Project config file at path %s does not exist.', $projectConfigRealFilePath !== false ? $projectConfigRealFilePath : $projectConfigFile));
				return 1;
			}

			$configFiles[] = $projectConfigRealFilePath;
		}

		foreach ($configFiles as $configFile) {
			$configurator->addConfig($configFile);
		}

		$configurator->addParameters([
			'rootDir' => $rootDir,
		]);
		$container = $configurator->createContainer();
		if (!isset($container->parameters['customRulesetUsed'])) {
			$output->writeln('');
			$output->writeln('<comment>No rules detected</comment>');
			$output->writeln('');
			$output->writeln('You have the following choices:');
			$output->writeln('');
			$output->writeln('* while running the analyse option, use the <info>--level</info> option to adjust your rule level - the higher the stricter');
			$output->writeln('');
			$output->writeln(sprintf('* create your own <info>custom ruleset</info> by selecting which rules you want to check by copying the service definitions from the built-in config level files in <options=bold>%s</>.', realpath(__DIR__ . '/../../conf')));
			$output->writeln('  * in this case, don\'t forget to define parameter <options=bold>customRulesetUsed</> in your config file.');
			$output->writeln('');
			return 1;
		} elseif ($container->parameters['customRulesetUsed']) {
			$defaultLevelUsed = false;
		}

		Debugger::$browser = $container->parameters['debug_cli_browser'];

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
		return $application->analyse(
			$input->getArgument('paths'),
			new ErrorsConsoleStyle($input, $output),
			$defaultLevelUsed
		);
	}

}
