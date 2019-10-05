<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\Generic\TemplateTypeFactory;
use PHPStan\Type\Generic\TemplateTypeHelper;
use PHPStan\Type\Generic\TemplateTypeScope;

class TemplateTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataAccepts(): array
	{
		$templateType = static function (string $name, ?Type $bound, ?string $functionName = null): Type {
			return TemplateTypeFactory::create(
				TemplateTypeScope::createWithFunction($functionName ?? '_'),
				$name,
				$bound
			);
		};

		return [
			[
				$templateType('T', new ObjectType('DateTime')),
				new ObjectType('DateTime'),
				TrinaryLogic::createYes(),
				TrinaryLogic::createNo(),
			],
			[
				$templateType('T', new ObjectType('DateTime')),
				$templateType('T', new ObjectType('DateTime')),
				TrinaryLogic::createYes(),
				TrinaryLogic::createYes(),
			],
			[
				$templateType('T', new ObjectType('DateTime'), 'a'),
				$templateType('T', new ObjectType('DateTime'), 'b'),
				TrinaryLogic::createMaybe(),
				TrinaryLogic::createNo(),
			],
			[
				$templateType('T', null),
				new MixedType(),
				TrinaryLogic::createYes(),
				TrinaryLogic::createYes(),
			],
			[
				$templateType('T', null),
				new IntersectionType([
					new ObjectWithoutClassType(),
					$templateType('T', null),
				]),
				TrinaryLogic::createYes(),
				TrinaryLogic::createYes(),
			],
		];
	}

	/**
	 * @dataProvider dataAccepts
	 */
	public function testAccepts(
		Type $type,
		Type $otherType,
		TrinaryLogic $expectedAccept,
		TrinaryLogic $expectedAcceptArg
	): void
	{
		assert($type instanceof TemplateType);

		$actualResult = $type->accepts($otherType, true);
		$this->assertSame(
			$expectedAccept->describe(),
			$actualResult->describe(),
			sprintf('%s -> accepts(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);

		$type = $type->toArgument();

		$actualResult = $type->accepts($otherType, true);
		$this->assertSame(
			$expectedAcceptArg->describe(),
			$actualResult->describe(),
			sprintf('%s -> accepts(%s) (Argument strategy)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}

	public function dataIsSuperTypeOf(): array
	{
		$templateType = static function (string $name, ?Type $bound, ?string $functionName = null): Type {
			return TemplateTypeFactory::create(
				TemplateTypeScope::createWithFunction($functionName ?? '_'),
				$name,
				$bound
			);
		};

		return [
			[
				$templateType('T', new ObjectType('DateTime')),
				new ObjectType('DateTime'),
				TrinaryLogic::createMaybe(),
				TrinaryLogic::createMaybe(),
			],
			[
				$templateType('T', new ObjectType('DateTime')),
				$templateType('T', new ObjectType('DateTime')),
				TrinaryLogic::createYes(),
				TrinaryLogic::createYes(),
			],
			[
				$templateType('T', new ObjectType('DateTime'), 'a'),
				$templateType('T', new ObjectType('DateTime'), 'b'),
				TrinaryLogic::createMaybe(),
				TrinaryLogic::createMaybe(),
			],
			[
				$templateType('T', new ObjectType('DateTime')),
				new StringType(),
				TrinaryLogic::createNo(),
				TrinaryLogic::createNo(),
			],
			[
				$templateType('T', new ObjectType('DateTime')),
				new ObjectType('DateTimeInterface'),
				TrinaryLogic::createMaybe(),
				TrinaryLogic::createMaybe(),
			],
			[
				$templateType('T', new ObjectType('DateTime')),
				$templateType('T', new ObjectType('DateTimeInterface')),
				TrinaryLogic::createMaybe(),
				TrinaryLogic::createMaybe(),
			],
			[
				$templateType('T', new ObjectType('DateTime')),
				new UnionType([
					new NullType(),
					new ObjectType('DateTime'),
				]),
				TrinaryLogic::createMaybe(),
				TrinaryLogic::createMaybe(),
			],
			[
				$templateType('T', null),
				new MixedType(true),
				TrinaryLogic::createMaybe(),
				TrinaryLogic::createYes(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 */
	public function testIsSuperTypeOf(
		Type $type,
		Type $otherType,
		TrinaryLogic $expectedIsSuperType,
		TrinaryLogic $expectedIsSuperTypeInverse
	): void
	{
		assert($type instanceof TemplateType);

		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedIsSuperType->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);

		$actualResult = $otherType->isSuperTypeOf($type);
		$this->assertSame(
			$expectedIsSuperTypeInverse->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $otherType->describe(VerbosityLevel::precise()), $type->describe(VerbosityLevel::precise()))
		);
	}

	/** @return array<string,array{Type,Type,array<string,string>}> */
	public function dataInferTemplateTypes(): array
	{
		$templateType = static function (string $name, ?Type $bound = null, ?string $functionName = null): Type {
			return TemplateTypeFactory::create(
				TemplateTypeScope::createWithFunction($functionName ?? '_'),
				$name,
				$bound
			);
		};

		return [
			'simple' => [
				new IntegerType(),
				$templateType('T'),
				['T' => 'int'],
			],
			'object' => [
				new ObjectType(\DateTime::class),
				$templateType('T'),
				['T' => 'DateTime'],
			],
			'object with bound' => [
				new ObjectType(\DateTime::class),
				$templateType('T', new ObjectType(\DateTimeInterface::class)),
				['T' => 'DateTime'],
			],
			'wrong object with bound' => [
				new ObjectType(\stdClass::class),
				$templateType('T', new ObjectType(\DateTimeInterface::class)),
				[],
			],
			'template type' => [
				TemplateTypeHelper::toArgument($templateType('T', new ObjectType(\DateTimeInterface::class))),
				$templateType('T', new ObjectType(\DateTimeInterface::class)),
				['T' => 'T of DateTimeInterface (function _(), argument)'],
			],
			'foreign template type' => [
				TemplateTypeHelper::toArgument($templateType('T', new ObjectType(\DateTimeInterface::class), 'a')),
				$templateType('T', new ObjectType(\DateTimeInterface::class), 'b'),
				['T' => 'T of DateTimeInterface (function a(), argument)'],
			],
			'foreign template type, imcompatible bound' => [
				TemplateTypeHelper::toArgument($templateType('T', new ObjectType(\stdClass::class), 'a')),
				$templateType('T', new ObjectType(\DateTime::class), 'b'),
				[],
			],
		];
	}

	/**
	 * @dataProvider dataInferTemplateTypes
	 * @param array<string,string> $expectedTypes
	 */
	public function testResolveTemplateTypes(Type $received, Type $template, array $expectedTypes): void
	{
		$result = $template->inferTemplateTypes($received);

		$this->assertSame(
			$expectedTypes,
			array_map(static function (Type $type): string {
				return $type->describe(VerbosityLevel::precise());
			}, $result->getTypes())
		);
	}

}
