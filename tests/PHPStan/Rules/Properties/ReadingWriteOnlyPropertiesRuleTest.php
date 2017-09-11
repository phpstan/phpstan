<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PHPStan\Rules\RuleLevelHelper;

class ReadingWriteOnlyPropertiesRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

	/** @var bool */
	private $checkThisOnly;

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new ReadingWriteOnlyPropertiesRule(new PropertyDescriptor(), new PropertyReflectionFinder(), new RuleLevelHelper(true), $this->checkThisOnly);
	}

	public function testPropertyMustBeReadableInAssignOp()
	{
		$this->checkThisOnly = false;
		$this->analyse([__DIR__ . '/data/writing-to-read-only-properties.php'], [
			[
				'Property WritingToReadOnlyProperties\Foo::$writeOnlyProperty is not readable.',
				22,
			],
			[
				'Property WritingToReadOnlyProperties\Foo::$writeOnlyProperty is not readable.',
				32,
			],
		]);
	}

	public function testPropertyMustBeReadableInAssignOpCheckThisOnly()
	{
		$this->checkThisOnly = true;
		$this->analyse([__DIR__ . '/data/writing-to-read-only-properties.php'], [
			[
				'Property WritingToReadOnlyProperties\Foo::$writeOnlyProperty is not readable.',
				22,
			],
		]);
	}

	public function testReadingWriteOnlyProperties()
	{
		$this->checkThisOnly = false;
		$this->analyse([__DIR__ . '/data/reading-write-only-properties.php'], [
			[
				'Property ReadingWriteOnlyProperties\Foo::$writeOnlyProperty is not readable.',
				17,
			],
			[
				'Property ReadingWriteOnlyProperties\Foo::$writeOnlyProperty is not readable.',
				22,
			],
		]);
	}

	public function testReadingWriteOnlyPropertiesCheckThisOnly()
	{
		$this->checkThisOnly = true;
		$this->analyse([__DIR__ . '/data/reading-write-only-properties.php'], [
			[
				'Property ReadingWriteOnlyProperties\Foo::$writeOnlyProperty is not readable.',
				17,
			],
		]);
	}

}
