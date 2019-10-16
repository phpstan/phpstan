<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\VerbosityLevel;

class TemplateTypeHelperTest extends \PHPStan\Testing\TestCase
{

	public function testIssue2512(): void
	{
		$templateType = TemplateTypeFactory::create(
			TemplateTypeScope::createWithFunction('a'),
			'T',
			null
		);

		$type = TemplateTypeHelper::resolveTemplateTypes(
			$templateType,
			new TemplateTypeMap([
				'T' => $templateType,
			])
		);

		$this->assertEquals(
			'T (function a(), parameter)',
			$type->describe(VerbosityLevel::precise())
		);

		$type = TemplateTypeHelper::resolveTemplateTypes(
			$templateType,
			new TemplateTypeMap([
				'T' => new IntersectionType([
					new ObjectType(\DateTime::class),
					$templateType,
				]),
			])
		);

		$this->assertEquals(
			'DateTime&T (function a(), parameter)',
			$type->describe(VerbosityLevel::precise())
		);
	}

}
