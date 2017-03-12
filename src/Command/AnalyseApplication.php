<?php declare(strict_types = 1);

namespace PHPStan\Command;

use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\Error;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Finder\Finder;

class AnalyseApplication
{

    /**
     * @var \PHPStan\Analyser\Analyser
     */
    private $analyser;

    /**
     * @var string
     */
    private $memoryLimitFile;

    /**
     * @var string[]
     */
    private $fileExtensions;

    /**
     * @var string[]
     */
    private $ignorePathPatterns;

    public function __construct(
        Analyser $analyser,
        string $memoryLimitFile,
        array $ignorePathPatterns,
        array $fileExtensions
    ) {
        $this->analyser = $analyser;
        $this->memoryLimitFile = $memoryLimitFile;
        $this->fileExtensions = $fileExtensions;
        $this->ignorePathPatterns = $ignorePathPatterns;
    }

    /**
     * @param string[] $paths
     * @param \Symfony\Component\Console\Style\StyleInterface $style
     * @param bool $defaultLevelUsed
     * @return int
     */
    public function analyse(array $paths, StyleInterface $style, StyleInterface $errorStyle, bool $defaultLevelUsed): int
    {
        $errors = [];
        $files = [];

        $this->updateMemoryLimitFile();

        $onlyFiles = true;
        foreach ($paths as $path) {
            if (!file_exists($path)) {
                $errors[] = new Error(sprintf('<error>Path %s does not exist</error>', $path), $path);
            } elseif (is_file($path)) {
                $files[] = $path;
            } else {
                $finder = new Finder();
                $finder->filter(function (\SplFileInfo $info) {
                    foreach ($this->ignorePathPatterns as $pattern) {
                        if (preg_match("#$pattern#", $info->getPath())) {
                            return false;
                        }
                    }
                    return true;
                });
                foreach ($finder->files()->name('*.{' . implode(',', $this->fileExtensions) . '}')->in($path) as $fileInfo) {
                    $files[] = $fileInfo->getPathname();
                    $onlyFiles = false;
                }
            }
        }

        $this->updateMemoryLimitFile();

        $progressStarted = false;

        $fileOrder = 0;
        $errors = array_merge($errors, $this->analyser->analyse(
            $files,
            $onlyFiles,
            function () use ($errorStyle, &$progressStarted, $files, &$fileOrder) {
                if (!$progressStarted) {
                    $errorStyle->progressStart(count($files));
                    $progressStarted = true;
                }
                $errorStyle->progressAdvance();
                if ($fileOrder % 100 === 0) {
                    $this->updateMemoryLimitFile();
                }
                $fileOrder++;
            }
        ));

        if ($progressStarted) {
            $errorStyle->progressFinish();
        }

        if (count($errors) === 0) {
            $errorStyle->success('No errors');
            if ($defaultLevelUsed) {
                $errorStyle->note(sprintf(
                    'PHPStan is performing only the most basic checks. You can pass a higher rule level through the --%s option (the default and current level is %d) to analyse code more thoroughly.',
                    AnalyseCommand::OPTION_LEVEL,
                    AnalyseCommand::DEFAULT_LEVEL
                ));
            }
            return 0;
        }

        $currentDir = dirname($paths[0]);
        $cropFilename = function (string $filename) use ($currentDir): string {
            if ($currentDir !== '' && strpos($filename, $currentDir) === 0) {
                return substr($filename, strlen($currentDir) + 1);
            }

            return $filename;
        };

        $totalErrorsCount = count($errors);

        $style->listing($errors);

        $errorStyle->error(sprintf($totalErrorsCount === 1 ? 'Found %d error' : 'Found %d errors', $totalErrorsCount));

        return 1;
    }

    private function updateMemoryLimitFile()
    {
        $bytes = memory_get_peak_usage(true);
        $megabytes = ceil($bytes / 1024 / 1024);
        file_put_contents($this->memoryLimitFile, sprintf('%d MB', $megabytes));

        if (function_exists('pcntl_signal_dispatch')) {
            pcntl_signal_dispatch();
        }
    }
}
