<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class FloatTypeTest extends \PHPStan\Testing\TestCase
{

    public function dataAccepts(): array
    {
        return [
            [
                new FloatType(),
                TrinaryLogic::createYes(),
            ],
            [
                new IntegerType(),
                TrinaryLogic::createYes(),
            ],
            [
                new MixedType(),
                TrinaryLogic::createYes(),
            ],
            [
                new UnionType([
                    new IntegerType(),
                    new FloatType(),
                ]),
                TrinaryLogic::createYes(),
            ],
            [
                new NullType(),
                TrinaryLogic::createNo(),
            ],
            [
                new StringType(),
                TrinaryLogic::createNo(),
            ],
            [
                new UnionType([
                    new IntegerType(),
                    new FloatType(),
                    new StringType(),
                ]),
                TrinaryLogic::createMaybe(),
            ],
            [
                new UnionType([
                    new StringType(),
                    new ResourceType(),
                ]),
                TrinaryLogic::createNo(),
            ],
        ];
    }

    /**
     * @dataProvider dataAccepts
     * @param Type $otherType
     * @param TrinaryLogic $expectedResult
     */
    public function testAccepts(Type $otherType, TrinaryLogic $expectedResult): void
    {
        $type = new FloatType();
        $actualResult = $type->accepts($otherType, true);
        $this->assertSame(
            $expectedResult->describe(),
            $actualResult->describe(),
            sprintf('%s -> accepts(%s)', $type->describe(VerbosityLevel::value()), $otherType->describe(VerbosityLevel::value()))
        );
    }
}
