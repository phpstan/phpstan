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

}
