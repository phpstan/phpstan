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
				[
					'constant' => [
						'FOO',
					],
					'method' => [
						'foo',
						'staticFoo',
					],
					'property' => [
						'foo',
						'staticFoo',
					],
				],
			],
			[
				true,
				\DeprecatedAnnotations\DeprecatedFoo::class,
				[
					'constant' => [
						'DEPRECATED_FOO',
					],
					'method' => [
						'deprecatedFoo',
						'deprecatedStaticFoo',
					],
					'property' => [
						'deprecatedFoo',
						'deprecatedStaticFoo',
					],
				],
			],
			[
				false,
				\DeprecatedAnnotations\FooInterface::class,
				[
					'constant' => [
						'FOO',
					],
					'method' => [
						'foo',
						'staticFoo',
					],
					'property' => [
						'foo',
						'staticFoo',
					],
				],
			],
			[
				true,
				\DeprecatedAnnotations\DeprecatedFooInterface::class,
				[
					'constant' => [
						'DEPRECATED_FOO',
					],
					'method' => [
						'deprecatedFoo',
						'deprecatedStaticFoo',
					],
					'property' => [
						'deprecatedFoo',
						'deprecatedStaticFoo',
					],
				],
			],
			[
				false,
				\DeprecatedAnnotations\FooTrait::class,
				[
					'method' => [
						'foo',
						'staticFoo',
					],
					'property' => [
						'foo',
						'staticFoo',
					],
				],
			],
			[
				true,
				\DeprecatedAnnotations\DeprecatedFooTrait::class,
				[
					'method' => [
						'deprecatedFoo',
						'deprecatedStaticFoo',
					],
					'property' => [
						'deprecatedFoo',
						'deprecatedStaticFoo',
					],
				],
			],
		];
	}

	/**
	 * @dataProvider dataDeprecatedAnnotations
	 * @param bool $deprecated
	 * @param string $className
	 * @param mixed[] $deprecatedAnnotations
	 */
	public function testDeprecatedAnnotations(bool $deprecated, string $className, array $deprecatedAnnotations): void
	{
		/** @var Broker $broker */
		$broker = $this->getContainer()->getByType(Broker::class);
		$class = $broker->getClass($className);
		$scope = $this->createMock(Scope::class);
		$scope->method('isInClass')->willReturn(true);
		$scope->method('getClassReflection')->willReturn($class);
		$scope->method('canAccessProperty')->willReturn(true);

		$this->assertSame($deprecated, $class->isDeprecated());

		foreach ($deprecatedAnnotations as $memberType => $members) {
			foreach ($members as $memberName) {
				// Constants in PHP <7.1 don't support PHPDocs via reflection
				$memberDeprecated = $deprecated && ($memberType !== 'constant' || PHP_VERSION_ID >= 70100);
				$memberAnnotation = $class->{'get' . ucfirst($memberType)}($memberName, $scope);
				$this->assertInstanceOf(DeprecatableReflection::class, $memberAnnotation);
				$this->assertSame($memberDeprecated, $memberAnnotation->isDeprecated());
			}
		}
	}

	public function testDeprecatedUserFunctions(): void
	{
		require_once __DIR__ . '/data/annotations-deprecated.php';

		/** @var Broker $broker */
		$broker = $this->getContainer()->getByType(Broker::class);
		$scope = $this->createMock(Scope::class);

		$this->assertFalse($broker->getFunction(new Name('\DeprecatedAnnotations\foo'), $scope)->isDeprecated());
		$this->assertTrue($broker->getFunction(new Name('\DeprecatedAnnotations\deprecatedFoo'), $scope)->isDeprecated());
	}

	public function testNonDeprecatedNativeFunctions(): void
	{
		/** @var Broker $broker */
		$broker = $this->getContainer()->getByType(Broker::class);
		$scope = $this->createMock(Scope::class);

		$this->assertFalse($broker->getFunction(new Name('str_replace'), $scope)->isDeprecated());
		$this->assertFalse($broker->getFunction(new Name('get_class'), $scope)->isDeprecated());
		$this->assertFalse($broker->getFunction(new Name('function_exists'), $scope)->isDeprecated());
	}

	// public function testDeprecatedNativeFunctions(): void
	// {
	// 	// FIXME: Sadly, I found no native function that was deprecated in PHP >=7.0
	// }

}
