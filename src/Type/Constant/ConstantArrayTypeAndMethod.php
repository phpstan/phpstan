<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

class ConstantArrayTypeAndMethod
{

	/** @var \PHPStan\Type\Type|null */
	private $type;

	/** @var string|null */
	private $method;

	/** @var TrinaryLogic */
	private $certainty;

	private function __construct(
		?Type $type,
		?string $method,
		TrinaryLogic $certainty
	)
	{
		$this->type = $type;
		$this->method = $method;
		$this->certainty = $certainty;
	}

	public static function createConcrete(
		Type $type,
		string $method,
		TrinaryLogic $certainty
	): self
	{
		if ($certainty->no()) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		return new self($type, $method, $certainty);
	}

	public static function createUnknown(): self
	{
		return new self(null, null, TrinaryLogic::createMaybe());
	}

	public function isUnknown(): bool
	{
		return $this->type === null;
	}

	public function getType(): Type
	{
		if ($this->type === null) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return $this->type;
	}

	public function getMethod(): string
	{
		if ($this->method === null) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return $this->method;
	}

	public function getCertainty(): TrinaryLogic
	{
		return $this->certainty;
	}

}
