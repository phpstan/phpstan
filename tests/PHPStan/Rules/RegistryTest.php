<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\Analyser\Scope;

class RegistryTest extends \PHPStan\Testing\TestCase
{

	public function testGetRules(): void
	{
		$rule = new DummyRule();

		$registry = new Registry(
			[
				$rule,
			]
		);

		$rules = $registry->getRules(\PhpParser\Node\Expr\FuncCall::class);
		$this->assertCount(1, $rules);
		$this->assertSame($rule, $rules[0]);

		$this->assertCount(0, $registry->getRules(\PhpParser\Node\Expr\MethodCall::class));
	}

	public function testGetRulesWithTwoDifferentInstances(): void
	{
		$fooRule = new UniversalRule(
			\PhpParser\Node\Expr\FuncCall::class,
			static function (\PhpParser\Node\Expr\FuncCall $node, Scope $scope): array {
				return ['Foo error'];
			}
		);
		$barRule = new UniversalRule(
			\PhpParser\Node\Expr\FuncCall::class,
			static function (\PhpParser\Node\Expr\FuncCall $node, Scope $scope): array {
				return ['Bar error'];
			}
		);

		$registry = new Registry(
			[
				$fooRule,
				$barRule,
			]
		);

		$rules = $registry->getRules(\PhpParser\Node\Expr\FuncCall::class);
		$this->assertCount(2, $rules);
		$this->assertSame($fooRule, $rules[0]);
		$this->assertSame($barRule, $rules[1]);

		$this->assertCount(0, $registry->getRules(\PhpParser\Node\Expr\MethodCall::class));
	}

}
