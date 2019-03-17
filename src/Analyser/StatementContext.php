<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class StatementContext
{

	private const NULL_VALUE = 0;
	private const CHECK_LAST_STATEMENT = 1;

	/** @var int */
	private $value;

	/** @var self[] */
	private static $registry = [];

	private function __construct(int $value)
	{
		$this->value = $value;
	}

	public static function createNull(): self
	{
		return self::create(self::NULL_VALUE);
	}

	public static function createCheckLastStatement(): self
	{
		return self::create(self::CHECK_LAST_STATEMENT);
	}

	public function shouldCheckLastStatement(): bool
	{
		return $this->value === self::CHECK_LAST_STATEMENT;
	}

	private static function create(int $value): self
	{
		self::$registry[$value] = self::$registry[$value] ?? new self($value);
		return self::$registry[$value];
	}

}
