<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Symfony\Component\Console\Output\OutputInterface;

class ThrottledProgressBar extends \Symfony\Component\Console\Helper\ProgressBar
{

	/** @var \Symfony\Component\Console\Output\OutputInterface */
	private $output;

	public function __construct(OutputInterface $output, int $max = 0)
	{
		parent::__construct($output, $max);
		$this->output = $output;
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.Typehints.TypeHintDeclaration.missingParameterTypeHint
	 * @param int $step
	 */
	public function setProgress($step)
	{
		if ($this->output->isDecorated() && $step > 0) {
			$stepTime = (time() - $this->getStartTime()) / $step;
			if ($stepTime > 0 && $stepTime < 1) {
				$this->setRedrawFrequency(1 / $stepTime);
			} else {
				$this->setRedrawFrequency(1);
			}
		}

		parent::setProgress($step);
	}

}
