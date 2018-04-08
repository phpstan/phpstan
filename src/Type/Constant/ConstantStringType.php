<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PhpParser\Node\Name;
use PHPStan\Broker\Broker;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\StringType;
use PHPStan\Type\Traits\ConstantScalarTypeTrait;
use PHPStan\Type\Type;

class ConstantStringType extends StringType implements ConstantScalarType
{

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

	public function isCallable(): TrinaryLogic
	{
		$broker = Broker::getInstance();

		// 'my_function'
		if ($broker->hasFunction(new Name($this->value), null)) {
			return TrinaryLogic::createYes();
		}

		// 'MyClass::myStaticFunction'
		$matches = \Nette\Utils\Strings::match($this->value, '#^([a-zA-Z_\\x7f-\\xff\\\\][a-zA-Z0-9_\\x7f-\\xff\\\\]*)::([a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*)\z#');
		if ($matches !== null) {
			if (!$broker->hasClass($matches[1])) {
				return TrinaryLogic::createNo();
			}

			$classRef = $broker->getClass($matches[1]);
			if (!$classRef->hasMethod($matches[2])) {
				if (!$classRef->hasNativeMethod('__callStatic')) {
					return TrinaryLogic::createNo();
				}
				return TrinaryLogic::createYes();
			}

			return TrinaryLogic::createYes();
		}

		return TrinaryLogic::createNo();
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

	public function toString(): Type
	{
		return $this;
	}

	public function getOffsetValueType(Type $offsetType): Type
	{
		$offsetNumberType = $offsetType->toNumber();
		if ($offsetNumberType instanceof ConstantScalarType) {
			$offsetValue = $offsetNumberType->getValue();
			if ($offsetValue < strlen($this->value)) {
				return new self($this->value[$offsetValue]);
			}
		} elseif (!$offsetNumberType instanceof ErrorType) {
			return new StringType();
		}

		return new ErrorType();
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType): Type
	{
		$valueStringType = $valueType->toString();
		if ($valueStringType instanceof ErrorType) {
			return new ErrorType();
		}
		if (
			$offsetType instanceof ConstantScalarType
			&& $valueStringType instanceof ConstantStringType
		) {
			$value = $this->value;
			$value[$offsetType->getValue()] = $valueStringType->getValue();

			return new self($value);
		}

		return new StringType();
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
