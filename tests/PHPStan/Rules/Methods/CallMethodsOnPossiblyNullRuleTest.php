<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

class CallMethodsOnPossiblyNullRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new CallMethodsOnPossiblyNullRule();
	}

	public function testExistingClassInTypehint(): void
	{
		$this->analyse([__DIR__ . '/data/possibly-nullable.php'], [
			[
				'Calling method format() on possibly null value of type DateTimeImmutable|null.',
				11,
			],
			[
				'Calling method find() on possibly null value of type CallingMethodOnPossiblyNullable\NullCoalesce|null.',
				58,
			],
			[
				'Calling method find() on possibly null value of type CallingMethodOnPossiblyNullable\NullCoalesce|null.',
				60,
			],
			[
				'Calling method find() on possibly null value of type CallingMethodOnPossiblyNullable\NullCoalesce|null.',
				64,
			],
			[
				'Calling method doFoo() on possibly null value of type CallingMethodOnPossiblyNullable\IssetIssue|null.',
				92,
			],
			[
				'Calling method doFoo() on possibly null value of type CallingMethodOnPossiblyNullable\IssetIssue|null.',
				93,
			],
			[
				'Calling method doFoo() on possibly null value of type CallingMethodOnPossiblyNullable\IssetIssue|null.',
				94,
			],
			[
				'Calling method add() on possibly null value of type DateTime|null.',
				128,
			],
		]);
	}

}
