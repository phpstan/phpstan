<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

class TemplateTypeScope
{

	/** @var string|null */
	private $className;

	/** @var string|null */
	private $functionName;

	public function __construct(?string $className, ?string $functionName)
	{
		$this->className = $className;
		$this->functionName = $functionName;
	}

	public function equals(self $other): bool
	{
		return $this->className === $other->className
			&& $this->functionName === $other->functionName;
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
