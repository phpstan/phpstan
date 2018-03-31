<?php declare(strict_types = 1);

namespace PHPStan\Type;

class FileTypeMapperTest extends \PHPStan\Testing\TestCase
{

	public function testGetResolvedPhpDoc(): void
	{
		$this->createBroker();

		/** @var FileTypeMapper $fileTypeMapper */
		$fileTypeMapper = $this->getContainer()->getByType(FileTypeMapper::class);

		$resolvedA = $fileTypeMapper->getResolvedPhpDoc(__DIR__ . '/data/annotations.php', 'Foo', null, '/**
 * @property int | float $numericBazBazProperty
 * @property X $singleLetterObjectName
 *
 * @method void simpleMethod()
 * @method string returningMethod()
 * @method ?float returningNullableScalar()
 * @method ?\stdClass returningNullableObject()
 * @method void complicatedParameters(string $a, ?int|?float|?\stdClass $b, \stdClass $c = null, string|?int $d)
 * @method Image rotate(float $angle, $backgroundColor)
 * @method int | float paramMultipleTypesWithExtraSpaces(string | null $string, stdClass | null $object)
 */');
		$this->assertCount(0, $resolvedA->getVarTags());
		$this->assertCount(0, $resolvedA->getParamTags());
		$this->assertCount(2, $resolvedA->getPropertyTags());
		$this->assertNull($resolvedA->getReturnTag());
		$this->assertSame('float|int', $resolvedA->getPropertyTags()['numericBazBazProperty']->getType()->describe());
		$this->assertSame('X', $resolvedA->getPropertyTags()['singleLetterObjectName']->getType()->describe());

		$this->assertCount(6, $resolvedA->getMethodTags());
		$this->assertArrayNotHasKey('complicatedParameters', $resolvedA->getMethodTags()); // ambiguous parameter types
		$simpleMethod = $resolvedA->getMethodTags()['simpleMethod'];
		$this->assertSame('void', $simpleMethod->getReturnType()->describe());
		$this->assertFalse($simpleMethod->isStatic());
		$this->assertCount(0, $simpleMethod->getParameters());

		$returningMethod = $resolvedA->getMethodTags()['returningMethod'];
		$this->assertSame('string', $returningMethod->getReturnType()->describe());
		$this->assertFalse($returningMethod->isStatic());
		$this->assertCount(0, $returningMethod->getParameters());

		$returningNullableScalar = $resolvedA->getMethodTags()['returningNullableScalar'];
		$this->assertSame('float|null', $returningNullableScalar->getReturnType()->describe());
		$this->assertFalse($returningNullableScalar->isStatic());
		$this->assertCount(0, $returningNullableScalar->getParameters());

		$returningNullableObject = $resolvedA->getMethodTags()['returningNullableObject'];
		$this->assertSame('stdClass|null', $returningNullableObject->getReturnType()->describe());
		$this->assertFalse($returningNullableObject->isStatic());
		$this->assertCount(0, $returningNullableObject->getParameters());

		$rotate = $resolvedA->getMethodTags()['rotate'];
		$this->assertSame('Image', $rotate->getReturnType()->describe());
		$this->assertFalse($rotate->isStatic());
		$this->assertCount(2, $rotate->getParameters());
		$this->assertSame('float', $rotate->getParameters()['angle']->getType()->describe());
		$this->assertTrue($rotate->getParameters()['angle']->passedByReference()->no());
		$this->assertFalse($rotate->getParameters()['angle']->isOptional());
		$this->assertFalse($rotate->getParameters()['angle']->isVariadic());
		$this->assertSame('mixed', $rotate->getParameters()['backgroundColor']->getType()->describe());
		$this->assertTrue($rotate->getParameters()['backgroundColor']->passedByReference()->no());
		$this->assertFalse($rotate->getParameters()['backgroundColor']->isOptional());
		$this->assertFalse($rotate->getParameters()['backgroundColor']->isVariadic());

		$paramMultipleTypesWithExtraSpaces = $resolvedA->getMethodTags()['paramMultipleTypesWithExtraSpaces'];
		$this->assertSame('float|int', $paramMultipleTypesWithExtraSpaces->getReturnType()->describe());
		$this->assertFalse($paramMultipleTypesWithExtraSpaces->isStatic());
		$this->assertCount(2, $paramMultipleTypesWithExtraSpaces->getParameters());
		$this->assertSame('string|null', $paramMultipleTypesWithExtraSpaces->getParameters()['string']->getType()->describe());
		$this->assertTrue($paramMultipleTypesWithExtraSpaces->getParameters()['string']->passedByReference()->no());
		$this->assertFalse($paramMultipleTypesWithExtraSpaces->getParameters()['string']->isOptional());
		$this->assertFalse($paramMultipleTypesWithExtraSpaces->getParameters()['string']->isVariadic());
		$this->assertSame('stdClass|null', $paramMultipleTypesWithExtraSpaces->getParameters()['object']->getType()->describe());
		$this->assertTrue($paramMultipleTypesWithExtraSpaces->getParameters()['object']->passedByReference()->no());
		$this->assertFalse($paramMultipleTypesWithExtraSpaces->getParameters()['object']->isOptional());
		$this->assertFalse($paramMultipleTypesWithExtraSpaces->getParameters()['object']->isVariadic());
	}

	public function testFileWithDependentPhpDocs(): void
	{
		$this->createBroker();

		/** @var FileTypeMapper $fileTypeMapper */
		$fileTypeMapper = $this->getContainer()->getByType(FileTypeMapper::class);

		$realpath = realpath(__DIR__ . '/data/dependent-phpdocs.php');
		if ($realpath === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$resolved = $fileTypeMapper->getResolvedPhpDoc(
			$realpath,
			\DependentPhpDocs\Foo::class,
			null,
			'/** @param Foo[]|Foo|\Iterator $pages */'
		);

		$this->assertCount(1, $resolved->getParamTags());
		$this->assertSame(
			'(DependentPhpDocs\Foo&iterable<DependentPhpDocs\Foo>)|(iterable<DependentPhpDocs\Foo>&Iterator)',
			$resolved->getParamTags()['pages']->getType()->describe()
		);
	}


	public function testFileWithCyclicPhpDocs(): void
	{
		$this->getContainer()->getByType(\PHPStan\Broker\Broker::class);

		/** @var FileTypeMapper $fileTypeMapper */
		$fileTypeMapper = $this->getContainer()->getByType(FileTypeMapper::class);

		$realpath = realpath(__DIR__ . '/data/cyclic-phpdocs.php');
		if ($realpath === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$resolved = $fileTypeMapper->getResolvedPhpDoc(
			$realpath,
			\CyclicPhpDocs\Foo::class,
			null,
			'/** @return iterable<Foo> | Foo */'
		);

		$returnType = $resolved->getReturnTag()->getType() ?? new MixedType();
		$this->assertSame('CyclicPhpDocs\Foo|iterable<CyclicPhpDocs\Foo>', $returnType->describe());
	}

}
