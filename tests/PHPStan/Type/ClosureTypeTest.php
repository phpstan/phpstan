<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class ClosureTypeTest extends \PHPStan\Testing\TestCase
{

    public function dataIsSuperTypeOf(): array
    {
        return [
            [
                new ClosureType([], new MixedType(), false),
                new ObjectType(\Closure::class),
                TrinaryLogic::createNo(),
            ],
            [
                new ClosureType([], new MixedType(), false),
                new ClosureType([], new MixedType(), false),
                TrinaryLogic::createYes(),
            ],
            [
                new ClosureType([], new UnionType([new IntegerType(), new StringType()]), false),
                new ClosureType([], new IntegerType(), false),
                TrinaryLogic::createYes(),
            ],
            [
                new ClosureType([], new MixedType(), false),
                new CallableType(),
                TrinaryLogic::createMaybe(),
            ],
        ];
    }

    /**
     * @dataProvider dataIsSuperTypeOf
     * @param ClosureType $type
     * @param Type $otherType
     * @param TrinaryLogic $expectedResult
     */
    public function testIsSuperTypeOf(
        ClosureType $type,
        Type $otherType,
        TrinaryLogic $expectedResult
    ): void {
        $actualResult = $type->isSuperTypeOf($otherType);
        $this->assertSame(
            $expectedResult->describe(),
            $actualResult->describe(),
            sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::value()), $otherType->describe(VerbosityLevel::value()))
        );
    }

    public function dataIsSubTypeOf(): array
    {
        return [
            [
                new ClosureType([], new MixedType(), false),
                new ObjectType(\Closure::class),
                TrinaryLogic::createYes(),
            ],
            [
                new ClosureType([], new MixedType(), false),
                new ClosureType([], new MixedType(), false),
                TrinaryLogic::createYes(),
            ],
            [
                new ClosureType([], new UnionType([new IntegerType(), new StringType()]), false),
                new ClosureType([], new IntegerType(), false),
                TrinaryLogic::createMaybe(),
            ],
            [
                new ClosureType([], new IntegerType(), false),
                new ClosureType([], new UnionType([new IntegerType(), new StringType()]), false),
                TrinaryLogic::createYes(),
            ],
            [
                new ClosureType([], new MixedType(), false),
                new CallableType(),
                TrinaryLogic::createYes(),
            ],
        ];
    }

    /**
     * @dataProvider dataIsSubTypeOf
     * @param ClosureType $type
     * @param Type $otherType
     * @param TrinaryLogic $expectedResult
     */
    public function testIsSubTypeOf(
        ClosureType $type,
        Type $otherType,
        TrinaryLogic $expectedResult
    ): void {
        $actualResult = $type->isSubTypeOf($otherType);
        $this->assertSame(
            $expectedResult->describe(),
            $actualResult->describe(),
            sprintf('%s -> isSubTypeOf(%s)', $type->describe(VerbosityLevel::value()), $otherType->describe(VerbosityLevel::value()))
        );
    }
}
