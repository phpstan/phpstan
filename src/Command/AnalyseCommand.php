<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Nette\DI\Config\Loader;
use Nette\DI\Helpers;
use PhpParser\Node\Stmt\Catch_;
use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\File\FileHelper;
use PHPStan\Type\TypeCombinator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use const PHP_VERSION_ID;

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
				new InputOption('debug', null, InputOption::VALUE_NONE, 'Show debug information - which file is analysed, do not catch internal errors'),
				new InputOption('autoload-file', 'a', InputOption::VALUE_REQUIRED, 'Project\'s additional autoload file path'),
				new InputOption('errorFormat', null, InputOption::VALUE_REQUIRED, 'Format in which to print the result of the analysis', 'table'),
				new InputOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for analysis'),
			]);
	}


	public function getAliases(): array
	{
		return ['analyze'];
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$consoleStyle = new ErrorsConsoleStyle($input, $output);

		if (PHP_VERSION_ID >= 70100 && $output instanceof ConsoleOutput) {
			$errorOutput = $output->getErrorOutput();
			$errorOutput->writeln('');
			$errorOutput->writeln("⚠️  You're running a really old version of PHPStan.️");
			$errorOutput->writeln('');
			$errorOutput->writeln('The last release in the 0.9.x series with new features');

			$lastRelease = new \DateTimeImmutable('2018-01-28 00:00:00');
			$daysSince = (time() - $lastRelease->getTimestamp()) / 60 / 60 / 24;
			if ($daysSince > 700) {
				$errorOutput->writeln('and bugfixes was released on <fg=red>January 28th 2018</>,');
				$errorOutput->writeln(sprintf('that\'s <fg=red>%d days ago.</>', floor($daysSince)));
			} else {
				$errorOutput->writeln('and bugfixes was released on <fg=red>January 28th 2018</>.');
			}
			$errorOutput->writeln('');

			$errorOutput->writeln('Since then more than <fg=cyan>30 new PHPStan versions</> were released');
			$errorOutput->writeln('with hundreds of new features, bugfixes, and other');
			$errorOutput->writeln('quality of life improvements.');
			$errorOutput->writeln('');

			$errorOutput->writeln("To learn about what you're missing out on, check out");
			$errorOutput->writeln('this blog with articles about the latest major releases:');
			$errorOutput->writeln('<options=underscore>https://medium.com/@ondrejmirtes/</>');
			$errorOutput->writeln('');

			$errorOutput->writeln('Historically, the main two blockers preventing');
			$errorOutput->writeln('people from upgrading were:');
			$errorOutput->writeln('1) Composer conflicts with other dependencies.');
			$errorOutput->writeln('2) Errors discovered by the new PHPStan version and no time');
			$errorOutput->writeln('   to fix them to get to a green build again.');
			$errorOutput->writeln('');

			$errorOutput->writeln('Today, neither of them is a problem anymore thanks to:');
			$errorOutput->writeln('1) phpstan/phpstan package having zero external dependencies.');
			$errorOutput->writeln('2) Baseline feature letting you use the latest release');
			$errorOutput->writeln('   right away even with pre-existing errors.');
			$errorOutput->writeln('   Read more about it here: <options=underscore>https://link.medium.com/JLkkGcHA32</>');
			$errorOutput->writeln('');

			$errorOutput->writeln('Upgrade today to <fg=green>PHPStan 0.12</> by using');
			$errorOutput->writeln('<fg=cyan>"phpstan/phpstan": "^0.12"</> in your <fg=cyan>composer.json</>.');
			$errorOutput->writeln('');
		}

		$memoryLimit = $input->getOption('memory-limit');
		if ($memoryLimit !== null) {
			if (!preg_match('#^-?\d+[kMG]?$#i', $memoryLimit)) {
				$consoleStyle->error(sprintf('Invalid memory limit format "%s".', $memoryLimit));
				return 1;
			}
			if (ini_set('memory_limit', $memoryLimit) === false) {
				$consoleStyle->error(sprintf('Memory limit "%s" cannot be set.', $memoryLimit));
				return 1;
			}
		}

		$currentWorkingDirectory = getcwd();
		$fileHelper = new FileHelper($currentWorkingDirectory);

		/** @var string|null $autoloadFile */
		$autoloadFile = $input->getOption('autoload-file');
		if ($autoloadFile !== null && is_file($autoloadFile)) {
			$autoloadFile = $fileHelper->normalizePath($autoloadFile);
			if (is_file($autoloadFile)) {
				require_once $autoloadFile;
			}
		}

		/** @var string|null $projectConfigFile */
		$projectConfigFile = $input->getOption('configuration');
		$levelOption = $input->getOption(self::OPTION_LEVEL);
		$defaultLevelUsed = false;
		if ($projectConfigFile === null && $levelOption === null) {
			$levelOption = self::DEFAULT_LEVEL;
			$defaultLevelUsed = true;
		}

		$containerFactory = new ContainerFactory($currentWorkingDirectory);

		$additionalConfigFiles = [];
		if ($levelOption !== null) {
			$levelConfigFile = sprintf('%s/config.level%s.neon', $containerFactory->getConfigDirectory(), $levelOption);
			if (!is_file($levelConfigFile)) {
				$output->writeln(sprintf('Level config file %s was not found.', $levelConfigFile));
				return 1;
			}

			$additionalConfigFiles[] = $levelConfigFile;
		}

		if ($projectConfigFile !== null) {
			if (!is_file($projectConfigFile)) {
				$output->writeln(sprintf('Project config file at path %s does not exist.', $projectConfigFile));
				return 1;
			}

			$additionalConfigFiles[] = $projectConfigFile;

			$loader = new Loader();
			$projectConfig = $loader->load($projectConfigFile, null);
			if (isset($projectConfig['parameters']['tmpDir'])) {
				$tmpDir = Helpers::expand($projectConfig['parameters']['tmpDir'], [
					'rootDir' => $containerFactory->getRootDirectory(),
					'currentWorkingDirectory' => $containerFactory->getCurrentWorkingDirectory(),
				]);
			}
		}

		if (!isset($tmpDir)) {
			$tmpDir = sys_get_temp_dir() . '/phpstan';
			if (!@mkdir($tmpDir, 0777, true) && !is_dir($tmpDir)) {
				$consoleStyle->error(sprintf('Cannot create a temp directory %s', $tmpDir));
				return 1;
			}
		}

		$container = $containerFactory->create($tmpDir, $additionalConfigFiles);
		$memoryLimitFile = $container->parameters['memoryLimitFile'];
		if (file_exists($memoryLimitFile)) {
			$consoleStyle->note(sprintf(
				"PHPStan crashed in the previous run probably because of excessive memory consumption.\nIt consumed around %s of memory.\n\nTo avoid this issue, allow to use more memory with the --memory-limit option.",
				file_get_contents($memoryLimitFile)
			));
			unlink($memoryLimitFile);
		}
		if (PHP_VERSION_ID >= 70100 && !property_exists(Catch_::class, 'types')) {
			$consoleStyle->note(
				'You\'re running PHP >= 7.1, but you still have PHP-Parser version 2.x. This will lead to parse errors in case you use PHP 7.1 syntax like nullable parameters, iterable and void typehints, union exception types, or class constant visibility. Update to PHP-Parser 3.x to dismiss this message.'
			);
		}
		$errorFormat = $input->getOption('errorFormat');
		$errorFormatterServiceName = sprintf('errorFormatter.%s', $errorFormat);
		if (!$container->hasService($errorFormatterServiceName)) {
			$consoleStyle->error(sprintf(
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
			return 1;
		} elseif ($container->parameters['customRulesetUsed']) {
			$defaultLevelUsed = false;
		}

		foreach ($container->parameters['autoload_files'] as $autoloadFile) {
			require_once $fileHelper->normalizePath($autoloadFile);
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

			$robotLoader->register();
		}

		TypeCombinator::setUnionTypesEnabled($container->parameters['checkUnionTypes']);

		/** @var string[] $paths */
		$paths = $input->getArgument('paths');

		/** @var bool $debug */
		$debug = $input->getOption('debug');

		/** @var \PHPStan\Command\AnalyseApplication $application */
		$application = $container->getByType(AnalyseApplication::class);
		return $this->handleReturn(
			$application->analyse(
				$paths,
				$consoleStyle,
				$errorFormatter,
				$defaultLevelUsed,
				$debug
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
