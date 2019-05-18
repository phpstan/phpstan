<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPUnit\Framework\TestCase;

class PhpDecimalOperatorTypeSpecifyingExtensionTest extends TestCase
{

	/**
	 * @dataProvider matchingSigilAndSidesProvider
	 */
	public function testSupportsMatchingSigilsAndSides(string $sigil, Type $leftType, Type $rightType): void
	{
		$extension = new PhpDecimalOperatorTypeSpecifyingExtension();

		$result = $extension->isOperatorSupported($sigil, $leftType, $rightType);

		self::assertTrue($result);
	}

	public function matchingSigilAndSidesProvider(): iterable
	{
		yield '+' => [
			'+',
			new ObjectType(\Decimal\Decimal::class),
			new ObjectType(\Decimal\Decimal::class),
		];

		yield '-' => [
			'-',
			new ObjectType(\Decimal\Decimal::class),
			new ObjectType(\Decimal\Decimal::class),
		];

		yield '*' => [
			'*',
			new ObjectType(\Decimal\Decimal::class),
			new ObjectType(\Decimal\Decimal::class),
		];

		yield '/' => [
			'/',
			new ObjectType(\Decimal\Decimal::class),
			new ObjectType(\Decimal\Decimal::class),
		];
	}

	/**
	 * @dataProvider notMatchingSidesProvider
	 */
	public function testNotSupportsNotMatchingSides(string $sigil, Type $leftType, Type $rightType): void
	{
		$extension = new PhpDecimalOperatorTypeSpecifyingExtension();

		$result = $extension->isOperatorSupported($sigil, $leftType, $rightType);

		self::assertFalse($result);
	}

	public function notMatchingSidesProvider(): iterable
	{
		yield 'left' => [
			'+',
			new ObjectType(\stdClass::class),
			new ObjectType(\Decimal\Decimal::class),
		];

		yield 'right' => [
			'+',
			new ObjectType(\Decimal\Decimal::class),
			new ObjectType(\stdClass::class),
		];
	}

}
