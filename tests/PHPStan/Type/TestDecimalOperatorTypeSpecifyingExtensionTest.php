<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Fixture\TestDecimal;
use PHPUnit\Framework\TestCase;

class TestDecimalOperatorTypeSpecifyingExtensionTest extends TestCase
{

	/**
	 * @dataProvider matchingSigilAndSidesProvider
	 */
	public function testSupportsMatchingSigilsAndSides(string $sigil, Type $leftType, Type $rightType): void
	{
		$extension = new TestDecimalOperatorTypeSpecifyingExtension();

		$result = $extension->isOperatorSupported($sigil, $leftType, $rightType);

		self::assertTrue($result);
	}

	public function matchingSigilAndSidesProvider(): iterable
	{
		yield '+' => [
			'+',
			new ObjectType(TestDecimal::class),
			new ObjectType(TestDecimal::class),
		];

		yield '-' => [
			'-',
			new ObjectType(TestDecimal::class),
			new ObjectType(TestDecimal::class),
		];

		yield '*' => [
			'*',
			new ObjectType(TestDecimal::class),
			new ObjectType(TestDecimal::class),
		];

		yield '/' => [
			'/',
			new ObjectType(TestDecimal::class),
			new ObjectType(TestDecimal::class),
		];
	}

	/**
	 * @dataProvider notMatchingSidesProvider
	 */
	public function testNotSupportsNotMatchingSides(string $sigil, Type $leftType, Type $rightType): void
	{
		$extension = new TestDecimalOperatorTypeSpecifyingExtension();

		$result = $extension->isOperatorSupported($sigil, $leftType, $rightType);

		self::assertFalse($result);
	}

	public function notMatchingSidesProvider(): iterable
	{
		yield 'left' => [
			'+',
			new ObjectType(\stdClass::class),
			new ObjectType(TestDecimal::class),
		];

		yield 'right' => [
			'+',
			new ObjectType(TestDecimal::class),
			new ObjectType(\stdClass::class),
		];
	}

}
