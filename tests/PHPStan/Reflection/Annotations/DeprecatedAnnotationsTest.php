<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Annotations;

use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\DeprecatableReflection;

class DeprecatedAnnotationsTest extends \PHPStan\Testing\TestCase
{

	public function dataDeprecatedAnnotations(): array
	{
		return [
			[
				false,
				\DeprecatedAnnotations\Foo::class,
				null,
				[
					'constant' => [
						'FOO' => null,
					],
					'method' => [
						'foo' => null,
						'staticFoo' => null,
					],
					'property' => [
						'foo' => null,
						'staticFoo' => null,
					],
				],
			],
			[
				true,
				\DeprecatedAnnotations\DeprecatedFoo::class,
				'DeprecatedAnnotations\DeprecatedFoo is deprecated in 1.0.0.',
				[
					'constant' => [
						'DEPRECATED_FOO' => 'DEPRECATED_FOO is deprecated Deprecated constant.',
					],
					'method' => [
						'deprecatedFoo' => 'DeprecatedAnnotations\DeprecatedFoo::deprecatedFoo is deprecated method.',
						'deprecatedStaticFoo' => 'DeprecatedAnnotations\DeprecatedFoo::deprecatedStaticFoo is deprecated static method.',
					],
					'property' => [
						'deprecatedFoo' => 'DeprecatedAnnotations\DeprecatedFoo::deprecatedFoo is deprecated.',
						'deprecatedStaticFoo' => 'DeprecatedAnnotations\DeprecatedFoo::deprecatedStaticFoo is deprecated.',
					],
				],
			],
			[
				false,
				\DeprecatedAnnotations\FooInterface::class,
				null,
				[
					'constant' => [
						'FOO' => null,
					],
					'method' => [
						'foo' => null,
						'staticFoo' => null,
					],
				],
			],
			[
				true,
				\DeprecatedAnnotations\DeprecatedWithMultipleTags::class,
				'DeprecatedAnnotations\DeprecatedWithMultipleTags is deprecated in Foo 1.1.0 and will be removed in 1.5.0, use \Foo\Bar\NotDeprecated instead.',
				[
					'method' => [
						'deprecatedFoo' => 'DeprecatedAnnotations\DeprecatedWithMultipleTags::deprecatedFoo is deprecated in Foo 1.1.0, will be removed in Foo 1.5.0, use \Foo\Bar\NotDeprecated::replacementFoo() instead.',
					],
				],
			],
		];
	}

	/**
	 * @dataProvider dataDeprecatedAnnotations
	 * @param bool $deprecated
	 * @param string $className
	 * @param string|null $classDeprecation
	 * @param array<string, mixed> $deprecatedAnnotations
	 */
	public function testDeprecatedAnnotations(bool $deprecated, string $className, ?string $classDeprecation, array $deprecatedAnnotations): void
	{
		/** @var Broker $broker */
		$broker = self::getContainer()->getByType(Broker::class);
		$class = $broker->getClass($className);
		$scope = $this->createMock(Scope::class);
		$scope->method('isInClass')->willReturn(true);
		$scope->method('getClassReflection')->willReturn($class);
		$scope->method('canAccessProperty')->willReturn(true);

		$this->assertSame($deprecated, $class->isDeprecated());
		$this->assertSame($classDeprecation, $class->getDeprecatedDescription());

		foreach ($deprecatedAnnotations['method'] ?? [] as $methodName => $deprecatedMessage) {
			$methodAnnotation = $class->getMethod($methodName, $scope);
			$this->assertInstanceOf(DeprecatableReflection::class, $methodAnnotation);
			$this->assertSame($deprecated, $methodAnnotation->isDeprecated());
			$this->assertSame($deprecatedMessage, $methodAnnotation->getDeprecatedDescription());
		}

		foreach ($deprecatedAnnotations['property'] ?? [] as $propertyName => $deprecatedMessage) {
			$propertyAnnotation = $class->getProperty($propertyName, $scope);
			$this->assertInstanceOf(DeprecatableReflection::class, $propertyAnnotation);
			$this->assertSame($deprecated, $propertyAnnotation->isDeprecated());
			$this->assertSame($deprecatedMessage, $propertyAnnotation->getDeprecatedDescription());
		}

		foreach ($deprecatedAnnotations['constant'] ?? [] as $constantName => $deprecatedMessage) {
			$constantAnnotation = $class->getConstant($constantName);
			$this->assertInstanceOf(DeprecatableReflection::class, $constantAnnotation);
			$this->assertSame($deprecated, $constantAnnotation->isDeprecated());
			$this->assertSame($deprecatedMessage, $constantAnnotation->getDeprecatedDescription());
		}
	}

	public function testDeprecatedUserFunctions(): void
	{
		require_once __DIR__ . '/data/annotations-deprecated.php';

		/** @var Broker $broker */
		$broker = self::getContainer()->getByType(Broker::class);

		$this->assertFalse($broker->getFunction(new Name\FullyQualified('DeprecatedAnnotations\foo'), null)->isDeprecated());
		$this->assertTrue($broker->getFunction(new Name\FullyQualified('DeprecatedAnnotations\deprecatedFoo'), null)->isDeprecated());
	}

	public function testNonDeprecatedNativeFunctions(): void
	{
		/** @var Broker $broker */
		$broker = self::getContainer()->getByType(Broker::class);

		$this->assertFalse($broker->getFunction(new Name('str_replace'), null)->isDeprecated());
		$this->assertFalse($broker->getFunction(new Name('get_class'), null)->isDeprecated());
		$this->assertFalse($broker->getFunction(new Name('function_exists'), null)->isDeprecated());
	}

}
