<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\Native\NativeParameterReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Generic\TemplateTypeMap;
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
		$this->parameters = $parameters ?? [];
		$this->returnType = $returnType ?? new MixedType();
		$this->variadic = $variadic;
		$this->isCommonCallable = $parameters === null && $returnType === null;
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
		if ($type instanceof CompoundType && !$type instanceof self) {
			return CompoundTypeHelper::accepts($type, $this, $strictTypes);
		}

		return $this->isSuperTypeOfInternal($type, true);
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		return $this->isSuperTypeOfInternal($type, false);
	}

	private function isSuperTypeOfInternal(Type $type, bool $treatMixedAsAny): TrinaryLogic
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
			$isSuperType = CallableTypeHelper::isParametersAcceptorSuperTypeOf($this, $variant, $treatMixedAsAny);
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

	public function isAcceptedBy(Type $acceptingType, bool $strictTypes): TrinaryLogic
	{
		return $this->isSubTypeOf($acceptingType);
	}

	public function equals(Type $type): bool
	{
		return $type instanceof self;
	}

	public function describe(VerbosityLevel $level): string
	{
		return $level->handle(
			static function (): string {
				return 'callable';
			},
			function () use ($level): string {
				return sprintf(
					'callable(%s): %s',
					implode(', ', array_map(
						static function (NativeParameterReflection $param) use ($level): string {
							return $param->getType()->describe($level);
						},
						$this->getParameters()
					)),
					$this->returnType->describe($level)
				);
			}
		);
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

	public function inferTemplateTypes(Type $receivedType): TemplateTypeMap
	{
		if ($receivedType instanceof UnionType || $receivedType instanceof IntersectionType) {
			return $receivedType->inferTemplateTypesOn($this);
		}

		if ($receivedType->isCallable()->no()) {
			return TemplateTypeMap::empty();
		}

		$parametersAcceptors = $receivedType->getCallableParametersAcceptors(new OutOfClassScope());

		$typeMap = TemplateTypeMap::empty();

		foreach ($parametersAcceptors as $parametersAcceptor) {
			$typeMap = $typeMap->union($this->inferTemplateTypesOnParametersAcceptor($receivedType, $parametersAcceptor));
		}

		return $typeMap;
	}

	private function inferTemplateTypesOnParametersAcceptor(Type $receivedType, ParametersAcceptor $parametersAcceptor): TemplateTypeMap
	{
		$typeMap = TemplateTypeMap::empty();
		$args = $parametersAcceptor->getParameters();
		$returnType = $parametersAcceptor->getReturnType();

		foreach ($this->getParameters() as $i => $param) {
			$argType = isset($args[$i]) ? $args[$i]->getType() : new NeverType();
			$paramType = $param->getType();
			$typeMap = $typeMap->union($paramType->inferTemplateTypes($argType));
		}

		return $typeMap->union($this->getReturnType()->inferTemplateTypes($returnType));
	}

	public function traverse(callable $cb): Type
	{
		if ($this->isCommonCallable) {
			return $this;
		}

		$parameters = array_map(static function (NativeParameterReflection $param) use ($cb): NativeParameterReflection {
			return new NativeParameterReflection(
				$param->getName(),
				$param->isOptional(),
				$cb($param->getType()),
				$param->passedByReference(),
				$param->isVariadic()
			);
		}, $this->getParameters());

		return new self(
			$parameters,
			$cb($this->getReturnType()),
			$this->isVariadic()
		);
	}

	public function isArray(): TrinaryLogic
	{
		return TrinaryLogic::createMaybe();
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self(
			(bool) $properties['isCommonCallable'] ? null : $properties['parameters'],
			(bool) $properties['isCommonCallable'] ? null : $properties['returnType'],
			$properties['variadic']
		);
	}

}
