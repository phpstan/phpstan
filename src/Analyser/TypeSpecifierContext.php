<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class TypeSpecifierContext
{

	public const CONTEXT_TRUE = 0b0001;
	public const CONTEXT_TRUTHY_BUT_NOT_TRUE = 0b0010;
	public const CONTEXT_TRUTHY = self::CONTEXT_TRUE | self::CONTEXT_TRUTHY_BUT_NOT_TRUE;
	public const CONTEXT_FALSE = 0b0100;
	public const CONTEXT_FALSEY_BUT_NOT_FALSE = 0b1000;
	public const CONTEXT_FALSEY = self::CONTEXT_FALSE | self::CONTEXT_FALSEY_BUT_NOT_FALSE;

	/** @var int|null */
	private $value;

	/** @var self[] */
	private static $registry;

	private function __construct(?int $value)
	{
		$this->value = $value;
	}

	private static function create(?int $value): self
	{
		self::$registry[$value] = self::$registry[$value] ?? new self($value);
		return self::$registry[$value];
	}

	public static function createTrue(): self
	{
		return self::create(self::CONTEXT_TRUE);
	}

	public static function createTruthy(): self
	{
		return self::create(self::CONTEXT_TRUTHY);
	}

	public static function createFalse(): self
	{
		return self::create(self::CONTEXT_FALSE);
	}

	public static function createFalsey(): self
	{
		return self::create(self::CONTEXT_FALSEY);
	}

	public static function createNull(): self
	{
		return self::create(null);
	}

	public function negate(): self
	{
		if ($this->value === null) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		return self::create(~$this->value);
	}

	public function true(): bool
	{
		return $this->value !== null && (bool) ($this->value & self::CONTEXT_TRUE);
	}

	public function truthy(): bool
	{
		return $this->value !== null && (bool) ($this->value & self::CONTEXT_TRUTHY);
	}

	public function false(): bool
	{
		return $this->value !== null && (bool) ($this->value & self::CONTEXT_FALSE);
	}

	public function falsey(): bool
	{
		return $this->value !== null && (bool) ($this->value & self::CONTEXT_FALSEY);
	}

	public function null(): bool
	{
		return $this->value === null;
	}

}
