<?php declare(strict_types = 1);

namespace PHPStan\Rules;

class RegistryTest extends \PHPStan\Testing\TestCase
{

	public function testGetRules()
	{
		$rule = new DummyRule();

		$registry = new Registry([
			$rule,
		]);

		$rules = $registry->getRules(\PhpParser\Node\Expr\FuncCall::class);
		$this->assertSame(1, count($rules));
		$this->assertSame($rule, $rules[0]);

		$this->assertSame(0, count($registry->getRules(\PhpParser\Node\Expr\MethodCall::class)));
	}

}
