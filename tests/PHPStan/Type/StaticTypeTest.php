<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class StaticTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataIsIterable(): array
	{
		return [
			[new StaticType('ArrayObject'), TrinaryLogic::createYes()],
			[new StaticType('Traversable'), TrinaryLogic::createYes()],
			[new StaticType('Unknown'), TrinaryLogic::createMaybe()],
			[new StaticType('DateTime'), TrinaryLogic::createNo()],
		];
	}

	/**
	 * @dataProvider dataIsIterable
	 * @param StaticType $type
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsIterable(StaticType $type, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isIterable();
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isIterable()', $type->describe(VerbosityLevel::precise()))
		);
	}

	public function dataIsCallable(): array
	{
		return [
			[new StaticType('Closure'), TrinaryLogic::createYes()],
			[new StaticType('Unknown'), TrinaryLogic::createMaybe()],
			[new StaticType('DateTime'), TrinaryLogic::createMaybe()],
		];
	}

	/**
	 * @dataProvider dataIsCallable
	 * @param StaticType $type
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsCallable(StaticType $type, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isCallable();
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isCallable()', $type->describe(VerbosityLevel::precise()))
		);
	}

	public function dataIsSuperTypeOf(): array
	{
		return [
			[
				new StaticType('UnknownClassA'),
				new ObjectType('UnknownClassB'),
				TrinaryLogic::createMaybe(),
			],
			[
				new StaticType(\ArrayAccess::class),
				new ObjectType(\Traversable::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new StaticType(\Countable::class),
				new ObjectType(\Countable::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new StaticType(\DateTimeImmutable::class),
				new ObjectType(\DateTimeImmutable::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new StaticType(\Traversable::class),
				new ObjectType(\ArrayObject::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new StaticType(\Traversable::class),
				new ObjectType(\Iterator::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new StaticType(\ArrayObject::class),
				new ObjectType(\Traversable::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new StaticType(\Iterator::class),
				new ObjectType(\Traversable::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new StaticType(\ArrayObject::class),
				new ObjectType(\DateTimeImmutable::class),
				TrinaryLogic::createNo(),
			],
			[
				new StaticType(\DateTimeImmutable::class),
				new UnionType(
					[
						new ObjectType(\DateTimeImmutable::class),
						new StringType(),
					]
				),
				TrinaryLogic::createMaybe(),
			],
			[
				new StaticType(\DateTimeImmutable::class),
				new UnionType(
					[
						new ObjectType(\ArrayObject::class),
						new StringType(),
					]
				),
				TrinaryLogic::createNo(),
			],
			[
				new StaticType(\LogicException::class),
				new ObjectType(\InvalidArgumentException::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new StaticType(\InvalidArgumentException::class),
				new ObjectType(\LogicException::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new StaticType(\ArrayAccess::class),
				new StaticType(\Traversable::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new StaticType(\Countable::class),
				new StaticType(\Countable::class),
				TrinaryLogic::createYes(),
			],
			[
				new StaticType(\DateTimeImmutable::class),
				new StaticType(\DateTimeImmutable::class),
				TrinaryLogic::createYes(),
			],
			[
				new StaticType(\Traversable::class),
				new StaticType(\ArrayObject::class),
				TrinaryLogic::createYes(),
			],
			[
				new StaticType(\Traversable::class),
				new StaticType(\Iterator::class),
				TrinaryLogic::createYes(),
			],
			[
				new StaticType(\ArrayObject::class),
				new StaticType(\Traversable::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new StaticType(\Iterator::class),
				new StaticType(\Traversable::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new StaticType(\ArrayObject::class),
				new StaticType(\DateTimeImmutable::class),
				TrinaryLogic::createNo(),
			],
			[
				new StaticType(\DateTimeImmutable::class),
				new UnionType(
					[
						new StaticType(\DateTimeImmutable::class),
						new StaticType(\DateTimeImmutable::class),
					]
				),
				TrinaryLogic::createYes(),
			],
			[
				new StaticType(\DateTimeImmutable::class),
				new UnionType(
					[
						new StaticType(\DateTimeImmutable::class),
						new StringType(),
					]
				),
				TrinaryLogic::createMaybe(),
			],
			[
				new StaticType(\DateTimeImmutable::class),
				new UnionType(
					[
						new StaticType(\ArrayObject::class),
						new StringType(),
					]
				),
				TrinaryLogic::createNo(),
			],
			[
				new StaticType(\LogicException::class),
				new StaticType(\InvalidArgumentException::class),
				TrinaryLogic::createYes(),
			],
			[
				new StaticType(\InvalidArgumentException::class),
				new StaticType(\LogicException::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new StaticType(\stdClass::class),
				new ObjectWithoutClassType(),
				TrinaryLogic::createMaybe(),
			],
			[
				new ObjectWithoutClassType(),
				new StaticType(\stdClass::class),
				TrinaryLogic::createYes(),
			],
			[
				new ThisType(\stdClass::class),
				new ObjectWithoutClassType(),
				TrinaryLogic::createMaybe(),
			],
			[
				new ObjectWithoutClassType(),
				new ThisType(\stdClass::class),
				TrinaryLogic::createYes(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 * @param Type $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSuperTypeOf(Type $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}

}
