<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class ExpressionContext
{

	/** @var bool */
	private $isDeep;

	private function __construct(bool $isDeep)
	{
		$this->isDeep = $isDeep;
	}

	public static function createTopLevel(): self
	{
		return new self(false);
	}

	public static function createDeep(): self
	{
		return new self(true);
	}

	public function enterDeep(): self
	{
		if ($this->isDeep) {
			return $this;
		}

		return self::createDeep();
	}

	public function isDeep(): bool
	{
		return $this->isDeep;
	}

}
