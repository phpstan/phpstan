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

	public function dataNot(): array
	{
		return [
			[
				TypeSpecifierContext::not(TypeSpecifierContext::createTrue()),
				[false, true, true, true, false],
			],
			[
				TypeSpecifierContext::not(TypeSpecifierContext::createTruthy()),
				[false, false, true, true, false],
			],
			[
				TypeSpecifierContext::not(TypeSpecifierContext::createFalse()),
				[true, true, false, true, false],
			],
			[
				TypeSpecifierContext::not(TypeSpecifierContext::createFalsey()),
				[true, true, false, false, false],
			],
		];
	}

	/**
	 * @dataProvider dataNot
	 * @param \PHPStan\Analyser\TypeSpecifierContext $context
	 * @param bool[] $results
	 */
	public function testNot(TypeSpecifierContext $context, array $results): void
	{
		foreach (['true', 'truthy', 'false', 'falsey', 'null'] as $index => $method) {
			$this->assertSame($results[$index], $context->$method());
		}
	}

	public function testNotNull(): void
	{
		$this->expectException(\PHPStan\ShouldNotHappenException::class);
		TypeSpecifierContext::not(TypeSpecifierContext::createNull());
	}

}
