<?php declare(strict_types = 1);

namespace PHPStan\Command;

use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use PHPStan\Command\ProgressPrinter\ProgressPrinter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyseCommand extends \Symfony\Component\Console\Command\Command
{

	private const NAME = 'analyse';

	public const OPTION_LEVEL = 'level';

	public const OPTION_NO_PROGRESS = 'no-progress';

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
				new InputOption(self::OPTION_NO_PROGRESS, null, InputOption::VALUE_NONE, 'Do not show progress, only results'),
				new InputOption('debug', null, InputOption::VALUE_NONE, 'Show debug information - which file is analysed, do not catch internal errors'),
				new InputOption('autoload-file', 'a', InputOption::VALUE_REQUIRED, 'Project\'s additional autoload file path'),
				new InputOption('error-format', null, InputOption::VALUE_REQUIRED, 'Format in which to print the result of the analysis', 'table'),
				new InputOption('progress-printer', null, InputOption::VALUE_REQUIRED, 'Type of printer used to output progress', 'bar'),
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
		$paths = $input->getArgument('paths');
		$memoryLimit = $input->getOption('memory-limit');
		$autoloadFile = $input->getOption('autoload-file');
		$configuration = $input->getOption('configuration');
		$level = $input->getOption(self::OPTION_LEVEL);
		$pathsFile = $input->getOption('paths-file');

		if (
			!is_array($paths)
			|| (!is_string($memoryLimit) && $memoryLimit !== null)
			|| (!is_string($autoloadFile) && $autoloadFile !== null)
			|| (!is_string($configuration) && $configuration !== null)
			|| (!is_string($level) && $level !== null)
			|| (!is_string($pathsFile) && $pathsFile !== null)
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
				$level
			);
		} catch (\PHPStan\Command\InceptionNotSuccessfulException $e) {
			return 1;
		}

		$errorOutput = $inceptionResult->getErrorOutput();
		$errorConsoleStyle = new ErrorsConsoleStyle($input, $errorOutput);
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
				implode(', ', array_map(static function (string $name) {
					return substr($name, strlen('errorFormatter.'));
				}, $container->findByType(ErrorFormatter::class)))
			));
			return 1;
		}

		/** @var ErrorFormatter $errorFormatter */
		$errorFormatter = $container->getService($errorFormatterServiceName);

		$debug = (bool) $input->getOption('debug');
		$noProgress = (bool) $input->getOption(self::OPTION_NO_PROGRESS);
		if ($debug) {
			/** @var ProgressPrinter $progressPrinter */
			$progressPrinter = $container->getService('progressPrinter.debug');
		} elseif ($noProgress) {
			/** @var ProgressPrinter $progressPrinter */
			$progressPrinter = $container->getService('progressPrinter.void');
		} else {
			$progressPrinter = $input->getOption('progress-printer');
			if (!is_string($progressPrinter)) {
				throw new \PHPStan\ShouldNotHappenException();
			}

			$progressPrinterServiceName = sprintf('progressPrinter.%s', $progressPrinter);
			if (!$container->hasService($progressPrinterServiceName)) {
				$errorOutput->writeln(sprintf(
					'Progress printer "%s" not found. Available progress printers are: %s',
					$progressPrinter,
					implode(', ', array_map(static function (string $name) {
						return substr($name, strlen('progressPrinter.'));
					}, $container->findByType(ProgressPrinter::class)))
				));
				return 1;
			}
			/** @var ProgressPrinter $progressPrinter */
			$progressPrinter = $container->getService($progressPrinterServiceName);
		}

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
				$errorConsoleStyle,
				$errorFormatter,
				$progressPrinter,
				$inceptionResult->isDefaultLevelUsed(),
				$debug
			)
		);
	}

}
