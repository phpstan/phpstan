<?php declare(strict_types = 1);

namespace PHPStan\Command;

use PhpParser\Node\Stmt\Catch_;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use PHPStan\Rules\RegistryFactory;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;

class AnalyseCommand extends \Symfony\Component\Console\Command\Command
{
    const NAME = 'analyse';

    const OPTION_LEVEL = 'level';
    const OPTION_NO_PROGRESS = 'no-progress';
    const OPTION_RULE = 'rule';
    const OPTION_AUTOLOAD_FILE = 'autoload-file';
    const OPTION_EXCLUED_RULE = 'exclude-rule';
    const OPTION_IGNORE_PATH = 'ignore-path';
    const OPTION_IGNORE_ERROR = 'ignore-error';
    const OPTION_EXTENSION = 'extension';

    const DEFAULT_LEVEL = 0;

    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Analyses source code')
            ->setDefinition([
                new InputArgument('paths', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Paths with source code to run analysis on'),
                new InputOption(self::OPTION_LEVEL, 'l', InputOption::VALUE_REQUIRED, 'Level of rule options - the higher the stricter'),
                new InputOption(self::OPTION_NO_PROGRESS, null, InputOption::VALUE_NONE, 'Do not show progress bar, only results'),
                new InputOption(self::OPTION_AUTOLOAD_FILE, 'a', InputOption::VALUE_OPTIONAL|InputOption::VALUE_IS_ARRAY, 'Project\'s additional autoload file path'),
                new InputOption(self::OPTION_RULE, 'r', InputOption::VALUE_OPTIONAL|InputOption::VALUE_IS_ARRAY, "Rule to be used. use FQCN for custom rule. the builtin rules:\n".implode("\n", RegistryFactory::getRuleArgList(65535))),
                new InputOption(self::OPTION_EXCLUED_RULE, 'R', InputOption::VALUE_OPTIONAL|InputOption::VALUE_IS_ARRAY, "Rule to be excluded"),
                new InputOption(self::OPTION_IGNORE_PATH, 'P', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Preg pattern **WITHOUT DELIMITER** for file path to be ignored'),
                new InputOption(self::OPTION_IGNORE_ERROR, 'E', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Preg pattern **WITHOUT DELIMITER** for error to be ignored'),
                new InputOption(self::OPTION_EXTENSION, 'x', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Extension class name to be used, must be FQCN'),
            ]);
    }


    public function getAliases(): array
    {
        return ['analyze'];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $autoloadFiles = $input->getOption(self::OPTION_AUTOLOAD_FILE);
        foreach ($autoloadFiles as $autoloadFile) {
            if (is_file($autoloadFile)) {
                require_once $autoloadFile;
            }
        }

        $rootDir = __DIR__ . '/../..';
        $confDir = $rootDir . '/conf';

        $builder = new \DI\ContainerBuilder();
        $builder->addDefinitions($confDir.'/config.php');

        $extensionDefinitions = [];
        $extensionNames = $input->getOption(self::OPTION_EXTENSION);
        foreach ($extensionNames as $extensionName) {
            if (!class_exists($extensionName, true)) {
                continue;
            }
            $interfaces = class_implements($extensionName);
            foreach ($interfaces as $interface) {
                switch ($interface) {
                case PropertiesClassReflectionExtension::class:
                case MethodsClassReflectionExtension::class:
                case DynamicMethodReturnTypeExtension::class:
                case DynamicStaticMethodReturnTypeExtension::class:
                    $extensionDefinitions[$interface][] = \DI\get($extensionName);
                    break;
                }
            }
        }

        foreach ($extensionDefinitions as $interface => $extensions) {
            $builder->addDefinitions([
                $interface => \DI\add($extensions), // use add to append
            ]);
        }

        $container = $builder->build();
        $container->set(\Interop\Container\ContainerInterface::class, $container);

        $ignorePathPatterns = $input->getOption(self::OPTION_IGNORE_PATH);
        if ($ignorePathPatterns) {
            $container->set('ignorePathPatterns', $ignorePathPatterns);
        }
        $ignoreErrors = $input->getOption(self::OPTION_IGNORE_ERROR);

        if ($ignoreErrors) {
            $container->set('ignoreErrors', $ignoreErrors);
        }

        $levelOption = $input->getOption(self::OPTION_LEVEL);
        $defaultLevelUsed = false;
        if ($levelOption === null) {
            $levelOption = self::DEFAULT_LEVEL;
            $defaultLevelUsed = true;
        } else {
            $levelOption = (int) $levelOption;
        }

        switch ($levelOption) {
        case 2:
            $container->set('checkThisOnly', false);
            break;
        case 5:
            $container->set('checkFunctionArgumentTypes', true);
            $container->set('enableUnionTypes', true);
            break;
        }

        $rules = $input->getOption(self::OPTION_RULE);
        if (!$rules) {
            $rules = RegistryFactory::getRuleArgList($levelOption);
        }

        $excludeRules = $input->getOption(self::OPTION_EXCLUED_RULE);
        if ($excludeRules) {
            $rules = array_values(array_diff($rules, $excludeRules));
        }

        RegistryFactory::setRules($rules);

        $showProgress = !$input->getOption(self::OPTION_NO_PROGRESS);
        $stderr = ($output instanceof ConsoleOutputInterface) ? $output->getErrorOutput() : $output;
        $errorStyle = new ErrorsConsoleStyle($input, $stderr, $showProgress);
        $consoleStyle = new ErrorsConsoleStyle($input, $output);
        $memoryLimitFile = $container->get('memoryLimitFile');
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

        $application = $container->get(AnalyseApplication::class);
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
