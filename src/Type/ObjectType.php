<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ConstantReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\Php\UniversalObjectCratesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\TrivialParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Traits\NonGenericTypeTrait;
use PHPStan\Type\Traits\TruthyBooleanTypeTrait;

class ObjectType implements TypeWithClassName, SubtractableType
{

	use TruthyBooleanTypeTrait;
	use NonGenericTypeTrait;

	private const EXTRA_OFFSET_CLASSES = ['SimpleXMLElement', 'DOMNodeList'];

	/** @var string */
	private $className;

	/** @var \PHPStan\Type\Type|null */
	private $subtractedType;

	public function __construct(
		string $className,
		?Type $subtractedType = null
	)
	{
		$this->className = $className;
		$this->subtractedType = $subtractedType;
	}

	private static function createFromReflection(ClassReflection $reflection): self
	{
		if (!$reflection->isGeneric()) {
			return new ObjectType($reflection->getName());
		}

		return new GenericObjectType(
			$reflection->getName(),
			$reflection->typeMapToList($reflection->getActiveTemplateTypeMap())
		);
	}

	public function getClassName(): string
	{
		return $this->className;
	}

	public function hasProperty(string $propertyName): TrinaryLogic
	{
		$classReflection = $this->getClassReflection();
		if ($classReflection === null) {
			return TrinaryLogic::createMaybe();
		}

		if ($classReflection->hasProperty($propertyName)) {
			return TrinaryLogic::createYes();
		}

		if ($classReflection->isFinal()) {
			return TrinaryLogic::createNo();
		}

		return TrinaryLogic::createMaybe();
	}

	public function getProperty(string $propertyName, ClassMemberAccessAnswerer $scope): PropertyReflection
	{
		$classReflection = $this->getClassReflection();
		if ($classReflection === null) {
			throw new \PHPStan\Broker\ClassNotFoundException($this->className);
		}

		return $classReflection->getProperty($propertyName, $scope);
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return [$this->className];
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		if ($type instanceof StaticType) {
			return $this->checkSubclassAcceptability($type->getBaseClass());
		}

		if ($type instanceof CompoundType) {
			return CompoundTypeHelper::accepts($type, $this, $strictTypes);
		}

		if ($type instanceof ClosureType) {
			return $this->isInstanceOf(\Closure::class);
		}

		if (!$type instanceof TypeWithClassName) {
			return TrinaryLogic::createNo();
		}

		return $this->checkSubclassAcceptability($type->getClassName());
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		if ($type instanceof ObjectWithoutClassType) {
			if ($type->getSubtractedType() !== null) {
				$isSuperType = $type->getSubtractedType()->isSuperTypeOf($this);
				if ($isSuperType->yes()) {
					return TrinaryLogic::createNo();
				}
			}
			return TrinaryLogic::createMaybe();
		}

		if (!$type instanceof TypeWithClassName) {
			return TrinaryLogic::createNo();
		}

		if ($this->subtractedType !== null) {
			$isSuperType = $this->subtractedType->isSuperTypeOf($type);
			if ($isSuperType->yes()) {
				return TrinaryLogic::createNo();
			}
		}

		if (
			$type instanceof SubtractableType
			&& $type->getSubtractedType() !== null
		) {
			$isSuperType = $type->getSubtractedType()->isSuperTypeOf($this);
			if ($isSuperType->yes()) {
				return TrinaryLogic::createNo();
			}
		}

		$thisClassName = $this->className;
		$thatClassName = $type->getClassName();

		if ($thatClassName === $thisClassName) {
			return TrinaryLogic::createYes();
		}

		$broker = Broker::getInstance();

		if (!$broker->hasClass($thisClassName) || !$broker->hasClass($thatClassName)) {
			return TrinaryLogic::createMaybe();
		}

		$thisClassReflection = $broker->getClass($thisClassName);
		$thatClassReflection = $broker->getClass($thatClassName);

		if ($thisClassReflection->getName() === $thatClassReflection->getName()) {
			return TrinaryLogic::createYes();
		}

		if ($thatClassReflection->isSubclassOf($thisClassName)) {
			return TrinaryLogic::createYes();
		}

		if ($thisClassReflection->isSubclassOf($thatClassName)) {
			return TrinaryLogic::createMaybe();
		}

		if ($thisClassReflection->isInterface() && !$thatClassReflection->getNativeReflection()->isFinal()) {
			return TrinaryLogic::createMaybe();
		}

		if ($thatClassReflection->isInterface() && !$thisClassReflection->getNativeReflection()->isFinal()) {
			return TrinaryLogic::createMaybe();
		}

		return TrinaryLogic::createNo();
	}

	public function equals(Type $type): bool
	{
		if (!$type instanceof self) {
			return false;
		}

		if ($this->className !== $type->className) {
			return false;
		}

		if ($this->subtractedType === null) {
			if ($type->subtractedType === null) {
				return true;
			}

			return false;
		}

		if ($type->subtractedType === null) {
			return false;
		}

		return $this->subtractedType->equals($type->subtractedType);
	}

	private function checkSubclassAcceptability(string $thatClass): TrinaryLogic
	{
		if ($this->className === $thatClass) {
			return TrinaryLogic::createYes();
		}

		$broker = Broker::getInstance();

		if (!$broker->hasClass($this->className) || !$broker->hasClass($thatClass)) {
			return TrinaryLogic::createNo();
		}

		$thisReflection = $broker->getClass($this->className);
		$thatReflection = $broker->getClass($thatClass);

		if ($thisReflection->getName() === $thatReflection->getName()) {
			// class alias
			return TrinaryLogic::createYes();
		}

		if ($thisReflection->isInterface() && $thatReflection->isInterface()) {
			return TrinaryLogic::createFromBoolean(
				$thatReflection->getNativeReflection()->implementsInterface($this->className)
			);
		}

		return TrinaryLogic::createFromBoolean(
			$thatReflection->isSubclassOf($this->className)
		);
	}

	public function describe(VerbosityLevel $level): string
	{
		return $level->handle(
			function (): string {
				$classReflection = $this->getClassReflection();
				if ($classReflection === null) {
					return $this->className;
				}

				return $classReflection->getDisplayName(false);
			},
			function (): string {
				return $this->className;
			},
			function () use ($level): string {
				$description = $this->className;
				if ($this->subtractedType !== null) {
					$description .= sprintf('~%s', $this->subtractedType->describe($level));
				}

				return $description;
			}
		);
	}

	public function toNumber(): Type
	{
		if ($this->isInstanceOf('SimpleXMLElement')->yes()) {
			return new UnionType([
				new FloatType(),
				new IntegerType(),
			]);
		}

		return new ErrorType();
	}

	public function toInteger(): Type
	{
		if ($this->isInstanceOf('SimpleXMLElement')->yes()) {
			return new IntegerType();
		}

		return new ErrorType();
	}

	public function toFloat(): Type
	{
		if ($this->isInstanceOf('SimpleXMLElement')->yes()) {
			return new FloatType();
		}
		return new ErrorType();
	}

	public function toString(): Type
	{
		$classReflection = $this->getClassReflection();
		if ($classReflection === null) {
			return new ErrorType();
		}

		if ($classReflection->hasNativeMethod('__toString')) {
			return new StringType();
		}

		return new ErrorType();
	}

	public function toArray(): Type
	{
		$classReflection = $this->getClassReflection();
		if ($classReflection === null) {
			return new ArrayType(new MixedType(), new MixedType());
		}

		$broker = Broker::getInstance();

		if (
			!$classReflection->getNativeReflection()->isUserDefined()
			|| UniversalObjectCratesClassReflectionExtension::isUniversalObjectCrate(
				$broker,
				$broker->getUniversalObjectCratesClasses(),
				$classReflection
			)
		) {
			return new ArrayType(new MixedType(), new MixedType());
		}
		$arrayKeys = [];
		$arrayValues = [];

		do {
			foreach ($classReflection->getNativeReflection()->getProperties() as $nativeProperty) {
				if ($nativeProperty->isStatic()) {
					continue;
				}

				$declaringClass = $broker->getClass($nativeProperty->getDeclaringClass()->getName());
				$property = $declaringClass->getNativeProperty($nativeProperty->getName());

				$keyName = $nativeProperty->getName();
				if ($nativeProperty->isPrivate()) {
					$keyName = sprintf(
						"\0%s\0%s",
						$declaringClass->getName(),
						$keyName
					);
				} elseif ($nativeProperty->isProtected()) {
					$keyName = sprintf(
						"\0*\0%s",
						$keyName
					);
				}

				$arrayKeys[] = new ConstantStringType($keyName);
				$arrayValues[] = $property->getReadableType();
			}

			$classReflection = $classReflection->getParentClass();
		} while ($classReflection !== false);

		return new ConstantArrayType($arrayKeys, $arrayValues);
	}

	public function canAccessProperties(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function canCallMethods(): TrinaryLogic
	{
		if (strtolower($this->className) === 'stdclass') {
			return TrinaryLogic::createNo();
		}

		return TrinaryLogic::createYes();
	}

	public function hasMethod(string $methodName): TrinaryLogic
	{
		$classReflection = $this->getClassReflection();
		if ($classReflection === null) {
			return TrinaryLogic::createMaybe();
		}

		if ($classReflection->hasMethod($methodName)) {
			return TrinaryLogic::createYes();
		}

		if ($classReflection->isFinal()) {
			return TrinaryLogic::createNo();
		}

		return TrinaryLogic::createMaybe();
	}

	public function getMethod(string $methodName, ClassMemberAccessAnswerer $scope): MethodReflection
	{
		$classReflection = $this->getClassReflection();
		if ($classReflection === null) {
			throw new \PHPStan\Broker\ClassNotFoundException($this->className);
		}

		return $classReflection->getMethod($methodName, $scope);
	}

	public function canAccessConstants(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function hasConstant(string $constantName): TrinaryLogic
	{
		$class = $this->getClassReflection();
		if ($class === null) {
			return TrinaryLogic::createNo();
		}

		return TrinaryLogic::createFromBoolean(
			$class->hasConstant($constantName)
		);
	}

	public function getConstant(string $constantName): ConstantReflection
	{
		$class = $this->getClassReflection();
		if ($class === null) {
			throw new \PHPStan\Broker\ClassNotFoundException($this->className);
		}

		return $class->getConstant($constantName);
	}

	public function isIterable(): TrinaryLogic
	{
		return $this->isInstanceOf(\Traversable::class);
	}

	public function isIterableAtLeastOnce(): TrinaryLogic
	{
		return $this->isInstanceOf(\Traversable::class)
			->and(TrinaryLogic::createMaybe());
	}

	public function getIterableKeyType(): Type
	{
		$classReflection = $this->getClassReflection();
		if ($classReflection === null) {
			return new ErrorType();
		}

		if ($this->isInstanceOf(\Iterator::class)->yes()) {
			return ParametersAcceptorSelector::selectSingle($classReflection->getNativeMethod('key')->getVariants())->getReturnType();
		}

		if ($this->isInstanceOf(\IteratorAggregate::class)->yes()) {
			return RecursionGuard::run($this, static function () use ($classReflection): Type {
				return ParametersAcceptorSelector::selectSingle(
					$classReflection->getNativeMethod('getIterator')->getVariants()
				)->getReturnType()->getIterableKeyType();
			});
		}

		if ($this->isInstanceOf(\Traversable::class)->yes()) {
			return new MixedType();
		}

		return new ErrorType();
	}

	public function getIterableValueType(): Type
	{
		$classReflection = $this->getClassReflection();
		if ($classReflection === null) {
			return new ErrorType();
		}

		if ($this->isInstanceOf(\Iterator::class)->yes()) {
			return ParametersAcceptorSelector::selectSingle(
				$classReflection->getNativeMethod('current')->getVariants()
			)->getReturnType();
		}

		if ($this->isInstanceOf(\IteratorAggregate::class)->yes()) {
			return RecursionGuard::run($this, static function () use ($classReflection): Type {
				return ParametersAcceptorSelector::selectSingle(
					$classReflection->getNativeMethod('getIterator')->getVariants()
				)->getReturnType()->getIterableValueType();
			});
		}

		if ($this->isInstanceOf(\Traversable::class)->yes()) {
			return new MixedType();
		}

		return new ErrorType();
	}

	public function isArray(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	private function isExtraOffsetAccessibleClass(): TrinaryLogic
	{
		$classReflection = $this->getClassReflection();
		if ($classReflection === null) {
			return TrinaryLogic::createMaybe();
		}

		foreach (self::EXTRA_OFFSET_CLASSES as $extraOffsetClass) {
			if ($classReflection->getName() === $extraOffsetClass) {
				return TrinaryLogic::createYes();
			}
			if ($classReflection->isSubclassOf($extraOffsetClass)) {
				return TrinaryLogic::createYes();
			}
		}

		return TrinaryLogic::createNo();
	}

	public function isOffsetAccessible(): TrinaryLogic
	{
		return $this->isInstanceOf(\ArrayAccess::class)->or(
			$this->isExtraOffsetAccessibleClass()
		);
	}

	public function hasOffsetValueType(Type $offsetType): TrinaryLogic
	{
		$broker = Broker::getInstance();
		if (!$broker->hasClass($this->className)) {
			return TrinaryLogic::createNo();
		}

		return $this->isExtraOffsetAccessibleClass()
			->or($this->isInstanceOf(\ArrayAccess::class))
			->and(TrinaryLogic::createMaybe());
	}

	public function getOffsetValueType(Type $offsetType): Type
	{
		$classReflection = $this->getClassReflection();
		if ($classReflection === null) {
			return new ErrorType();
		}

		if (!$this->isExtraOffsetAccessibleClass()->no()) {
			return new MixedType();
		}

		if ($this->isInstanceOf(\ArrayAccess::class)->yes()) {
			return RecursionGuard::run($this, static function () use ($classReflection): Type {
				return ParametersAcceptorSelector::selectSingle($classReflection->getNativeMethod('offsetGet')->getVariants())->getReturnType();
			});
		}

		return new ErrorType();
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType): Type
	{
		if ($this->isOffsetAccessible()->no()) {
			return new ErrorType();
		}

		if ($this->isInstanceOf(\ArrayAccess::class)->yes()) {
			$classReflection = $this->getClassReflection();
			if ($classReflection === null) {
				return new ErrorType();
			}
			$acceptedValueType = new NeverType();
			$acceptedOffsetType = RecursionGuard::run($this, function () use ($classReflection, &$acceptedValueType): Type {
				$parameters = ParametersAcceptorSelector::selectSingle($classReflection->getNativeMethod('offsetSet')->getVariants())->getParameters();
				if (count($parameters) < 2) {
					throw new \PHPStan\ShouldNotHappenException(sprintf(
						'Method %s::%s() has less than 2 parameters.',
						$this->className,
						'offsetSet'
					));
				}

				$offsetParameter = $parameters[0];
				$acceptedValueType = $parameters[1]->getType();

				return $offsetParameter->getType();
			});

			if ($offsetType === null) {
				$offsetType = new NullType();
			}

			if (
				(!$offsetType instanceof MixedType && !$acceptedOffsetType->isSuperTypeOf($offsetType)->yes())
				|| (!$valueType instanceof MixedType && !$acceptedValueType->isSuperTypeOf($valueType)->yes())
			) {
				return new ErrorType();
			}
		}

		// in the future we may return intersection of $this and OffsetAccessibleType()
		return $this;
	}

	public function isCallable(): TrinaryLogic
	{
		$parametersAcceptors = $this->findCallableParametersAcceptors();
		if ($parametersAcceptors === null) {
			return TrinaryLogic::createNo();
		}

		if (
			count($parametersAcceptors) === 1
			&& $parametersAcceptors[0] instanceof TrivialParametersAcceptor
		) {
			return TrinaryLogic::createMaybe();
		}

		return TrinaryLogic::createYes();
	}

	/**
	 * @param \PHPStan\Reflection\ClassMemberAccessAnswerer $scope
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getCallableParametersAcceptors(ClassMemberAccessAnswerer $scope): array
	{
		if ($this->className === \Closure::class) {
			return [new TrivialParametersAcceptor()];
		}
		$parametersAcceptors = $this->findCallableParametersAcceptors();
		if ($parametersAcceptors === null) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return $parametersAcceptors;
	}

	/**
	 * @return \PHPStan\Reflection\ParametersAcceptor[]|null
	 */
	private function findCallableParametersAcceptors(): ?array
	{
		$classReflection = $this->getClassReflection();
		if ($classReflection === null) {
			return [new TrivialParametersAcceptor()];
		}

		if ($classReflection->hasNativeMethod('__invoke')) {
			return $classReflection->getNativeMethod('__invoke')->getVariants();
		}

		if (!$classReflection->getNativeReflection()->isFinal()) {
			return [new TrivialParametersAcceptor()];
		}

		return null;
	}

	public function isCloneable(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self(
			$properties['className'],
			$properties['subtractedType'] ?? null
		);
	}

	public function isInstanceOf(string $className): TrinaryLogic
	{
		$classReflection = $this->getClassReflection();
		if ($classReflection === null) {
			return TrinaryLogic::createMaybe();
		}

		if ($classReflection->isSubclassOf($className) || $classReflection->getName() === $className) {
			return TrinaryLogic::createYes();
		}

		if ($classReflection->isInterface()) {
			return TrinaryLogic::createMaybe();
		}

		return TrinaryLogic::createNo();
	}

	public function subtract(Type $type): Type
	{
		if ($this->subtractedType !== null) {
			$type = TypeCombinator::union($this->subtractedType, $type);
		}

		return new self($this->className, $type);
	}

	public function getTypeWithoutSubtractedType(): Type
	{
		return new self($this->className);
	}

	public function changeSubtractedType(?Type $subtractedType): Type
	{
		return new self($this->className, $subtractedType);
	}

	public function getSubtractedType(): ?Type
	{
		return $this->subtractedType;
	}

	public function traverse(callable $cb): Type
	{
		$subtractedType = $this->subtractedType !== null ? $cb($this->subtractedType) : null;

		if ($subtractedType !== $this->subtractedType) {
			return new self(
				$this->className,
				$subtractedType
			);
		}

		return $this;
	}

	public function getClassReflection(): ?ClassReflection
	{
		$broker = Broker::getInstance();
		if (!$broker->hasClass($this->className)) {
			return null;
		}

		return $broker->getClass($this->className);
	}

	public function getAncestorWithClassName(string $className): ?TypeWithClassName
	{
		$broker = Broker::getInstance();
		$theirReflection = $broker->getClass($className);
		$thisReflection = $broker->getClass($this->getClassName());

		if ($theirReflection->getName() === $thisReflection->getName()) {
			return $this;
		}

		foreach ($this->getInterfaces() as $interface) {
			$ancestor = $interface->getAncestorWithClassName($className);
			if ($ancestor !== null) {
				return $ancestor;
			}
		}

		$parent = $this->getParent();
		if ($parent !== null) {
			$ancestor = $parent->getAncestorWithClassName($className);
			if ($ancestor !== null) {
				return $ancestor;
			}
		}

		return null;
	}

	private function getParent(): ?ObjectType
	{
		$thisReflection = $this->getClassReflection();
		if ($thisReflection === null) {
			return null;
		}

		$parentReflection = $thisReflection->getParentClass();
		if ($parentReflection === false) {
			return null;
		}

		return self::createFromReflection($parentReflection);
	}

	/** @return ObjectType[] */
	private function getInterfaces(): array
	{
		$thisReflection = $this->getClassReflection();
		if ($thisReflection === null) {
			return [];
		}

		return array_map(static function (ClassReflection $interfaceReflection): self {
			return self::createFromReflection($interfaceReflection);
		}, $thisReflection->getInterfaces());
	}

}
