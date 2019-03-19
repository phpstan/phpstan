<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

class ImpossibleCheckTypeFunctionCallRuleTest extends \PHPStan\Testing\RuleTestCase
{

	/** @var bool */
	private $checkAlwaysTrueCheckTypeFunctionCall;

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new ImpossibleCheckTypeFunctionCallRule(
			new ImpossibleCheckTypeHelper(
				$this->createBroker(),
				$this->getTypeSpecifier()
			),
			$this->checkAlwaysTrueCheckTypeFunctionCall
		);
	}

	public function testImpossibleCheckTypeFunctionCall(): void
	{
		$this->checkAlwaysTrueCheckTypeFunctionCall = true;
		$this->analyse(
			[__DIR__ . '/data/check-type-function-call.php'],
			[
				[
					'Call to function is_int() with int will always evaluate to true.',
					25,
				],
				[
					'Call to function is_int() with string will always evaluate to false.',
					31,
				],
				[
					'Call to function is_callable() with array<int> will always evaluate to false.',
					44,
				],
				[
					'Call to function assert() with false will always evaluate to false.',
					48,
				],
				[
					'Call to function is_callable() with \'date\' will always evaluate to true.',
					84,
				],
				[
					'Call to function is_callable() with \'nonexistentFunction\' will always evaluate to false.',
					87,
				],
				[
					'Call to function is_numeric() with \'123\' will always evaluate to true.',
					102,
				],
				[
					'Call to function is_numeric() with \'blabla\' will always evaluate to false.',
					105,
				],
				[
					'Call to function is_numeric() with 123|float will always evaluate to true.',
					118,
				],
				[
					'Call to function is_string() with string will always evaluate to true.',
					140,
				],
				[
					'Call to function method_exists() with CheckTypeFunctionCall\Foo and \'doFoo\' will always evaluate to true.',
					179,
				],
				[
					'Call to function method_exists() with $this(CheckTypeFunctionCall\FinalClassWithMethodExists) and \'doFoo\' will always evaluate to true.',
					191,
				],
				[
					'Call to function method_exists() with $this(CheckTypeFunctionCall\FinalClassWithMethodExists) and \'doBar\' will always evaluate to false.',
					194,
				],
				[
					'Call to function property_exists() with $this(CheckTypeFunctionCall\FinalClassWithPropertyExists) and \'fooProperty\' will always evaluate to true.',
					209,
				],
				[
					'Call to function property_exists() with $this(CheckTypeFunctionCall\FinalClassWithPropertyExists) and \'barProperty\' will always evaluate to false.',
					212,
				],
				[
					'Call to function in_array() with arguments int, array(\'foo\', \'bar\') and true will always evaluate to false.',
					235,
				],
				[
					'Call to function in_array() with arguments \'bar\'|\'foo\', array(\'baz\', \'lorem\') and true will always evaluate to false.',
					244,
				],
				[
					'Call to function in_array() with arguments \'bar\'|\'foo\', array(\'foo\', \'bar\') and true will always evaluate to true.',
					248,
				],
				[
					'Call to function in_array() with arguments \'foo\', array(\'foo\') and true will always evaluate to true.',
					252,
				],
				[
					'Call to function in_array() with arguments \'foo\', array(\'foo\', \'bar\') and true will always evaluate to true.',
					256,
				],
				[
					'Call to function in_array() with arguments \'bar\', array()|array(\'foo\') and true will always evaluate to false.',
					320,
				],
				[
					'Call to function in_array() with arguments \'baz\', array(0 => \'bar\', ?1 => \'foo\') and true will always evaluate to false.',
					336,
				],
				[
					'Call to function in_array() with arguments \'foo\', array() and true will always evaluate to false.',
					343,
				],
				[
					'Call to function array_key_exists() with \'a\' and array(\'a\' => 1, ?\'b\' => 2) will always evaluate to true.',
					360,
				],
				[
					'Call to function array_key_exists() with \'c\' and array(\'a\' => 1, ?\'b\' => 2) will always evaluate to false.',
					366,
				],
			]
		);
	}

	public function testImpossibleCheckTypeFunctionCallWithoutAlwaysTrue(): void
	{
		$this->checkAlwaysTrueCheckTypeFunctionCall = false;
		$this->analyse(
			[__DIR__ . '/data/check-type-function-call.php'],
			[
				[
					'Call to function is_int() with string will always evaluate to false.',
					31,
				],
				[
					'Call to function is_callable() with array<int> will always evaluate to false.',
					44,
				],
				[
					'Call to function assert() with false will always evaluate to false.',
					48,
				],
				[
					'Call to function is_callable() with \'nonexistentFunction\' will always evaluate to false.',
					87,
				],
				[
					'Call to function is_numeric() with \'blabla\' will always evaluate to false.',
					105,
				],
				[
					'Call to function method_exists() with $this(CheckTypeFunctionCall\FinalClassWithMethodExists) and \'doBar\' will always evaluate to false.',
					194,
				],
				[
					'Call to function property_exists() with $this(CheckTypeFunctionCall\FinalClassWithPropertyExists) and \'barProperty\' will always evaluate to false.',
					212,
				],
				[
					'Call to function in_array() with arguments int, array(\'foo\', \'bar\') and true will always evaluate to false.',
					235,
				],
				[
					'Call to function in_array() with arguments \'bar\'|\'foo\', array(\'baz\', \'lorem\') and true will always evaluate to false.',
					244,
				],
				[
					'Call to function in_array() with arguments \'bar\', array()|array(\'foo\') and true will always evaluate to false.',
					320,
				],
				[
					'Call to function in_array() with arguments \'baz\', array(0 => \'bar\', ?1 => \'foo\') and true will always evaluate to false.',
					336,
				],
				[
					'Call to function in_array() with arguments \'foo\', array() and true will always evaluate to false.',
					343,
				],
				[
					'Call to function array_key_exists() with \'c\' and array(\'a\' => 1, ?\'b\' => 2) will always evaluate to false.',
					366,
				],
			]
		);
	}

}
