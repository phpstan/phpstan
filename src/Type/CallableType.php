<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Traits\MaybeIterableTypeTrait;
use PHPStan\Type\Traits\MaybeObjectTypeTrait;
use PHPStan\Type\Traits\MaybeOffsetAccessibleTypeTrait;
use PHPStan\Type\Traits\TruthyBooleanTypeTrait;

class CallableType implements CompoundType, ParametersAcceptor
{

	use MaybeIterableTypeTrait;
	use MaybeObjectTypeTrait;
	use MaybeOffsetAccessibleTypeTrait;
	use TruthyBooleanTypeTrait;

	/** @var array<int, \PHPStan\Reflection\Native\NativeParameterReflection> */
	private $parameters;

	/** @var Type */
	private $returnType;

	/** @var bool */
	private $variadic;

	/** @var bool */
	private $isCommonCallable;

	/**
	 * @param array<int, \PHPStan\Reflection\Native\NativeParameterReflection> $parameters
	 * @param Type $returnType
	 * @param bool $variadic
	 */
	public function __construct(
		?array $parameters = null,
		?Type $returnType = null,
		bool $variadic = true
	)
	{
		if ($returnType === null) {
			$returnType = new MixedType();
		}

		$this->parameters = $parameters ?? [];
		$this->returnType = $returnType;
		$this->variadic = $variadic;
		$this->isCommonCallable = $parameters === null;
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return [];
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		if ($type instanceof CompoundType) {
			return CompoundTypeHelper::accepts($type, $this, $strictTypes);
		}

		return $this->isSuperTypeOf($type);
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		$isCallable = $type->isCallable();
		if ($isCallable->no() || $this->isCommonCallable) {
			return $isCallable;
		}

		static $scope;
		if ($scope === null) {
			$scope = new OutOfClassScope();
		}

		$variantsResult = null;
		foreach ($type->getCallableParametersAcceptors($scope) as $variant) {
			$isSuperType = CallableTypeHelper::isParametersAcceptorSuperTypeOf($this, $variant);
			if ($variantsResult === null) {
				$variantsResult = $isSuperType;
			} else {
				$variantsResult = $variantsResult->or($isSuperType);
			}
		}

		if ($variantsResult === null) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return $isCallable->and($variantsResult);
	}

	public function isSubTypeOf(Type $otherType): TrinaryLogic
	{
		if ($otherType instanceof IntersectionType || $otherType instanceof UnionType) {
			return $otherType->isSuperTypeOf($this);
		}

		return $otherType->isCallable()
			->and($otherType instanceof self ? TrinaryLogic::createYes() : TrinaryLogic::createMaybe());
	}

	public function equals(Type $type): bool
	{
		return $type instanceof self;
	}

	public function describe(VerbosityLevel $level): string
	{
		return 'callable';
	}

	public function isCallable(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	/**
	 * @param \PHPStan\Reflection\ClassMemberAccessAnswerer $scope
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getCallableParametersAcceptors(ClassMemberAccessAnswerer $scope): array
	{
		return [$this];
	}

	public function toNumber(): Type
	{
		return new ErrorType();
	}

	public function toString(): Type
	{
		return new ErrorType();
	}

	public function toInteger(): Type
	{
		return new ErrorType();
	}

	public function toFloat(): Type
	{
		return new ErrorType();
	}

	public function toArray(): Type
	{
		return new ArrayType(new MixedType(), new MixedType());
	}

	/**
	 * @return array<int, \PHPStan\Reflection\Native\NativeParameterReflection>
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	public function isVariadic(): bool
	{
		return $this->variadic;
	}

	public function getReturnType(): Type
	{
		return $this->returnType;
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self(
			(bool) $properties['isCommonCallable'] ? null : $properties['parameters'],
			$properties['returnType'],
			$properties['variadic']
		);
	}

}
