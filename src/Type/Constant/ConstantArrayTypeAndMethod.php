<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PHPStan\Type\Type;

class ConstantArrayTypeAndMethod
{

	/** @var \PHPStan\Type\Type|null */
	private $type;

	/** @var string|null */
	private $method;

	private function __construct(?Type $type, ?string $method)
	{
		$this->type = $type;
		$this->method = $method;
	}

	public static function createConcrete(Type $type, string $method): self
	{
		return new self($type, $method);
	}

	public static function createUnknown(): self
	{
		return new self(null, null);
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

}
