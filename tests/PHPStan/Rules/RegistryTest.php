<?php declare(strict_types = 1);

namespace PHPStan\Rules;

class RegistryTest extends \PHPStan\Testing\TestCase
{

	public function testGetRules(): void
	{
		$rule = new DummyRule();

		$registry = new Registry([
			$rule,
		]);

		$rules = $registry->getRules(\PhpParser\Node\Expr\FuncCall::class);
		$this->assertCount(1, $rules);
		$this->assertSame($rule, $rules[0]);

		$this->assertCount(0, $registry->getRules(\PhpParser\Node\Expr\MethodCall::class));
	}

}
