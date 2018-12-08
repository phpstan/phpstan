<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;

class UnionTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataIsCallable(): array
	{
		return [
			[
				new UnionType(
					[
						new ConstantArrayType(
							[new ConstantIntegerType(0), new ConstantIntegerType(1)],
							[new ConstantStringType('Closure'), new ConstantStringType('bind')]
						),
						new ConstantStringType('array_push'),
					]
				),
				TrinaryLogic::createYes(),
			],
			[
				new UnionType(
					[
						new ArrayType(new MixedType(), new MixedType()),
						new StringType(),
					]
				),
				TrinaryLogic::createMaybe(),
			],
			[
				new UnionType(
					[
						new ArrayType(new MixedType(), new MixedType()),
						new ObjectType('Closure'),
					]
				),
				TrinaryLogic::createMaybe(),
			],
			[
				new UnionType(
					[
						new StringType(),
						new IntegerType(),
					]
				),
				TrinaryLogic::createMaybe(),
			],
		];
	}

	/**
	 * @dataProvider dataIsCallable
	 * @param UnionType $type
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsCallable(UnionType $type, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isCallable();
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isCallable()', $type->describe(VerbosityLevel::precise()))
		);
	}

	public function dataIsSuperTypeOf(): \Iterator
	{
		$unionTypeA = new UnionType(
			[
				new IntegerType(),
				new StringType(),
			]
		);

		yield [
			$unionTypeA,
			$unionTypeA->getTypes()[0],
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeA,
			$unionTypeA->getTypes()[1],
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeA,
			$unionTypeA,
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeA,
			new IntersectionType([new StringType(), new CallableType()]),
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeA,
			new MixedType(),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new CallableType(),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new UnionType([new IntegerType(), new FloatType()]),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new UnionType([new CallableType(), new FloatType()]),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new IntersectionType([new MixedType(), new CallableType()]),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new FloatType(),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeA,
			new UnionType([new ConstantBooleanType(true), new FloatType()]),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeA,
			new IterableType(new MixedType(), new MixedType()),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeA,
			new IntersectionType([new ArrayType(new MixedType(), new MixedType()), new CallableType()]),
			TrinaryLogic::createNo(),
		];

		$unionTypeB = new UnionType(
			[
				new IntersectionType(
					[
						new ObjectType('ArrayObject'),
						new IterableType(new MixedType(), new ObjectType('DatePeriod')),
					]
				),
				new ArrayType(new MixedType(), new ObjectType('DatePeriod')),
			]
		);

		yield [
			$unionTypeB,
			$unionTypeB->getTypes()[0],
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeB,
			$unionTypeB->getTypes()[1],
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeB,
			$unionTypeB,
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeB,
			new MixedType(),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeB,
			new ObjectType('ArrayObject'),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeB,
			new IterableType(new MixedType(), new ObjectType('DatePeriod')),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeB,
			new IterableType(new MixedType(), new MixedType()),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeB,
			new StringType(),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeB,
			new IntegerType(),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeB,
			new ObjectType('Foo'),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeB,
			new IterableType(new MixedType(), new ObjectType('DateTime')),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeB,
			new CallableType(),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeB,
			new IntersectionType([new MixedType(), new CallableType()]),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeB,
			new IntersectionType([new StringType(), new CallableType()]),
			TrinaryLogic::createNo(),
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 * @param UnionType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSuperTypeOf(UnionType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}

	public function dataIsSubTypeOf(): \Iterator
	{
		$unionTypeA = new UnionType(
			[
				new IntegerType(),
				new StringType(),
			]
		);

		yield [
			$unionTypeA,
			$unionTypeA,
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeA,
			new UnionType(array_merge($unionTypeA->getTypes(), [new ResourceType()])),
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeA,
			new MixedType(),
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeA,
			$unionTypeA->getTypes()[0],
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			$unionTypeA->getTypes()[1],
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new CallableType(),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new UnionType([new IntegerType(), new FloatType()]),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new UnionType([new CallableType(), new FloatType()]),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new IntersectionType([new StringType(), new CallableType()]),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new IntersectionType([new MixedType(), new CallableType()]),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeA,
			new FloatType(),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeA,
			new UnionType([new ConstantBooleanType(true), new FloatType()]),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeA,
			new IterableType(new MixedType(), new MixedType()),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeA,
			new IntersectionType([new ArrayType(new MixedType(), new MixedType()), new CallableType()]),
			TrinaryLogic::createNo(),
		];

		$unionTypeB = new UnionType(
			[
				new IntersectionType(
					[
						new ObjectType('ArrayObject'),
						new IterableType(new MixedType(), new ObjectType('Item')),
						new CallableType(),
					]
				),
				new ArrayType(new MixedType(), new ObjectType('Item')),
			]
		);

		yield [
			$unionTypeB,
			$unionTypeB,
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeB,
			new UnionType(array_merge($unionTypeB->getTypes(), [new StringType()])),
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeB,
			new MixedType(),
			TrinaryLogic::createYes(),
		];

		yield [
			$unionTypeB,
			$unionTypeB->getTypes()[0],
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeB,
			$unionTypeB->getTypes()[1],
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeB,
			new ObjectType('ArrayObject'),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeB,
			new CallableType(),
			TrinaryLogic::createMaybe(),
		];

		yield [
			$unionTypeB,
			new FloatType(),
			TrinaryLogic::createNo(),
		];

		yield [
			$unionTypeB,
			new ObjectType('Foo'),
			TrinaryLogic::createNo(),
		];
	}

	/**
	 * @dataProvider dataIsSubTypeOf
	 * @param UnionType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSubTypeOf(UnionType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isSubTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSubTypeOf(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}

	/**
	 * @dataProvider dataIsSubTypeOf
	 * @param UnionType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSubTypeOfInversed(UnionType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $otherType->isSuperTypeOf($type);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $otherType->describe(VerbosityLevel::precise()), $type->describe(VerbosityLevel::precise()))
		);
	}

	public function dataDescribe(): array
	{
		return [
			[
				new UnionType([new IntegerType(), new StringType()]),
				'int|string',
				'int|string',
			],
			[
				new UnionType([new IntegerType(), new StringType(), new NullType()]),
				'int|string|null',
				'int|string|null',
			],
			[
				new UnionType(
					[
						new ConstantStringType('1aaa'),
						new ConstantStringType('11aaa'),
						new ConstantStringType('2aaa'),
						new ConstantStringType('10aaa'),
						new ConstantIntegerType(2),
						new ConstantIntegerType(1),
						new ConstantIntegerType(10),
						new ConstantFloatType(2.2),
						new NullType(),
						new ConstantStringType('10'),
						new ObjectType(\stdClass::class),
						new ConstantBooleanType(true),
						new ConstantStringType('foo'),
						new ConstantStringType('2'),
						new ConstantStringType('1'),
					]
				),
				"1|2|2.2|10|'1'|'10'|'10aaa'|'11aaa'|'1aaa'|'2'|'2aaa'|'foo'|stdClass|true|null",
				'float|int|stdClass|string|true|null',
			],
			[
				TypeCombinator::union(
					new ConstantArrayType(
						[
							new ConstantStringType('a'),
							new ConstantStringType('b'),
						],
						[
							new StringType(),
							new BooleanType(),
						]
					),
					new ConstantArrayType(
						[
							new ConstantStringType('a'),
							new ConstantStringType('b'),
						],
						[
							new IntegerType(),
							new FloatType(),
						]
					),
					new ConstantStringType('aaa')
				),
				'\'aaa\'|array(\'a\' => int|string, \'b\' => bool|float)',
				'array<string, bool|float|int|string>|string',
			],
			[
				TypeCombinator::union(
					new ConstantArrayType(
						[
							new ConstantStringType('a'),
							new ConstantStringType('b'),
						],
						[
							new StringType(),
							new BooleanType(),
						]
					),
					new ConstantArrayType(
						[
							new ConstantStringType('b'),
							new ConstantStringType('c'),
						],
						[
							new IntegerType(),
							new FloatType(),
						]
					),
					new ConstantStringType('aaa')
				),
				'\'aaa\'|array(?\'a\' => string, \'b\' => bool|int, ?\'c\' => float)',
				'array<string, bool|float|int|string>|string',
			],
			[
				TypeCombinator::union(
					new ConstantArrayType(
						[
							new ConstantStringType('a'),
							new ConstantStringType('b'),
						],
						[
							new StringType(),
							new BooleanType(),
						]
					),
					new ConstantArrayType(
						[
							new ConstantStringType('c'),
							new ConstantStringType('d'),
						],
						[
							new IntegerType(),
							new FloatType(),
						]
					),
					new ConstantStringType('aaa')
				),
				'\'aaa\'|array(\'a\' => string, \'b\' => bool)|array(\'c\' => int, \'d\' => float)',
				'array<string, bool|float|int|string>|string',
			],
			[
				TypeCombinator::union(
					new ConstantArrayType(
						[
							new ConstantIntegerType(0),
						],
						[
							new StringType(),
						]
					),
					new ConstantArrayType(
						[
							new ConstantIntegerType(0),
							new ConstantIntegerType(1),
							new ConstantIntegerType(2),
						],
						[
							new IntegerType(),
							new BooleanType(),
							new FloatType(),
						]
					)
				),
				'array(0 => int|string, ?1 => bool, ?2 => float)',
				'array<int, bool|float|int|string>',
			],
			[
				TypeCombinator::union(
					new ConstantArrayType([], []),
					new ConstantArrayType(
						[
							new ConstantStringType('foo'),
						],
						[
							new ConstantStringType('bar'),
						]
					)
				),
				'array()|array(\'foo\' => \'bar\')',
				'array<string, string>',
			],
		];
	}

	/**
	 * @dataProvider dataDescribe
	 * @param UnionType $type
	 * @param string $expectedValueDescription
	 * @param string $expectedTypeOnlyDescription
	 */
	public function testDescribe(
		UnionType $type,
		string $expectedValueDescription,
		string $expectedTypeOnlyDescription
	): void
	{
		$this->assertSame($expectedValueDescription, $type->describe(VerbosityLevel::precise()));
		$this->assertSame($expectedTypeOnlyDescription, $type->describe(VerbosityLevel::typeOnly()));
	}

	public function dataAccepts(): array
	{
		return [
			[
				new UnionType([new CallableType(), new NullType()]),
				new ClosureType([], new StringType(), false),
				TrinaryLogic::createYes(),
			],
			[
				new UnionType([new CallableType(), new NullType()]),
				new UnionType([new ClosureType([], new StringType(), false), new BooleanType()]),
				TrinaryLogic::createMaybe(),
			],
			[
				new UnionType([new CallableType(), new NullType()]),
				new BooleanType(),
				TrinaryLogic::createNo(),
			],
		];
	}

	/**
	 * @dataProvider dataAccepts
	 * @param UnionType $type
	 * @param Type $acceptedType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testAccepts(
		UnionType $type,
		Type $acceptedType,
		TrinaryLogic $expectedResult
	): void
	{
		$this->assertSame(
			$expectedResult->describe(),
			$type->accepts($acceptedType, true)->describe(),
			sprintf('%s -> accepts(%s)', $type->describe(VerbosityLevel::precise()), $acceptedType->describe(VerbosityLevel::precise()))
		);
	}

	public function dataHasMethod(): array
	{
		return [
			[
				new UnionType([new ObjectType(\DateTimeImmutable::class), new IntegerType()]),
				'format',
				TrinaryLogic::createMaybe(),
			],
			[
				new UnionType([new ObjectType(\DateTimeImmutable::class), new ObjectType(\DateTime::class)]),
				'format',
				TrinaryLogic::createYes(),
			],
			[
				new UnionType([new FloatType(), new IntegerType()]),
				'format',
				TrinaryLogic::createNo(),
			],
			[
				new UnionType([new ObjectType(\DateTimeImmutable::class), new NullType()]),
				'format',
				TrinaryLogic::createMaybe(),
			],
		];
	}

	/**
	 * @dataProvider dataHasMethod
	 * @param UnionType $type
	 * @param string $methodName
	 * @param TrinaryLogic $expectedResult
	 */
	public function testHasMethod(
		UnionType $type,
		string $methodName,
		TrinaryLogic $expectedResult
	): void
	{
		$this->assertSame($expectedResult->describe(), $type->hasMethod($methodName)->describe());
	}

}
