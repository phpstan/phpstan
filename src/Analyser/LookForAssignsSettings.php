<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class LookForAssignsSettings
{

	/** @var bool */
	private $respectAllEarlyTermination;

	/** @var bool */
	private $respectContinue;

	public function __construct(
		bool $respectAllEarlyTermination,
		bool $respectContinue
	)
	{
		$this->respectAllEarlyTermination = $respectAllEarlyTermination;
		$this->respectContinue = $respectContinue;
	}

	public function isRespectAllEarlyTermination(): bool
	{
		return $this->respectAllEarlyTermination;
	}

	public function isRespectContinue(): bool
	{
		return $this->respectContinue;
	}

}
