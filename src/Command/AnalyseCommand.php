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
				new InputOption(self::OPTION_LEVEL, 'l', InputOption::VALUE_REQUIRED, 'Level of rule options - the higher the stricter', self::DEFAULT_LEVEL),
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

		$levelConfigFile = sprintf('%s/config.level%s.neon', $confDir, $input->getOption(self::OPTION_LEVEL));
		if (!is_file($levelConfigFile)) {
			$output->writeln(sprintf('Level config file %s was not found.', $levelConfigFile));
			return 1;
		}

		$configFiles = [$confDir . '/config.neon', $levelConfigFile];

		$projectConfigFile = $input->getOption('configuration');
		if ($projectConfigFile !== null) {
			$projectConfigFilePath = realpath($projectConfigFile);
			if (!is_file($projectConfigFilePath)) {
				$output->writeln(sprintf('Project config file at path %s does not exist.', $projectConfigFilePath !== false ? $projectConfigFilePath : $projectConfigFile));
				return 1;
			}

			$configFiles[] = $projectConfigFilePath;
		}

		foreach ($configFiles as $configFile) {
			$configurator->addConfig($configFile);
		}

		$configurator->addParameters([
			'rootDir' => $rootDir,
		]);
		$container = $configurator->createContainer();

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
			new ErrorsConsoleStyle($input, $output)
		);
	}

}
