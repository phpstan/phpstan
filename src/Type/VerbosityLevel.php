<?php declare(strict_types = 1);

namespace PHPStan\Type;

class VerbosityLevel
{

	private const TYPE_ONLY = 1;
	private const VALUE = 2;
	private const PRECISE = 3;

	/** @var self[] */
	private static $registry;

	/** @var int */
	private $value;

	private function __construct(int $value)
	{
		$this->value = $value;
	}

	private static function create(int $value): self
	{
		self::$registry[$value] = self::$registry[$value] ?? new self($value);
		return self::$registry[$value];
	}

	public static function typeOnly(): self
	{
		return self::create(self::TYPE_ONLY);
	}

	public static function value(): self
	{
		return self::create(self::VALUE);
	}

	public static function precise(): self
	{
		return self::create(self::PRECISE);
	}

	/**
	 * @param callable(): string $typeOnlyCallback
	 * @param callable(): string $valueCallback
	 * @param callable(): string|null $preciseCallback
	 * @return string
	 */
	public function handle(
		callable $typeOnlyCallback,
		callable $valueCallback,
		?callable $preciseCallback = null
	): string
	{
		if ($this->value === self::TYPE_ONLY) {
			return $typeOnlyCallback();
		}

		if ($this->value === self::VALUE || $preciseCallback === null) {
			return $valueCallback();
		}

		return $preciseCallback();
	}

}
