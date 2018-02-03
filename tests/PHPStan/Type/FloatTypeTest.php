<?php declare(strict_types = 1);

namespace PHPStan\Type;

class FloatTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataAccepts(): array
	{
		return [
			[
				new FloatType(),
				true,
			],
			[
				new IntegerType(),
				true,
			],
			[
				new MixedType(),
				true,
			],
			[
				new UnionType([
					new IntegerType(),
					new FloatType(),
				]),
				true,
			],
			[
				new NullType(),
				false,
			],
			[
				new StringType(),
				false,
			],
			[
				new UnionType([
					new IntegerType(),
					new FloatType(),
					new StringType(),
				]),
				false,
			],
		];
	}

	/**
	 * @dataProvider dataAccepts
	 * @param Type $otherType
	 * @param bool $expectedResult
	 */
	public function testAccepts(Type $otherType, bool $expectedResult): void
	{
		$this->createBroker();

		$type = new FloatType();
		$actualResult = $type->accepts($otherType);
		$this->assertSame(
			$expectedResult,
			$actualResult,
			sprintf('%s -> accepts(%s)', $type->describe(), $otherType->describe())
		);
	}

}
