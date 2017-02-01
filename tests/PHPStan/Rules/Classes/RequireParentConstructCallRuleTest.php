<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

class RequireParentConstructCallRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new RequireParentConstructCallRule();
	}

	public function testCallToParentConstructor()
	{
		if (!extension_loaded('soap')) {
			$this->markTestSkipped('Extension SOAP needed');
		}

		$this->analyse([__DIR__ . '/data/call-to-parent-constructor.php'], [
			[
				'IpsumCallToParentConstructor::__construct() calls parent constructor but parent does not have one.',
				31,
			],
			[
				'BCallToParentConstructor::__construct() does not call parent constructor from ACallToParentConstructor.',
				51,
			],
			[
				'CCallToParentConstructor::__construct() calls parent constructor but does not extend any class.',
				61,
			],
			[
				'FCallToParentConstructor::__construct() does not call parent constructor from DCallToParentConstructor.',
				86,
			],
			[
				'BarSoapClient::__construct() does not call parent constructor from SoapClient.',
				129,
			],
			[
				'StaticCallOnAVariable::__construct() does not call parent constructor from FooCallToParentConstructor.',
				140,
			],
		]);
	}

}
