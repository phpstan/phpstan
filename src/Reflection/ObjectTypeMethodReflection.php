<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Reflection\Php\DummyParameter;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;

class ObjectTypeMethodReflection implements MethodReflection
{

	/** @var \PHPStan\Type\ObjectType */
	private $objectType;

	/** @var \PHPStan\Reflection\MethodReflection */
	private $reflection;

	/** @var \PHPStan\Reflection\ParametersAcceptor[]|null */
	private $variants;

	public function __construct(
		ObjectType $objectType,
		MethodReflection $reflection
	)
	{
		$this->objectType = $objectType;
		$this->reflection = $reflection;
	}

	public function getDeclaringClass(): ClassReflection
	{
		return $this->reflection->getDeclaringClass();
	}

	public function isStatic(): bool
	{
		return $this->reflection->isStatic();
	}

	public function isPrivate(): bool
	{
		return $this->reflection->isPrivate();
	}

	public function isPublic(): bool
	{
		return $this->reflection->isPublic();
	}

	/** @return string|false */
	public function getDocComment()
	{
		return $this->reflection->getDocComment();
	}

	public function getName(): string
	{
		return $this->reflection->getName();
	}

	public function getPrototype(): ClassMemberReflection
	{
		return $this->reflection->getPrototype();
	}

	/**
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getVariants(): array
	{
		if ($this->variants !== null) {
			return $this->variants;
		}

		$variants = [];
		foreach ($this->reflection->getVariants() as $variant) {
			$variants[] = $this->processVariant($variant);
		}

		$this->variants = $variants;

		return $this->variants;
	}

	private function processVariant(ParametersAcceptor $acceptor): ParametersAcceptor
	{
		return new FunctionVariant(
			$acceptor->getTemplateTypeMap(),
			$acceptor->getResolvedTemplateTypeMap(),
			array_map(function (ParameterReflection $parameter): ParameterReflection {
				$type = TypeTraverser::map($parameter->getType(), function (Type $type, callable $traverse): Type {
					if ($type instanceof StaticType) {
						return $traverse($this->objectType);
					}

					return $traverse($type);
				});

				return new DummyParameter(
					$parameter->getName(),
					$type,
					$parameter->isOptional(),
					$parameter->passedByReference(),
					$parameter->isVariadic(),
					$parameter->getDefaultValue()
				);
			}, $acceptor->getParameters()),
			$acceptor->isVariadic(),
			$acceptor->getReturnType()
		);
	}

	public function isDeprecated(): TrinaryLogic
	{
		return $this->reflection->isDeprecated();
	}

	public function getDeprecatedDescription(): ?string
	{
		return $this->reflection->getDeprecatedDescription();
	}

	public function isFinal(): TrinaryLogic
	{
		return $this->reflection->isFinal();
	}

	public function isInternal(): TrinaryLogic
	{
		return $this->reflection->isInternal();
	}

	public function getThrowType(): ?Type
	{
		return $this->reflection->getThrowType();
	}

	public function hasSideEffects(): TrinaryLogic
	{
		return $this->reflection->hasSideEffects();
	}

}
