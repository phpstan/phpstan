<?php declare(strict_types = 1);

namespace PHPStan\Type;

class FileTypeMapperTest extends \PHPStan\Testing\TestCase
{

	public function testGetResolvedPhpDoc(): void
	{
		/** @var FileTypeMapper $fileTypeMapper */
		$fileTypeMapper = self::getContainer()->getByType(FileTypeMapper::class);

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
		self::assertCount(0, $resolvedA->getVarTags());
		self::assertCount(0, $resolvedA->getParamTags());
		self::assertCount(2, $resolvedA->getPropertyTags());
		self::assertNull($resolvedA->getReturnTag());
		self::assertSame('float|int', $resolvedA->getPropertyTags()['numericBazBazProperty']->getType()->describe(VerbosityLevel::value()));
		self::assertSame('X', $resolvedA->getPropertyTags()['singleLetterObjectName']->getType()->describe(VerbosityLevel::value()));

		self::assertCount(6, $resolvedA->getMethodTags());
		self::assertArrayNotHasKey('complicatedParameters', $resolvedA->getMethodTags()); // ambiguous parameter types
		$simpleMethod = $resolvedA->getMethodTags()['simpleMethod'];
		self::assertSame('void', $simpleMethod->getReturnType()->describe(VerbosityLevel::value()));
		self::assertFalse($simpleMethod->isStatic());
		self::assertCount(0, $simpleMethod->getParameters());

		$returningMethod = $resolvedA->getMethodTags()['returningMethod'];
		self::assertSame('string', $returningMethod->getReturnType()->describe(VerbosityLevel::value()));
		self::assertFalse($returningMethod->isStatic());
		self::assertCount(0, $returningMethod->getParameters());

		$returningNullableScalar = $resolvedA->getMethodTags()['returningNullableScalar'];
		self::assertSame('float|null', $returningNullableScalar->getReturnType()->describe(VerbosityLevel::value()));
		self::assertFalse($returningNullableScalar->isStatic());
		self::assertCount(0, $returningNullableScalar->getParameters());

		$returningNullableObject = $resolvedA->getMethodTags()['returningNullableObject'];
		self::assertSame('stdClass|null', $returningNullableObject->getReturnType()->describe(VerbosityLevel::value()));
		self::assertFalse($returningNullableObject->isStatic());
		self::assertCount(0, $returningNullableObject->getParameters());

		$rotate = $resolvedA->getMethodTags()['rotate'];
		self::assertSame('Image', $rotate->getReturnType()->describe(VerbosityLevel::value()));
		self::assertFalse($rotate->isStatic());
		self::assertCount(2, $rotate->getParameters());
		self::assertSame('float', $rotate->getParameters()['angle']->getType()->describe(VerbosityLevel::value()));
		self::assertTrue($rotate->getParameters()['angle']->passedByReference()->no());
		self::assertFalse($rotate->getParameters()['angle']->isOptional());
		self::assertFalse($rotate->getParameters()['angle']->isVariadic());
		self::assertSame('mixed', $rotate->getParameters()['backgroundColor']->getType()->describe(VerbosityLevel::value()));
		self::assertTrue($rotate->getParameters()['backgroundColor']->passedByReference()->no());
		self::assertFalse($rotate->getParameters()['backgroundColor']->isOptional());
		self::assertFalse($rotate->getParameters()['backgroundColor']->isVariadic());

		$paramMultipleTypesWithExtraSpaces = $resolvedA->getMethodTags()['paramMultipleTypesWithExtraSpaces'];
		self::assertSame('float|int', $paramMultipleTypesWithExtraSpaces->getReturnType()->describe(VerbosityLevel::value()));
		self::assertFalse($paramMultipleTypesWithExtraSpaces->isStatic());
		self::assertCount(2, $paramMultipleTypesWithExtraSpaces->getParameters());
		self::assertSame('string|null', $paramMultipleTypesWithExtraSpaces->getParameters()['string']->getType()->describe(VerbosityLevel::value()));
		self::assertTrue($paramMultipleTypesWithExtraSpaces->getParameters()['string']->passedByReference()->no());
		self::assertFalse($paramMultipleTypesWithExtraSpaces->getParameters()['string']->isOptional());
		self::assertFalse($paramMultipleTypesWithExtraSpaces->getParameters()['string']->isVariadic());
		self::assertSame('stdClass|null', $paramMultipleTypesWithExtraSpaces->getParameters()['object']->getType()->describe(VerbosityLevel::value()));
		self::assertTrue($paramMultipleTypesWithExtraSpaces->getParameters()['object']->passedByReference()->no());
		self::assertFalse($paramMultipleTypesWithExtraSpaces->getParameters()['object']->isOptional());
		self::assertFalse($paramMultipleTypesWithExtraSpaces->getParameters()['object']->isVariadic());
	}

	public function testFileWithDependentPhpDocs(): void
	{
		/** @var FileTypeMapper $fileTypeMapper */
		$fileTypeMapper = self::getContainer()->getByType(FileTypeMapper::class);

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

		self::assertCount(1, $resolved->getParamTags());
		self::assertSame(
			'(DependentPhpDocs\Foo&iterable<DependentPhpDocs\Foo>)|(iterable<DependentPhpDocs\Foo>&Iterator)',
			$resolved->getParamTags()['pages']->getType()->describe(VerbosityLevel::value())
		);
	}

	public function testFileThrowsPhpDocs(): void
	{
		/** @var FileTypeMapper $fileTypeMapper */
		$fileTypeMapper = self::getContainer()->getByType(FileTypeMapper::class);

		$realpath = realpath(__DIR__ . '/data/throws-phpdocs.php');
		if ($realpath === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$resolved = $fileTypeMapper->getResolvedPhpDoc($realpath, \ThrowsPhpDocs\Foo::class, null, '/**
 * @throws RuntimeException
 */');

		self::assertNotNull($resolved->getThrowsTag());
		self::assertSame(
			\RuntimeException::class,
			$resolved->getThrowsTag()->getType()->describe(VerbosityLevel::value())
		);

		$resolved = $fileTypeMapper->getResolvedPhpDoc($realpath, \ThrowsPhpDocs\Foo::class, null, '/**
 * @throws RuntimeException|LogicException
 */');

		self::assertNotNull($resolved->getThrowsTag());
		self::assertSame(
			'LogicException|RuntimeException',
			$resolved->getThrowsTag()->getType()->describe(VerbosityLevel::value())
		);

		$resolved = $fileTypeMapper->getResolvedPhpDoc($realpath, \ThrowsPhpDocs\Foo::class, null, '/**
 * @throws RuntimeException
 * @throws LogicException
 */');

		self::assertNotNull($resolved->getThrowsTag());
		self::assertSame(
			'LogicException|RuntimeException',
			$resolved->getThrowsTag()->getType()->describe(VerbosityLevel::value())
		);
	}

	public function testFileWithCyclicPhpDocs(): void
	{
		self::getContainer()->getByType(\PHPStan\Broker\Broker::class);

		/** @var FileTypeMapper $fileTypeMapper */
		$fileTypeMapper = self::getContainer()->getByType(FileTypeMapper::class);

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

		/** @var \PHPStan\PhpDoc\Tag\ReturnTag $returnTag */
		$returnTag = $resolved->getReturnTag();
		self::assertSame('CyclicPhpDocs\Foo|iterable<CyclicPhpDocs\Foo>', $returnTag->getType()->describe(VerbosityLevel::value()));
	}

}
