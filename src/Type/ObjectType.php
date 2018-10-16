<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ConstantReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\Php\UniversalObjectCratesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\TrivialParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Traits\TruthyBooleanTypeTrait;

class ObjectType implements TypeWithClassName
{

	use TruthyBooleanTypeTrait;

	private const EXTRA_OFFSET_CLASSES = [
		'SimpleXMLElement' => true,
		'DOMNodeList' => true,
	];

	/** @var string */
	private $className;

	public function __construct(string $className)
	{
		$this->className = $className;
	}

	public function getClassName(): string
	{
		return $this->className;
	}

	public function hasProperty(string $propertyName): bool
	{
		$broker = Broker::getInstance();
		if (!$broker->hasClass($this->className)) {
			return false;
		}

		return $broker->getClass($this->className)->hasProperty($propertyName);
	}

	public function getProperty(string $propertyName, ClassMemberAccessAnswerer $scope): PropertyReflection
	{
		$broker = Broker::getInstance();
		return $broker->getClass($this->className)->getProperty($propertyName, $scope);
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

		if (
			$this->isInstanceOf('SimpleXMLElement')->yes()
			&& $type->isSuperTypeOf($this)->no()
		) {
			return (new UnionType([
				new IntegerType(),
				new FloatType(),
				new StringType(),
				new BooleanType(),
			]))->accepts($type, $strictTypes);
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
			return TrinaryLogic::createMaybe();
		}

		if ($type instanceof ClosureType) {
			return $this->isInstanceOf(\Closure::class);
		}

		if (!$type instanceof TypeWithClassName) {
			return TrinaryLogic::createNo();
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
		return $type instanceof self && $this->className === $type->className;
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
				$broker = Broker::getInstance();
				if (!$broker->hasClass($this->className)) {
					return $this->className;
				}

				return $broker->getClass($this->className)->getDisplayName();
			},
			function (): string {
				return $this->className;
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
		$broker = Broker::getInstance();
		if (!$broker->hasClass($this->className)) {
			return new ErrorType();
		}

		$classReflection = $broker->getClass($this->className);
		if ($classReflection->hasNativeMethod('__toString')) {
			return new StringType();
		}

		return new ErrorType();
	}

	public function toArray(): Type
	{
		$broker = Broker::getInstance();
		if (!$broker->hasClass($this->className)) {
			return new ArrayType(new MixedType(), new MixedType());
		}

		$classReflection = $broker->getClass($this->className);
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
				$arrayValues[] = $property->getType();
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

	public function hasMethod(string $methodName): bool
	{
		$broker = Broker::getInstance();
		if (!$broker->hasClass($this->className)) {
			return false;
		}

		return $broker->getClass($this->className)->hasMethod($methodName);
	}

	public function getMethod(string $methodName, ClassMemberAccessAnswerer $scope): MethodReflection
	{
		$broker = Broker::getInstance();
		return $broker->getClass($this->className)->getMethod($methodName, $scope);
	}

	public function canAccessConstants(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function hasConstant(string $constantName): bool
	{
		$broker = Broker::getInstance();
		if (!$broker->hasClass($this->className)) {
			return false;
		}

		return $broker->getClass($this->className)->hasConstant($constantName);
	}

	public function getConstant(string $constantName): ConstantReflection
	{
		$broker = Broker::getInstance();
		return $broker->getClass($this->className)->getConstant($constantName);
	}

	public function isIterable(): TrinaryLogic
	{
		return $this->isInstanceOf(\Traversable::class);
	}

	public function getIterableKeyType(): Type
	{
		$broker = Broker::getInstance();

		if (!$broker->hasClass($this->className)) {
			return new ErrorType();
		}

		$classReflection = $broker->getClass($this->className);

		if ($this->isInstanceOf(\Iterator::class)->yes()) {
			return ParametersAcceptorSelector::selectSingle($classReflection->getNativeMethod('key')->getVariants())->getReturnType();
		}

		if ($this->isInstanceOf(\IteratorAggregate::class)->yes()) {
			return RecursionGuard::run($this, static function () use ($classReflection) {
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
		$broker = Broker::getInstance();

		if (!$broker->hasClass($this->className)) {
			return new ErrorType();
		}

		$classReflection = $broker->getClass($this->className);

		if ($this->isInstanceOf(\Iterator::class)->yes()) {
			return ParametersAcceptorSelector::selectSingle(
				$classReflection->getNativeMethod('current')->getVariants()
			)->getReturnType();
		}

		if ($this->isInstanceOf(\IteratorAggregate::class)->yes()) {
			return RecursionGuard::run($this, static function () use ($classReflection) {
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

	private function isExtraOffsetAccessibleClass(): TrinaryLogic
	{
		$broker = Broker::getInstance();
		if (!$broker->hasClass($this->className)) {
			return TrinaryLogic::createMaybe();
		}

		$classReflection = $broker->getClass($this->className);

		if (array_key_exists($classReflection->getName(), self::EXTRA_OFFSET_CLASSES)) {
			return TrinaryLogic::createYes();
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
			->or($this->isInstanceOf(\ArrayAccess::class));
	}

	public function getOffsetValueType(Type $offsetType): Type
	{
		$broker = Broker::getInstance();

		if (!$broker->hasClass($this->className)) {
			return new ErrorType();
		}

		if (!$this->isExtraOffsetAccessibleClass()->no()) {
			return new MixedType();
		}

		if ($this->isInstanceOf(\ArrayAccess::class)->yes()) {
			$classReflection = $broker->getClass($this->className);
			return RecursionGuard::run($this, static function () use ($classReflection) {
				return ParametersAcceptorSelector::selectSingle($classReflection->getNativeMethod('offsetGet')->getVariants())->getReturnType();
			});
		}

		return new ErrorType();
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType): Type
	{
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
		$broker = Broker::getInstance();

		if (!$broker->hasClass($this->className)) {
			return [new TrivialParametersAcceptor()];
		}

		$classReflection = $broker->getClass($this->className);
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
		return new self($properties['className']);
	}

	public function isInstanceOf(string $className): TrinaryLogic
	{
		$broker = Broker::getInstance();

		if (!$broker->hasClass($this->className)) {
			return TrinaryLogic::createMaybe();
		}

		$classReflection = $broker->getClass($this->className);
		if ($classReflection->isSubclassOf($className) || $classReflection->getName() === $className) {
			return TrinaryLogic::createYes();
		}

		if ($classReflection->isInterface()) {
			return TrinaryLogic::createMaybe();
		}

		return TrinaryLogic::createNo();
	}

}
