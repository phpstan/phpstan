<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\TrinaryLogic;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Test\A;
use PHPStan\Type\Test\B;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

class GenericObjectTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataIsSuperTypeOf(): array
	{
		return [
			'equal type' => [
				new GenericObjectType(A\A::class, [new ObjectType('DateTime')]),
				new GenericObjectType(A\A::class, [new ObjectType('DateTime')]),
				TrinaryLogic::createYes(),
			],
			'sub-class with static @extends with same type args' => [
				new GenericObjectType(A\A::class, [new ObjectType('DateTime')]),
				new ObjectType(A\AOfDateTime::class),
				TrinaryLogic::createYes(),
			],
			'sub-class with @extends with same type args' => [
				new GenericObjectType(A\A::class, [new ObjectType('DateTime')]),
				new GenericObjectType(A\SubA::class, [new ObjectType('DateTime')]),
				TrinaryLogic::createYes(),
			],
			'same class, different type args' => [
				new GenericObjectType(A\A::class, [new ObjectType('DateTimeInterface')]),
				new GenericObjectType(A\A::class, [new ObjectType('DateTime')]),
				TrinaryLogic::createNo(),
			],
			'same class, one naked' => [
				new GenericObjectType(A\A::class, [new ObjectType('DateTimeInterface')]),
				new ObjectType(A\A::class),
				TrinaryLogic::createMaybe(),
			],
			'implementation with @extends with same type args' => [
				new GenericObjectType(B\I::class, [new ObjectType('DateTime')]),
				new GenericObjectType(B\IImpl::class, [new ObjectType('DateTime')]),
				TrinaryLogic::createYes(),
			],
			'implementation with @extends with different type args' => [
				new GenericObjectType(B\I::class, [new ObjectType('DateTimeInteface')]),
				new GenericObjectType(B\IImpl::class, [new ObjectType('DateTime')]),
				TrinaryLogic::createNo(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 */
	public function testIsSuperTypeOf(Type $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}

	public function dataAccepts(): array
	{
		return [
			'equal type' => [
				new GenericObjectType(A\A::class, [new ObjectType('DateTime')]),
				new GenericObjectType(A\A::class, [new ObjectType('DateTime')]),
				TrinaryLogic::createYes(),
			],
			'sub-class with static @extends with same type args' => [
				new GenericObjectType(A\A::class, [new ObjectType('DateTime')]),
				new ObjectType(A\AOfDateTime::class),
				TrinaryLogic::createYes(),
			],
			'sub-class with @extends with same type args' => [
				new GenericObjectType(A\A::class, [new ObjectType('DateTime')]),
				new GenericObjectType(A\SubA::class, [new ObjectType('DateTime')]),
				TrinaryLogic::createYes(),
			],
			'same class, different type args' => [
				new GenericObjectType(A\A::class, [new ObjectType('DateTimeInterface')]),
				new GenericObjectType(A\A::class, [new ObjectType('DateTime')]),
				TrinaryLogic::createNo(),
			],
			'same class, one naked' => [
				new GenericObjectType(A\A::class, [new ObjectType('DateTimeInterface')]),
				new ObjectType(A\A::class),
				TrinaryLogic::createYes(),
			],
			'implementation with @extends with same type args' => [
				new GenericObjectType(B\I::class, [new ObjectType('DateTime')]),
				new GenericObjectType(B\IImpl::class, [new ObjectType('DateTime')]),
				TrinaryLogic::createYes(),
			],
			'implementation with @extends with different type args' => [
				new GenericObjectType(B\I::class, [new ObjectType('DateTimeInteface')]),
				new GenericObjectType(B\IImpl::class, [new ObjectType('DateTime')]),
				TrinaryLogic::createNo(),
			],
			'generic object accepts normal object of same type' => [
				new GenericObjectType(\Traversable::class, [new MixedType(true), new ObjectType('DateTimeInteface')]),
				new ObjectType(\Traversable::class),
				TrinaryLogic::createYes(),
			],
		];
	}

	/**
	 * @dataProvider dataAccepts
	 */
	public function testAccepts(
		Type $acceptingType,
		Type $acceptedType,
		TrinaryLogic $expectedResult
	): void
	{
		$actualResult = $acceptingType->accepts($acceptedType, true);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> accepts(%s)', $acceptingType->describe(VerbosityLevel::precise()), $acceptedType->describe(VerbosityLevel::precise()))
		);
	}

	/** @return array<string,array{Type,Type,array<string,string>}> */
	public function dataInferTemplateTypes(): array
	{
		$templateType = static function (string $name, ?Type $bound = null): Type {
			return TemplateTypeFactory::create(
				TemplateTypeScope::createWithFunction('a'),
				$name,
				$bound ?? new MixedType()
			);
		};

		return [
			'simple' => [
				new GenericObjectType(A\A::class, [
					new ObjectType(\DateTime::class),
				]),
				new GenericObjectType(A\A::class, [
					$templateType('T'),
				]),
				['T' => 'DateTime'],
			],
			'two types' => [
				new GenericObjectType(A\A2::class, [
					new ObjectType(\DateTime::class),
					new IntegerType(),
				]),
				new GenericObjectType(A\A2::class, [
					$templateType('K'),
					$templateType('V'),
				]),
				['K' => 'DateTime', 'V' => 'int'],
			],
			'union' => [
				new UnionType([
					new GenericObjectType(A\A2::class, [
						new ObjectType(\DateTime::class),
						new IntegerType(),
					]),
					new GenericObjectType(A\A2::class, [
						new IntegerType(),
						new ObjectType(\DateTime::class),
					]),
				]),
				new GenericObjectType(A\A2::class, [
					$templateType('K'),
					$templateType('V'),
				]),
				['K' => 'DateTime|int', 'V' => 'DateTime|int'],
			],
			'nested' => [
				new GenericObjectType(A\A::class, [
					new GenericObjectType(A\A2::class, [
						new ObjectType(\DateTime::class),
						new IntegerType(),
					]),
				]),
				new GenericObjectType(A\A::class, [
					new GenericObjectType(A\A2::class, [
						$templateType('K'),
						$templateType('V'),
					]),
				]),
				['K' => 'DateTime', 'V' => 'int'],
			],
			'missing type' => [
				new GenericObjectType(A\A2::class, [
					new ObjectType(\DateTime::class),
				]),
				new GenericObjectType(A\A2::class, [
					$templateType('K', new ObjectType(\DateTimeInterface::class)),
					$templateType('V', new ObjectType(\DateTimeInterface::class)),
				]),
				['K' => 'DateTime'],
			],
			'wrong class' => [
				new GenericObjectType(B\I::class, [
					new ObjectType(\DateTime::class),
				]),
				new GenericObjectType(A\A::class, [
					$templateType('T', new ObjectType(\DateTimeInterface::class)),
				]),
				[],
			],
			'wrong type' => [
				new IntegerType(),
				new GenericObjectType(A\A::class, [
					$templateType('T', new ObjectType(\DateTimeInterface::class)),
				]),
				[],
			],
			'sub type' => [
				new ObjectType(A\AOfDateTime::class),
				new GenericObjectType(A\A::class, [
					$templateType('T'),
				]),
				['T' => 'DateTime'],
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
