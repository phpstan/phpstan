<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class ContextTest extends \PHPStan\Testing\TestCase
{

	public function dataContext(): array
	{
		return [
			[
				Context::createTrue(),
				[true, true, false, false, false],
			],
			[
				Context::createTruthy(),
				[true, true, false, false, false],
			],
			[
				Context::createFalse(),
				[false, false, true, true, false],
			],
			[
				Context::createFalsey(),
				[false, false, true, true, false],
			],
			[
				Context::createNull(),
				[false, false, false, false, true],
			],
		];
	}

	/**
	 * @dataProvider dataContext
	 * @param \PHPStan\Analyser\Context $context
	 * @param bool[] $results
	 */
	public function testContext(Context $context, array $results): void
	{
		foreach (['true', 'truthy', 'false', 'falsey', 'null'] as $index => $method) {
			$this->assertSame($results[$index], $context->$method());
		}
	}

	public function dataNot(): array
	{
		return [
			[
				Context::not(Context::createTrue()),
				[false, true, true, true, false],
			],
			[
				Context::not(Context::createTruthy()),
				[false, false, true, true, false],
			],
			[
				Context::not(Context::createFalse()),
				[true, true, false, true, false],
			],
			[
				Context::not(Context::createFalsey()),
				[true, true, false, false, false],
			],
		];
	}

	/**
	 * @dataProvider dataNot
	 * @param \PHPStan\Analyser\Context $context
	 * @param bool[] $results
	 */
	public function testNot(Context $context, array $results): void
	{
		foreach (['true', 'truthy', 'false', 'falsey', 'null'] as $index => $method) {
			$this->assertSame($results[$index], $context->$method());
		}
	}

	public function testNotNull(): void
	{
		$this->expectException(\PHPStan\ShouldNotHappenException::class);
		Context::not(Context::createNull());
	}

}
