<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Testing\RuleTestCase;

class AccessPropertiesInAssignRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		$broker = $this->createBroker();
		return new AccessPropertiesInAssignRule(
			new AccessPropertiesRule($broker, new RuleLevelHelper($broker, true, false, true), true)
		);
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/access-properties-assign.php'], [
			[
				'Access to an undefined property TestAccessPropertiesAssign\AccessPropertyWithDimFetch::$foo.',
				15,
			],
		]);
	}

}
