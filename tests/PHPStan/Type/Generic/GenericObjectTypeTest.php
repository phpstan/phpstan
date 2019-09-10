<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\TrinaryLogic;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Test\A;
use PHPStan\Type\Test\B;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class GenericObjectTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataIsSuperTypeOf(): array
	{
		return [
			'equal type' => [
				new GenericObjectType(A\A::class, [new ObjectType('DateTime')]),
				new GenericObjectType(A\A::class, [new ObjectType('DateTime')]),
				TrinaryLogic::createYes(),
			],
			'sub-class with static @extends with same type args' => [
				new GenericObjectType(A\A::class, [new ObjectType('DateTime')]),
				new ObjectType(A\AOfDateTime::class),
				TrinaryLogic::createYes(),
			],
			'sub-class with @extends with same type args' => [
				new GenericObjectType(A\A::class, [new ObjectType('DateTime')]),
				new GenericObjectType(A\SubA::class, [new ObjectType('DateTime')]),
				TrinaryLogic::createYes(),
			],
			'same class, different type args' => [
				new GenericObjectType(A\A::class, [new ObjectType('DateTimeInterface')]),
				new GenericObjectType(A\A::class, [new ObjectType('DateTime')]),
				TrinaryLogic::createNo(),
			],
			'same class, one naked' => [
				new GenericObjectType(A\A::class, [new ObjectType('DateTimeInterface')]),
				new ObjectType(A\A::class),
				TrinaryLogic::createMaybe(),
			],
			'implementation with @extends with same type args' => [
				new GenericObjectType(B\I::class, [new ObjectType('DateTime')]),
				new GenericObjectType(B\IImpl::class, [new ObjectType('DateTime')]),
				TrinaryLogic::createYes(),
			],
			'implementation with @extends with different type args' => [
				new GenericObjectType(B\I::class, [new ObjectType('DateTimeInteface')]),
				new GenericObjectType(B\IImpl::class, [new ObjectType('DateTime')]),
				TrinaryLogic::createNo(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
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

	public function dataAccepts(): array
	{
		return [
			'equal type' => [
				new GenericObjectType(A\A::class, [new ObjectType('DateTime')]),
				new GenericObjectType(A\A::class, [new ObjectType('DateTime')]),
				TrinaryLogic::createYes(),
			],
			'sub-class with static @extends with same type args' => [
				new GenericObjectType(A\A::class, [new ObjectType('DateTime')]),
				new ObjectType(A\AOfDateTime::class),
				TrinaryLogic::createYes(),
			],
			'sub-class with @extends with same type args' => [
				new GenericObjectType(A\A::class, [new ObjectType('DateTime')]),
				new GenericObjectType(A\SubA::class, [new ObjectType('DateTime')]),
				TrinaryLogic::createYes(),
			],
			'same class, different type args' => [
				new GenericObjectType(A\A::class, [new ObjectType('DateTimeInterface')]),
				new GenericObjectType(A\A::class, [new ObjectType('DateTime')]),
				TrinaryLogic::createNo(),
			],
			'same class, one naked' => [
				new GenericObjectType(A\A::class, [new ObjectType('DateTimeInterface')]),
				new ObjectType(A\A::class),
				TrinaryLogic::createMaybe(),
			],
			'implementation with @extends with same type args' => [
				new GenericObjectType(B\I::class, [new ObjectType('DateTime')]),
				new GenericObjectType(B\IImpl::class, [new ObjectType('DateTime')]),
				TrinaryLogic::createYes(),
			],
			'implementation with @extends with different type args' => [
				new GenericObjectType(B\I::class, [new ObjectType('DateTimeInteface')]),
				new GenericObjectType(B\IImpl::class, [new ObjectType('DateTime')]),
				TrinaryLogic::createNo(),
			],
		];
	}

	/**
	 * @dataProvider dataAccepts
	 */
	public function testAccepts(
		Type $acceptingType,
		Type $acceptedType,
		TrinaryLogic $expectedResult
	): void
	{
		$actualResult = $acceptingType->accepts($acceptedType, true);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> accepts(%s)', $acceptingType->describe(VerbosityLevel::precise()), $acceptedType->describe(VerbosityLevel::precise()))
		);
	}

}
