<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPUnit\Framework\TestCase;

class RuleErrorBuilderTest extends TestCase
{

	public function testMessageAndBuild(): void
	{
		$builder = RuleErrorBuilder::message('Foo');
		$ruleError = $builder->build();
		$this->assertSame('Foo', $ruleError->getMessage());
	}

	public function testMessageAndLineAndBuild(): void
	{
		$builder = RuleErrorBuilder::message('Foo')->line(25);
		$ruleError = $builder->build();
		$this->assertSame('Foo', $ruleError->getMessage());

		$this->assertInstanceOf(LineRuleError::class, $ruleError);
		$this->assertSame(25, $ruleError->getLine());
	}

}
