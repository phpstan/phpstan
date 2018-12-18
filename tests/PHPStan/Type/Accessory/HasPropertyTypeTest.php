<?php declare(strict_types = 1);

namespace PHPStan\Type\Accessory;

use PHPStan\TrinaryLogic;
use PHPStan\Type\CallableType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

class HasPropertyTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataIsSuperTypeOf(): array
	{
		return [
			[
				new HasPropertyType('format'),
				new HasPropertyType('format'),
				TrinaryLogic::createYes(),
			],
			[
				new HasPropertyType('format'),
				new HasPropertyType('FORMAT'),
				TrinaryLogic::createNo(),
			],
			[
				new HasPropertyType('d'),
				new ObjectType(\DateInterval::class),
				TrinaryLogic::createYes(),
			],
			[
				new HasPropertyType('foo'),
				new ObjectType('UnknownClass'),
				TrinaryLogic::createMaybe(),
			],
			[
				new HasPropertyType('foo'),
				new ObjectType(\Closure::class),
				TrinaryLogic::createNo(),
			],
			[
				new HasPropertyType('foo'),
				new ObjectWithoutClassType(),
				TrinaryLogic::createMaybe(),
			],
			[
				new HasPropertyType('foo'),
				new HasPropertyType('bar'),
				TrinaryLogic::createMaybe(),
			],
			[
				new HasPropertyType('foo'),
				new HasOffsetType(new MixedType()),
				TrinaryLogic::createMaybe(),
			],
			[
				new HasPropertyType('foo'),
				new IterableType(new MixedType(), new MixedType()),
				TrinaryLogic::createMaybe(),
			],
			[
				new HasPropertyType('foo'),
				new CallableType(),
				TrinaryLogic::createMaybe(),
			],
			[
				new HasPropertyType('d'),
				new ObjectWithoutClassType(),
				TrinaryLogic::createMaybe(), // an intentional imprecision
			],
			[
				new HasPropertyType('d'),
				new UnionType([
					new ObjectType(\DateInterval::class),
					new ObjectType('UnknownClass'),
				]),
				TrinaryLogic::createMaybe(),
			],
			[
				new HasPropertyType('d'),
				new IntersectionType([
					new ObjectType('UnknownClass'),
					new HasPropertyType('d'),
				]),
				TrinaryLogic::createYes(),
			],
			[
				new HasPropertyType('d'),
				new IntersectionType([
					new ObjectWithoutClassType(),
					new HasPropertyType('d'),
				]),
				TrinaryLogic::createYes(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 * @param HasPropertyType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSuperTypeOf(HasPropertyType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}

	public function dataIsSubTypeOf(): array
	{
		return [
			[
				new HasPropertyType('foo'),
				new HasPropertyType('foo'),
				TrinaryLogic::createYes(),
			],
			[
				new HasPropertyType('foo'),
				new UnionType([
					new HasPropertyType('foo'),
					new NullType(),
				]),
				TrinaryLogic::createYes(),
			],
			[
				new HasPropertyType('foo'),
				new IntersectionType([
					new HasPropertyType('foo'),
					new HasPropertyType('bar'),
				]),
				TrinaryLogic::createMaybe(),
			],
			[
				new HasPropertyType('d'),
				new ObjectType(\DateInterval::class),
				TrinaryLogic::createMaybe(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSubTypeOf
	 * @param HasPropertyType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSubTypeOf(HasPropertyType $type, Type $otherType, TrinaryLogic $expectedResult): void
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
	 * @param HasPropertyType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSubTypeOfInversed(HasPropertyType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $otherType->isSuperTypeOf($type);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $otherType->describe(VerbosityLevel::precise()), $type->describe(VerbosityLevel::precise()))
		);
	}

}
