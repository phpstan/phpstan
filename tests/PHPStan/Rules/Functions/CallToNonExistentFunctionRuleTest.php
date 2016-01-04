<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

class CallToNonExistentFunctionRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new CallToNonExistentFunctionRule($this->getBroker());
	}

	public function testEmptyFile()
	{
		$this->analyse([__DIR__ . '/data/empty.php'], []);
	}

	public function testCallToExistingFunction()
	{
		require_once __DIR__ . '/data/existing-function-definition.php';
		$this->analyse([__DIR__ . '/data/existing-function.php'], []);
	}

	public function testCallToNonexistentFunction()
	{
		$this->analyse([__DIR__ . '/data/nonexistent-function.php'], [
			[
				'Function foobarNonExistentFunction does not exist.',
				5,
			],
		]);
	}

	public function testCallToNonexistentNestedFunction()
	{
		$this->analyse([__DIR__ . '/data/nonexistent-nested-function.php'], [
			[
				'Function barNonExistentFunction does not exist.',
				5,
			],
		]);
	}

}
