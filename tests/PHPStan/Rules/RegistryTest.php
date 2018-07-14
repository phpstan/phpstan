<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\Analyser\Scope;

class RegistryTest extends \PHPStan\Testing\TestCase
{

	public function testGetRules(): void
	{
		$rule = new DummyRule();

		$registry = new Registry([
			$rule,
		]);

		$rules = $registry->getRules(\PhpParser\Node\Expr\FuncCall::class);
		self::assertCount(1, $rules);
		self::assertSame($rule, $rules[0]);

		self::assertCount(0, $registry->getRules(\PhpParser\Node\Expr\MethodCall::class));
	}

	public function testGetRulesWithTwoDifferentInstances(): void
	{
		$fooRule = new UniversalRule(\PhpParser\Node\Expr\FuncCall::class, function (\PhpParser\Node\Expr\FuncCall $node, Scope $scope): array {
			return ['Foo error'];
		});
		$barRule = new UniversalRule(\PhpParser\Node\Expr\FuncCall::class, function (\PhpParser\Node\Expr\FuncCall $node, Scope $scope): array {
			return ['Bar error'];
		});

		$registry = new Registry([
			$fooRule,
			$barRule,
		]);

		$rules = $registry->getRules(\PhpParser\Node\Expr\FuncCall::class);
		self::assertCount(2, $rules);
		self::assertSame($fooRule, $rules[0]);
		self::assertSame($barRule, $rules[1]);

		self::assertCount(0, $registry->getRules(\PhpParser\Node\Expr\MethodCall::class));
	}

}
