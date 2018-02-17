<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PhpParser\Node\Name;
use PHPStan\Broker\Broker;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\StringType;
use PHPStan\Type\Traits\ConstantScalarTypeTrait;
use PHPStan\Type\Type;

class ConstantStringType extends StringType implements ConstantScalarType
{

	use ConstantScalarTypeTrait;

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
		if (preg_match('#^(\w++)::(\w++)\z#', $this->value, $matches)) {
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

	public static function __set_state(array $properties): Type
	{
		return new self($properties['value']);
	}

}
