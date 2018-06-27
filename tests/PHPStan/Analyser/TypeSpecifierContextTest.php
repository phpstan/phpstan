<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class TypeSpecifierContextTest extends \PHPStan\Testing\TestCase
{

    public function dataContext(): array
    {
        return [
            [
                TypeSpecifierContext::createTrue(),
                [true, true, false, false, false],
            ],
            [
                TypeSpecifierContext::createTruthy(),
                [true, true, false, false, false],
            ],
            [
                TypeSpecifierContext::createFalse(),
                [false, false, true, true, false],
            ],
            [
                TypeSpecifierContext::createFalsey(),
                [false, false, true, true, false],
            ],
            [
                TypeSpecifierContext::createNull(),
                [false, false, false, false, true],
            ],
        ];
    }

    /**
     * @dataProvider dataContext
     * @param \PHPStan\Analyser\TypeSpecifierContext $context
     * @param bool[] $results
     */
    public function testContext(TypeSpecifierContext $context, array $results): void
    {
        foreach (['true', 'truthy', 'false', 'falsey', 'null'] as $index => $method) {
            $this->assertSame($results[$index], $context->$method());
        }
    }

    public function dataNegate(): array
    {
        return [
            [
                TypeSpecifierContext::createTrue()->negate(),
                [false, true, true, true, false],
            ],
            [
                TypeSpecifierContext::createTruthy()->negate(),
                [false, false, true, true, false],
            ],
            [
                TypeSpecifierContext::createFalse()->negate(),
                [true, true, false, true, false],
            ],
            [
                TypeSpecifierContext::createFalsey()->negate(),
                [true, true, false, false, false],
            ],
        ];
    }

    /**
     * @dataProvider dataNegate
     * @param \PHPStan\Analyser\TypeSpecifierContext $context
     * @param bool[] $results
     */
    public function testNegate(TypeSpecifierContext $context, array $results): void
    {
        foreach (['true', 'truthy', 'false', 'falsey', 'null'] as $index => $method) {
            $this->assertSame($results[$index], $context->$method());
        }
    }

    public function testNegateNull(): void
    {
        $this->expectException(\PHPStan\ShouldNotHappenException::class);
        TypeSpecifierContext::createNull()->negate();
    }
}
