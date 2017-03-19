<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

class ErrorType extends BaseTypeX
{
	const UNDEFINED_OFFSET = 'undefined offset';

	/** @var string */
	private $message;

	public function __construct(TypeXFactory $factory, string $message = 'error')
	{
		parent::__construct($factory);
		$this->message = $message;
	}

	public function describe(): string
	{
		return sprintf('*%s*', strtoupper($this->message));
	}

	public function acceptsX(TypeX $otherType): bool
	{
		return FALSE;
	}

	public function isAssignable(): int
	{
		return self::RESULT_NO;
	}

	public function isCallable(): int
	{
		return self::RESULT_NO;
	}

	public function getCallReturnType(TypeX ...$callArgsTypes): TypeX
	{
		return $this;
	}

	public function isIterable(): int
	{
		return self::RESULT_NO;
	}

	public function getIterableKeyType(): TypeX
	{
		return $this;
	}

	public function getIterableValueType(): TypeX
	{
		return $this;
	}

	public function canCallMethodsX(): int
	{
		return self::RESULT_NO;
	}

	public function canAccessPropertiesX(): int
	{
		return self::RESULT_NO;
	}

	public function canAccessOffset(): int
	{
		return self::RESULT_NO;
	}
}
