<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PhpParser\Node\Name;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\InaccessibleMethod;
use PHPStan\Reflection\TrivialParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\StringType;
use PHPStan\Type\Traits\ConstantScalarTypeTrait;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class ConstantStringType extends StringType implements ConstantScalarType
{

	private const DESCRIBE_LIMIT = 20;

	use ConstantScalarTypeTrait;
	use ConstantScalarToBooleanTrait;

	/** @var string */
	private $value;

	public function __construct(string $value)
	{
		$this->value = $value;
	}

	public function getValue(): string
	{
		return $this->value;
	}

	public function describe(VerbosityLevel $level): string
	{
		return $level->handle(
			static function (): string {
				return 'string';
			},
			function (): string {
				return var_export(
					\Nette\Utils\Strings::truncate($this->value, self::DESCRIBE_LIMIT),
					true
				);
			},
			function (): string {
				return var_export($this->value, true);
			}
		);
	}

	public function isCallable(): TrinaryLogic
	{
		if ($this->value === '') {
			return TrinaryLogic::createNo();
		}

		$broker = Broker::getInstance();

		// 'my_function'
		if ($broker->hasFunction(new Name($this->value), null)) {
			return TrinaryLogic::createYes();
		}

		// 'MyClass::myStaticFunction'
		$matches = \Nette\Utils\Strings::match($this->value, '#^([a-zA-Z_\\x7f-\\xff\\\\][a-zA-Z0-9_\\x7f-\\xff\\\\]*)::([a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*)\z#');
		if ($matches !== null) {
			if (!$broker->hasClass($matches[1])) {
				return TrinaryLogic::createMaybe();
			}

			$classRef = $broker->getClass($matches[1]);
			if ($classRef->hasMethod($matches[2])) {
				return TrinaryLogic::createYes();
			}

			if (!$classRef->getNativeReflection()->isFinal()) {
				return TrinaryLogic::createMaybe();
			}

			return TrinaryLogic::createNo();
		}

		return TrinaryLogic::createNo();
	}

	/**
	 * @param \PHPStan\Reflection\ClassMemberAccessAnswerer $scope
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getCallableParametersAcceptors(ClassMemberAccessAnswerer $scope): array
	{
		$broker = Broker::getInstance();

		// 'my_function'
		$functionName = new Name($this->value);
		if ($broker->hasFunction($functionName, null)) {
			return $broker->getFunction($functionName, null)->getVariants();
		}

		// 'MyClass::myStaticFunction'
		$matches = \Nette\Utils\Strings::match($this->value, '#^([a-zA-Z_\\x7f-\\xff\\\\][a-zA-Z0-9_\\x7f-\\xff\\\\]*)::([a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*)\z#');
		if ($matches !== null) {
			if (!$broker->hasClass($matches[1])) {
				return [new TrivialParametersAcceptor()];
			}

			$classReflection = $broker->getClass($matches[1]);
			if ($classReflection->hasMethod($matches[2])) {
				$method = $classReflection->getMethod($matches[2], $scope);
				if (!$scope->canCallMethod($method)) {
					return [new InaccessibleMethod($method)];
				}

				return $method->getVariants();
			}

			if (!$classReflection->getNativeReflection()->isFinal()) {
				return [new TrivialParametersAcceptor()];
			}
		}

		throw new \PHPStan\ShouldNotHappenException();
	}

	public function toNumber(): Type
	{
		if (is_numeric($this->value)) {
			/** @var mixed $value */
			$value = $this->value;
			$value = +$value;
			if (is_float($value)) {
				return new ConstantFloatType($value);
			}

			return new ConstantIntegerType($value);
		}

		return new ErrorType();
	}

	public function toInteger(): Type
	{
		$type = $this->toNumber();
		if ($type instanceof ErrorType) {
			return $type;
		}

		return $type->toInteger();
	}

	public function toFloat(): Type
	{
		$type = $this->toNumber();
		if ($type instanceof ErrorType) {
			return $type;
		}

		return $type->toFloat();
	}

	public function hasOffsetValueType(Type $offsetType): TrinaryLogic
	{
		if ($offsetType instanceof ConstantIntegerType) {
			return TrinaryLogic::createFromBoolean(
				$offsetType->getValue() < strlen($this->value)
			);
		}

		return parent::hasOffsetValueType($offsetType);
	}

	public function getOffsetValueType(Type $offsetType): Type
	{
		if ($offsetType instanceof ConstantIntegerType) {
			if ($offsetType->getValue() < strlen($this->value)) {
				return new self($this->value[$offsetType->getValue()]);
			}

			return new ErrorType();
		}

		return parent::getOffsetValueType($offsetType);
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType): Type
	{
		$valueStringType = $valueType->toString();
		if ($valueStringType instanceof ErrorType) {
			return new ErrorType();
		}
		if (
			$offsetType instanceof ConstantIntegerType
			&& $valueStringType instanceof ConstantStringType
		) {
			$value = $this->value;
			$value[$offsetType->getValue()] = $valueStringType->getValue();

			return new self($value);
		}

		return parent::setOffsetValueType($offsetType, $valueType);
	}

	public function append(self $otherString): self
	{
		return new self($this->getValue() . $otherString->getValue());
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self($properties['value']);
	}

}
