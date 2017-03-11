<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

use PhpParser\Node\Name;
use PHPStan\Broker\Broker;

class ConstantStringType extends StringType implements ConstantScalarType
{
	/** @var Broker */
	private $broker;

	/** @var string */
	private $value;

	public function __construct(TypeXFactory $factory, Broker $broker, string $value)
	{
		parent::__construct($factory);
		$this->broker = $broker;
		$this->value = $value;
	}

	public function getValue(): string
	{
		return $this->value;
	}

	public function generalize(): TypeX
	{
		return $this->factory->createStringType();
	}

	public function describe(): string
	{
		return 'string';
		// return json_encode($this->value);
	}

	public function acceptsX(TypeX $otherType): bool
	{
		return $otherType instanceof self && $otherType->value === $this->value;
	}

	public function isCallable(): int
	{
		// 1) my_function
		if ($this->broker->hasFunction(new Name($this->value))) {
			return self::RESULT_YES;
		}

		// 2) MyClass::myStaticFunction
		if (preg_match('#^(\w++)::(\w++)\z#', $this->value, $matches)) {
			if (!$this->broker->hasClass($matches[1])) {
				return self::RESULT_NO;
			}

			$classRef = $this->broker->getClass($matches[1]);
			if (!$classRef->hasMethod($matches[2])) {
				return self::RESULT_NO;
			}

			$methodRef = $classRef->getMethod($matches[2]);
			if (!$methodRef->isStatic()) {
				return self::RESULT_NO;
			}

			return self::RESULT_YES;
		}

		// 3) MyClass
		if ($this->broker->hasClass($this->value)) {
			$classRef = $this->broker->getClass($this->value);
			if (!$classRef->hasMethod('__invoke')) {
				return self::RESULT_NO;
			}

			return self::RESULT_YES;
		}

		return self::RESULT_NO;
	}
}
