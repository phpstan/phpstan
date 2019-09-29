<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PHPStan\Testing\TestCase;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class ConstantStringTypeTest extends TestCase
{

	public function dataIsSuperTypeOf(): array
	{
		return [
			[
				new ConstantStringType(\Exception::class),
				new GenericClassStringType(new ObjectType(\Exception::class)),
				TrinaryLogic::createMaybe(),
			],
			[
				new ConstantStringType(\Exception::class),
				new GenericClassStringType(new ObjectType(\Throwable::class)),
				TrinaryLogic::createMaybe(),
			],
			[
				new ConstantStringType(\Exception::class),
				new GenericClassStringType(new ObjectType(\InvalidArgumentException::class)),
				TrinaryLogic::createNo(),
			],
			[
				new ConstantStringType(\Exception::class),
				new GenericClassStringType(new ObjectType(\stdClass::class)),
				TrinaryLogic::createNo(),
			],
			[
				new ConstantStringType(\Exception::class),
				new ConstantStringType(\Exception::class),
				TrinaryLogic::createYes(),
			],
			[
				new ConstantStringType(\Exception::class),
				new ConstantStringType(\InvalidArgumentException::class),
				TrinaryLogic::createNo(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 */
	public function testIsSuperTypeOf(ConstantStringType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}

}
