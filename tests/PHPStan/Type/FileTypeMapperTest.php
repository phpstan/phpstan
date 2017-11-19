<?php declare(strict_types = 1);

namespace PHPStan\Type;

class FileTypeMapperTest extends \PHPStan\Testing\TestCase
{

	public function testAccepts()
	{
		$this->createBroker();
		$fileTypeMapper = $this->getContainer()->getByType(FileTypeMapper::class);

		$resolvedA = $fileTypeMapper->getResolvedPhpDoc(__DIR__ . '/data/annotations.php', '/**
 * @property int | float $numericBazBazProperty
 * @property X $singleLetterObjectName
 *
 * @method void simpleMethod
 * @method string returningMethod()
 * @method ?float returningNullableScalar()
 * @method ?\stdClass returningNullableObject()
 * @method void complicatedParameters(string $a, ?int|?float|?\stdClass $b, \stdClass $c = null, string|?int $d)
 * @method Image rotate(float $angle, $backgroundColor)
 * @method int | float paramMultipleTypesWithExtraSpaces(string | null $string, stdClass | null $object)
 */');

		$this->assertEquals(
			[
				'var' => [],
				'method' => [
					'simpleMethod' => [
						'returnType' => \PHPStan\Type\VoidType::__set_state([]),
						'isStatic' => false,
						'parameters' => [],
					],
					'returningMethod' => [
						'returnType' => \PHPStan\Type\StringType::__set_state([]),
						'isStatic' => false,
						'parameters' => [],
					],
					'returningNullableScalar' => [
						'returnType' => \PHPStan\Type\UnionType::__set_state([
							'types' => [
								0 => \PHPStan\Type\FloatType::__set_state([]),
								1 => \PHPStan\Type\NullType::__set_state([]),
							],
						]),
						'isStatic' => false,
						'parameters' => [],
					],
					'returningNullableObject' => [
						'returnType' => \PHPStan\Type\UnionType::__set_state([
							'types' => [
								0 => \PHPStan\Type\ObjectType::__set_state([
									'className' => 'stdClass',
								]),
								1 => \PHPStan\Type\NullType::__set_state([]),
							],
						]),
						'isStatic' => false,
						'parameters' => [],
					],
					'rotate' => [
						'returnType' => \PHPStan\Type\ObjectType::__set_state([
							'className' => 'Image',
						]),
						'isStatic' => false,
						'parameters' => [
							'angle' => [
								'type' => \PHPStan\Type\FloatType::__set_state([]),
								'isPassedByReference' => false,
								'isOptional' => false,
								'isVariadic' => false,
							],
							'backgroundColor' => [
								'type' => \PHPStan\Type\MixedType::__set_state([
									'isExplicitMixed' => false,
								]),
								'isPassedByReference' => false,
								'isOptional' => false,
								'isVariadic' => false,
							],
						],
					],
					'paramMultipleTypesWithExtraSpaces' => [
						'returnType' => \PHPStan\Type\UnionType::__set_state([
							'types' => [
								0 => \PHPStan\Type\FloatType::__set_state([]),
								1 => \PHPStan\Type\IntegerType::__set_state([]),
							],
						]),
						'isStatic' => false,
						'parameters' => [
							'string' => [
								'type' => \PHPStan\Type\UnionType::__set_state([
									'types' => [
										0 => \PHPStan\Type\StringType::__set_state([]),
										1 => \PHPStan\Type\NullType::__set_state([]),
									],
								]),
								'isPassedByReference' => false,
								'isOptional' => false,
								'isVariadic' => false,
							],
							'object' => [
								'type' => \PHPStan\Type\UnionType::__set_state([
									'types' => [
										0 => \PHPStan\Type\ObjectType::__set_state([
											'className' => 'stdClass',
										]),
										1 => \PHPStan\Type\NullType::__set_state([]),
									],
								]),
								'isPassedByReference' => false,
								'isOptional' => false,
								'isVariadic' => false,
							],
						],
					],
				],
				'property' => [
					'numericBazBazProperty' => [
						'type' => \PHPStan\Type\UnionType::__set_state([
							'types' => [
								0 => \PHPStan\Type\FloatType::__set_state([]),
								1 => \PHPStan\Type\IntegerType::__set_state([]),
							],
						]),
						'readable' => true,
						'writable' => true,
					],
					'singleLetterObjectName' => [
						'type' => \PHPStan\Type\ObjectType::__set_state([
							'className' => 'X',
						]),
						'readable' => true,
						'writable' => true,
					],
				],
				'param' => [],
				'return' => null,
			],
			$resolvedA
		);
	}

}
