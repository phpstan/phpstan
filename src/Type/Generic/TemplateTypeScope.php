<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

class TemplateTypeScope
{

	/** @var string|null */
	private $className;

	/** @var string|null */
	private $functionName;

	public static function createWithFunction(string $functionName): self
	{
		return new self(null, $functionName);
	}

	public static function createWithMethod(string $className, string $functionName): self
	{
		return new self($className, $functionName);
	}

	public static function createWithClass(string $className): self
	{
		return new self($className, null);
	}

	private function __construct(?string $className, ?string $functionName)
	{
		$this->className = $className;
		$this->functionName = $functionName;
	}

	public function getClassName(): ?string
	{
		return $this->className;
	}

	public function getFunctionName(): ?string
	{
		return $this->functionName;
	}

	public function equals(self $other): bool
	{
		return $this->className === $other->className
			&& $this->functionName === $other->functionName;
	}

	public function describe(): string
	{
		if ($this->className === null) {
			return sprintf('function %s()', $this->functionName);
		}

		if ($this->functionName === null) {
			return sprintf('class %s', $this->className);
		}

		return sprintf('method %s::%s()', $this->className, $this->functionName);
	}

	/**
	 * @param mixed[] $properties
	 */
	public static function __set_state(array $properties): self
	{
		return new self(
			$properties['className'],
			$properties['functionName']
		);
	}

}
