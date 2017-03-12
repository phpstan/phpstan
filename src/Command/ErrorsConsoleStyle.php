<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ErrorsConsoleStyle extends \Symfony\Component\Console\Style\SymfonyStyle
{
    /** @var bool */
    private $showProgress;

    /** @var \Symfony\Component\Console\Output\OutputInterface */
    private $output;

    /** @var \Symfony\Component\Console\Helper\ProgressBar|null */
    private $progressBar;

    public function __construct(InputInterface $input, OutputInterface $output, bool $showProgress = true)
    {
        parent::__construct($input, $output);
        $this->output = $output;
        $this->showProgress = $showProgress;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param int $max
     */
    public function createProgressBar($max = 0): ProgressBar
    {
        $this->progressBar = parent::createProgressBar($max);
        return $this->progressBar;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param int $max
     */
    public function progressStart($max = 0)
    {
        if (!$this->showProgress) {
            return;
        }
        parent::progressStart($max);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param int $step
     */
    public function progressAdvance($step = 1)
    {
        if (!$this->showProgress) {
            return;
        }
        if ($this->output->isDecorated() && $step > 0) {
            $stepTime = (time() - $this->progressBar->getStartTime()) / $step;
            if ($stepTime > 0 && $stepTime < 1) {
                $this->progressBar->setRedrawFrequency(1 / $stepTime);
            } else {
                $this->progressBar->setRedrawFrequency(1);
            }
        }

        $this->progressBar->setProgress($this->progressBar->getProgress() + $step);
    }

    public function progressFinish()
    {
        if (!$this->showProgress) {
            return;
        }
        parent::progressFinish();
    }
}
