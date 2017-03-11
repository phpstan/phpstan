<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Nette\Configurator;
use PhpParser\Node\Stmt\Catch_;
use PHPStan\File\FileHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use PHPStan\Rules\RegistryFactory;

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
                new InputOption(self::OPTION_LEVEL, 'l', InputOption::VALUE_REQUIRED, 'Level of rule options - the higher the stricter'),
                new InputOption(ErrorsConsoleStyle::OPTION_NO_PROGRESS, null, InputOption::VALUE_NONE, 'Do not show progress bar, only results'),
                new InputOption('autoload-file', 'a', InputOption::VALUE_OPTIONAL, 'Project\'s additional autoload file path'),
                new InputOption('rules', 'r', InputOption::VALUE_OPTIONAL, "rule1,rule2,...\n".implode("\n", RegistryFactory::getRuleArgList(65535))),
            ]);
    }


    public function getAliases(): array
    {
        return ['analyze'];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($output instanceof ConsoleOutputInterface) {
            $stderr = $output->getErrorOutput();
        }

        $autoloadFile = $input->getOption('autoload-file');
        if ($autoloadFile !== null && is_file($autoloadFile)) {
            require_once $autoloadFile;
        }

        $currentWorkingDirectory = getcwd();

        $fileHelper = new FileHelper($currentWorkingDirectory);

        $rootDir = $fileHelper->normalizePath(__DIR__ . '/../..');
        $tmpDir = sys_get_temp_dir();
        $confDir = $rootDir . '/conf';

        $configurator = new Configurator();
        $configurator->defaultExtensions = [];
        $configurator->setDebugMode(true);
        $configurator->setTempDirectory($tmpDir);

        $levelOption = $input->getOption(self::OPTION_LEVEL);
        $defaultLevelUsed = false;
        if ($levelOption === null) {
            $levelOption = self::DEFAULT_LEVEL;
            $defaultLevelUsed = true;
        }
        $rules = $input->getOption('rules');
        if ($rules) {
            $rules = explode(',', $rules);
        } else {
            $rules = RegistryFactory::getRuleArgList((int)$levelOption);
        }
        RegistryFactory::setRules($rules);

        $configFiles = [
            $confDir . '/config.neon',
            $confDir . '/config.rules.neon',
        ];

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

        $errorStyle = new ErrorsConsoleStyle($input, $stderr);
        $consoleStyle = new ErrorsConsoleStyle($input, $output);
        $memoryLimitFile = $container->parameters['memoryLimitFile'];
        if (file_exists($memoryLimitFile)) {
            $errorStyle->note(sprintf(
                "PHPStan crashed in the previous run probably because of excessive memory consumption.\nIt consumed around %s of memory.\n\nTo avoid this issue, increase the memory_limit directive in your php.ini file here:\n%s\n\nIf you can't or don't want to change the system-wide memory limit, run PHPStan like this:\n%s",
                file_get_contents($memoryLimitFile),
                php_ini_loaded_file(),
                sprintf('php -d memory_limit=XX %s', implode(' ', $_SERVER['argv']))
            ));
            unlink($memoryLimitFile);
        }
        if (PHP_VERSION_ID >= 70100 && !property_exists(Catch_::class, 'types')) {
            $errorStyle->note(
                'You\'re running PHP >= 7.1, but you still have PHP-Parser version 2.x. This will lead to parse errors in case you use PHP 7.1 syntax like nullable parameters, iterable and void typehints, union exception types, or class constant visibility. Update to PHP-Parser 3.x to dismiss this message.'
            );
        }
        $this->setUpSignalHandler($errorStyle, $memoryLimitFile);
        if (!isset($container->parameters['customRulesetUsed'])) {
            $stderr->writeln('');
            $stderr->writeln('<comment>No rules detected</comment>');
            $stderr->writeln('');
            $stderr->writeln('You have the following choices:');
            $stderr->writeln('');
            $stderr->writeln('* while running the analyse option, use the <info>--level</info> option to adjust your rule level - the higher the stricter');
            $stderr->writeln('');
            $stderr->writeln(sprintf('* create your own <info>custom ruleset</info> by selecting which rules you want to check by copying the service definitions from the built-in config level files in <options=bold>%s</>.', $fileHelper->normalizePath(__DIR__ . '/../../conf')));
            $stderr->writeln('  * in this case, don\'t forget to define parameter <options=bold>customRulesetUsed</> in your config file.');
            $stderr->writeln('');
            return $this->handleReturn(1, $memoryLimitFile);
        } elseif ($container->parameters['customRulesetUsed']) {
            $defaultLevelUsed = false;
        }

        foreach ($container->parameters['autoload_files'] as $autoloadFile) {
            require_once $autoloadFile;
        }

        if (count($container->parameters['autoload_directories']) > 0) {
            $robotLoader = new \Nette\Loaders\RobotLoader();

            $robotLoader->acceptFiles = '*.' . implode(', *.', $container->parameters['fileExtensions']);

            $robotLoader->setTempDirectory($tmpDir);
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
                $errorStyle,
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
