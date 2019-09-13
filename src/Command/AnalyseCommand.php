<?php declare(strict_types = 1);

namespace PHPStan\Command;

use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyseCommand extends \Symfony\Component\Console\Command\Command
{

	private const NAME = 'analyse';

	public const OPTION_LEVEL = 'level';

	public const DEFAULT_LEVEL = CommandHelper::DEFAULT_LEVEL;

	protected function configure(): void
	{
		$this->setName(self::NAME)
			->setDescription('Analyses source code')
			->setDefinition([
				new InputArgument('paths', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Paths with source code to run analysis on'),
				new InputOption('paths-file', null, InputOption::VALUE_REQUIRED, 'Path to a file with a list of paths to run analysis on'),
				new InputOption('configuration', 'c', InputOption::VALUE_REQUIRED, 'Path to project configuration file'),
				new InputOption(self::OPTION_LEVEL, 'l', InputOption::VALUE_REQUIRED, 'Level of rule options - the higher the stricter'),
				new InputOption(ErrorsConsoleStyle::OPTION_NO_PROGRESS, null, InputOption::VALUE_NONE, 'Do not show progress bar, only results'),
				new InputOption('debug', null, InputOption::VALUE_NONE, 'Show debug information - which file is analysed, do not catch internal errors'),
				new InputOption('autoload-file', 'a', InputOption::VALUE_REQUIRED, 'Project\'s additional autoload file path'),
				new InputOption('error-format', null, InputOption::VALUE_REQUIRED, 'Format in which to print the result of the analysis', 'table'),
				new InputOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for analysis'),
				new InputOption('xdebug', null, InputOption::VALUE_NONE, 'Allow running with XDebug for debugging purposes'),
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
			/** @var \Symfony\Component\Console\Application|null $application */
			$application = $this->getApplication();
			if ($application === null) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			$application->setCatchExceptions(false);
			return;
		}
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$paths = $input->getArgument('paths');
		$memoryLimit = $input->getOption('memory-limit');
		$autoloadFile = $input->getOption('autoload-file');
		$configuration = $input->getOption('configuration');
		$level = $input->getOption(self::OPTION_LEVEL);
		$pathsFile = $input->getOption('paths-file');
		$allowXdebug = $input->getOption('xdebug');

		if (
			!is_array($paths)
			|| (!is_string($memoryLimit) && $memoryLimit !== null)
			|| (!is_string($autoloadFile) && $autoloadFile !== null)
			|| (!is_string($configuration) && $configuration !== null)
			|| (!is_string($level) && $level !== null)
			|| (!is_string($pathsFile) && $pathsFile !== null)
			|| (!is_bool($allowXdebug))
		) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		try {
			$inceptionResult = CommandHelper::begin(
				$input,
				$output,
				$paths,
				$pathsFile,
				$memoryLimit,
				$autoloadFile,
				$configuration,
				$level,
				$allowXdebug
			);
		} catch (\PHPStan\Command\InceptionNotSuccessfulException $e) {
			return 1;
		}

		$errorOutput = $inceptionResult->getErrorOutput();
		$errorFormat = $input->getOption('error-format');

		if (!is_string($errorFormat) && $errorFormat !== null) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$container = $inceptionResult->getContainer();
		$errorFormatterServiceName = sprintf('errorFormatter.%s', $errorFormat);
		if (!$container->hasService($errorFormatterServiceName)) {
			$errorOutput->writeln(sprintf(
				'Error formatter "%s" not found. Available error formatters are: %s',
				$errorFormat,
				implode(', ', array_map(static function (string $name): string {
					return substr($name, strlen('errorFormatter.'));
				}, $container->findByType(ErrorFormatter::class)))
			));
			return 1;
		}

		/** @var ErrorFormatter $errorFormatter */
		$errorFormatter = $container->getService($errorFormatterServiceName);

		/** @var AnalyseApplication  $application */
		$application = $container->getByType(AnalyseApplication::class);

		$debug = $input->getOption('debug');
		if (!is_bool($debug)) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return $inceptionResult->handleReturn(
			$application->analyse(
				$inceptionResult->getFiles(),
				$inceptionResult->isOnlyFiles(),
				$inceptionResult->getConsoleStyle(),
				$errorFormatter,
				$inceptionResult->isDefaultLevelUsed(),
				$debug,
				$inceptionResult->getProjectConfigFile()
			)
		);
	}

}
