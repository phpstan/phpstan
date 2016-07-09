<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

class CallToNonExistentFunctionRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new CallToNonExistentFunctionRule($this->createBroker());
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
				'Function foobarNonExistentFunction not found.',
				5,
			],
		]);
	}

	public function testCallToNonexistentNestedFunction()
	{
		$this->analyse([__DIR__ . '/data/nonexistent-nested-function.php'], [
			[
				'Function barNonExistentFunction not found.',
				5,
			],
		]);
	}

	public function testCallToIncorrectCaseFunctionName()
	{
		require_once __DIR__ . '/data/incorrect-function-case-definition.php';
		$this->analyse([__DIR__ . '/data/incorrect-function-case.php'], [
			[
				'Call to function IncorrectFunctionCase\fooBar() with incorrect case: foobar',
				5,
			],
			[
				'Call to function IncorrectFunctionCase\fooBar() with incorrect case: IncorrectFunctionCase\foobar',
				7,
			],
			[
				'Call to function htmlspecialchars() with incorrect case: htmlSpecialChars',
				10,
			],
		]);
	}

}
