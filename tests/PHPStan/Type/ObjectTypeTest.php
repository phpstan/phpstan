<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class ObjectTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataIsIterable(): array
	{
		return [
			[new ObjectType('ArrayObject'), TrinaryLogic::createYes()],
			[new ObjectType('Traversable'), TrinaryLogic::createYes()],
			[new ObjectType('Unknown'), TrinaryLogic::createMaybe()],
			[new ObjectType('DateTime'), TrinaryLogic::createNo()],
		];
	}

	/**
	 * @dataProvider dataIsIterable
	 * @param ObjectType $type
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsIterable(ObjectType $type, TrinaryLogic $expectedResult): void
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
			[new ObjectType('Closure'), TrinaryLogic::createYes()],
			[new ObjectType('Unknown'), TrinaryLogic::createMaybe()],
			[new ObjectType('DateTime'), TrinaryLogic::createMaybe()],
		];
	}

	/**
	 * @dataProvider dataIsCallable
	 * @param ObjectType $type
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsCallable(ObjectType $type, TrinaryLogic $expectedResult): void
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
				new ObjectType('UnknownClassA'),
				new ObjectType('UnknownClassB'),
				TrinaryLogic::createMaybe(),
			],
			[
				new ObjectType(\ArrayAccess::class),
				new ObjectType(\Traversable::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new ObjectType(\Countable::class),
				new ObjectType(\Countable::class),
				TrinaryLogic::createYes(),
			],
			[
				new ObjectType(\DateTimeImmutable::class),
				new ObjectType(\DateTimeImmutable::class),
				TrinaryLogic::createYes(),
			],
			[
				new ObjectType(\Traversable::class),
				new ObjectType(\ArrayObject::class),
				TrinaryLogic::createYes(),
			],
			[
				new ObjectType(\Traversable::class),
				new ObjectType(\Iterator::class),
				TrinaryLogic::createYes(),
			],
			[
				new ObjectType(\ArrayObject::class),
				new ObjectType(\Traversable::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new ObjectType(\Iterator::class),
				new ObjectType(\Traversable::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new ObjectType(\ArrayObject::class),
				new ObjectType(\DateTimeImmutable::class),
				TrinaryLogic::createNo(),
			],
			[
				new ObjectType(\DateTimeImmutable::class),
				new UnionType([
					new ObjectType(\DateTimeImmutable::class),
					new StringType(),
				]),
				TrinaryLogic::createMaybe(),
			],
			[
				new ObjectType(\DateTimeImmutable::class),
				new UnionType([
					new ObjectType(\ArrayObject::class),
					new StringType(),
				]),
				TrinaryLogic::createNo(),
			],
			[
				new ObjectType(\LogicException::class),
				new ObjectType(\InvalidArgumentException::class),
				TrinaryLogic::createYes(),
			],
			[
				new ObjectType(\InvalidArgumentException::class),
				new ObjectType(\LogicException::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new ObjectType(\ArrayAccess::class),
				new StaticType(\Traversable::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new ObjectType(\Countable::class),
				new StaticType(\Countable::class),
				TrinaryLogic::createYes(),
			],
			[
				new ObjectType(\DateTimeImmutable::class),
				new StaticType(\DateTimeImmutable::class),
				TrinaryLogic::createYes(),
			],
			[
				new ObjectType(\Traversable::class),
				new StaticType(\ArrayObject::class),
				TrinaryLogic::createYes(),
			],
			[
				new ObjectType(\Traversable::class),
				new StaticType(\Iterator::class),
				TrinaryLogic::createYes(),
			],
			[
				new ObjectType(\ArrayObject::class),
				new StaticType(\Traversable::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new ObjectType(\Iterator::class),
				new StaticType(\Traversable::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new ObjectType(\ArrayObject::class),
				new StaticType(\DateTimeImmutable::class),
				TrinaryLogic::createNo(),
			],
			[
				new ObjectType(\DateTimeImmutable::class),
				new UnionType([
					new StaticType(\DateTimeImmutable::class),
					new StringType(),
				]),
				TrinaryLogic::createMaybe(),
			],
			[
				new ObjectType(\DateTimeImmutable::class),
				new UnionType([
					new StaticType(\ArrayObject::class),
					new StringType(),
				]),
				TrinaryLogic::createNo(),
			],
			[
				new ObjectType(\LogicException::class),
				new StaticType(\InvalidArgumentException::class),
				TrinaryLogic::createYes(),
			],
			[
				new ObjectType(\InvalidArgumentException::class),
				new StaticType(\LogicException::class),
				TrinaryLogic::createMaybe(),
			],
			[
				new ObjectType(\stdClass::class),
				new ClosureType([], new MixedType(), false),
				TrinaryLogic::createNo(),
			],
			[
				new ObjectType(\Closure::class),
				new ClosureType([], new MixedType(), false),
				TrinaryLogic::createYes(),
			],
			[
				new ObjectType(\Countable::class),
				new IterableType(new MixedType(), new MixedType()),
				TrinaryLogic::createMaybe(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 * @param ObjectType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSuperTypeOf(ObjectType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}

}
