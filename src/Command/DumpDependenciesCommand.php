<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Nette\Utils\Json;
use PHPStan\Dependency\DependencyDumper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DumpDependenciesCommand extends \Symfony\Component\Console\Command\Command
{

	private const NAME = 'dump-deps';

	protected function configure(): void
	{
		$this->setName(self::NAME)
			->setDescription('Dumps files dependency tree')
			->setDefinition([
				new InputArgument('paths', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Paths with source code to run dump on'),
				new InputOption('configuration', 'c', InputOption::VALUE_REQUIRED, 'Path to project configuration file'),
				new InputOption(ErrorsConsoleStyle::OPTION_NO_PROGRESS, null, InputOption::VALUE_NONE, 'Do not show progress bar, only results'),
				new InputOption('autoload-file', 'a', InputOption::VALUE_REQUIRED, 'Project\'s additional autoload file path'),
				new InputOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for the run'),
			]);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		try {
			/** @var string[] $paths */
			$paths = $input->getArgument('paths');

			/** @var string|null $memoryLimit */
			$memoryLimit = $input->getOption('memory-limit');

			/** @var string|null $autoloadFile */
			$autoloadFile = $input->getOption('autoload-file');

			/** @var string|null $configurationFile */
			$configurationFile = $input->getOption('configuration');
			$inceptionResult = CommandHelper::begin(
				$input,
				$output,
				$paths,
				$memoryLimit,
				$autoloadFile,
				$configurationFile,
				'0' // irrelevant but prevents an error when a config file is passed
			);
		} catch (\PHPStan\Command\InceptionNotSuccessfulException $e) {
			return 1;
		}

		$consoleStyle = $inceptionResult->getConsoleStyle();

		/** @var DependencyDumper $dependencyDumper */
		$dependencyDumper = $inceptionResult->getContainer()->getByType(DependencyDumper::class);
		$dependencies = $dependencyDumper->dumpDependencies(
			$inceptionResult->getFiles(),
			static function (int $count) use ($consoleStyle): void {
				$consoleStyle->progressStart($count);
			},
			static function () use ($consoleStyle): void {
				$consoleStyle->progressAdvance();
			}
		);
		$consoleStyle->progressFinish();
		$consoleStyle->writeln(Json::encode($dependencies, Json::PRETTY));

		return $inceptionResult->handleReturn(0);
	}

}
