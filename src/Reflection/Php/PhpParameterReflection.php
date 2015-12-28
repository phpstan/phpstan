<?php declare(strict_types=1);

namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;

class PhpParameterReflection implements ParameterReflection
{

	/** @var \ReflectionParameter */
	private $reflection;

	/** @var \PHPStan\Type\Type */
	private $type;

	public function __construct(\ReflectionParameter $reflection)
	{
		$this->reflection = $reflection;
	}

	public function isOptional(): bool
	{
		return $this->reflection->isOptional();
	}

	public function getName(): string
	{
		return $this->reflection->getName();
	}

	public function getType(): Type
	{
		if ($this->type === null) {
			$phpTypeReflection = $this->reflection->getType();
			if ($phpTypeReflection === null) {
				$this->type = new MixedType(true);
			} else {
				$this->type = TypehintHelper::getTypeObjectFromTypehint(
					(string) $phpTypeReflection,
					$phpTypeReflection->allowsNull(),
					$this->reflection->getDeclaringClass() !== null ? $this->reflection->getDeclaringClass()->getName() : null
				);
			}
		}

		return $this->type;
	}

}
