<?php declare(strict_types = 1);

namespace PHPStan\Generics;

use PHPStan\Type\ErrorType;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\Generic\TemplateTypeFactory;
use PHPStan\Type\Generic\TemplateTypeScope;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

class TemplateTypeFactoryTest extends \PHPStan\Testing\TestCase
{

	/** @return array<array{?Type, bool}> */
	public function dataCreate(): array
	{
		return [
			[
				new ObjectType('DateTime'),
				true,
			],
			[
				new MixedType(),
				true,
			],
			[
				null,
				true,
			],
			[
				new StringType(),
				false,
			],
			[
				new ErrorType(),
				false,
			],
			[
				TemplateTypeFactory::create(
					TemplateTypeScope::createWithFunction('a'),
					'U',
					null
				),
				false,
			],
			[
				new UnionType([
					new StringType(),
					new IntegerType(),
				]),
				false,
			],
		];
	}

	/**
	 * @dataProvider dataCreate
	 */
	public function testCreate(?Type $bound, bool $expectSuccess): void
	{
		$scope = TemplateTypeScope::createWithFunction('a');
		$templateType = TemplateTypeFactory::create($scope, 'T', $bound);

		if ($expectSuccess) {
			$this->assertInstanceOf(TemplateType::class, $templateType);
			$this->assertTrue(($bound ?? new MixedType())->equals($templateType->getBound()));
		} else {
			$this->assertInstanceOf(ErrorType::class, $templateType);
		}
	}

}
