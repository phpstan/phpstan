<?php declare(strict_types = 1);

namespace PHPStan\Rules;

class RegistryTest extends \PHPStan\TestCase
{

	public function testGetRules()
	{
		$rule = new DummyRule();

		$registry = new Registry([
			$rule,
		]);

		$rules = $registry->getRules(['PHPParser_Node_Expr_FuncCall']);
		$this->assertSame(1, count($rules));
		$this->assertSame($rule, $rules[0]);

		$this->assertSame(0, count($registry->getRules(['foo'])));
	}

}
