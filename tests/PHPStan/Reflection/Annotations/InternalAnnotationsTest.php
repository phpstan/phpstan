<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Annotations;

use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\InternableReflection;

class InternalAnnotationsTest extends \PHPStan\Testing\TestCase
{

	public function dataInternalAnnotations(): array
	{
		return [
			[
				false,
				\InternalAnnotations\Foo::class,
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
				\InternalAnnotations\InternalFoo::class,
				[
					'constant' => [
						'INTERNAL_FOO',
					],
					'method' => [
						'internalFoo',
						'internalStaticFoo',
					],
					'property' => [
						'internalFoo',
						'internalStaticFoo',
					],
				],
			],
			[
				false,
				\InternalAnnotations\FooInterface::class,
				[
					'constant' => [
						'FOO',
					],
					'method' => [
						'foo',
						'staticFoo',
					],
				],
			],
			[
				true,
				\InternalAnnotations\InternalFooInterface::class,
				[
					'constant' => [
						'INTERNAL_FOO',
					],
					'method' => [
						'internalFoo',
						'internalStaticFoo',
					],
				],
			],
			[
				false,
				\InternalAnnotations\FooTrait::class,
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
				\InternalAnnotations\InternalFooTrait::class,
				[
					'method' => [
						'internalFoo',
						'internalStaticFoo',
					],
					'property' => [
						'internalFoo',
						'internalStaticFoo',
					],
				],
			],
		];
	}

	/**
	 * @dataProvider dataInternalAnnotations
	 * @param bool $internal
	 * @param string $className
	 * @param array<string, mixed> $internalAnnotations
	 */
	public function testInternalAnnotations(bool $internal, string $className, array $internalAnnotations): void
	{
		/** @var Broker $broker */
		$broker = self::getContainer()->getByType(Broker::class);
		$class = $broker->getClass($className);
		$scope = $this->createMock(Scope::class);
		$scope->method('isInClass')->willReturn(true);
		$scope->method('getClassReflection')->willReturn($class);
		$scope->method('canAccessProperty')->willReturn(true);

		$this->assertSame($internal, $class->isInternal());

		foreach ($internalAnnotations as $memberType => $members) {
			foreach ($members as $memberName) {
				$memberAnnotation = $class->{'get' . \ucfirst($memberType)}($memberName, $scope);
				$this->assertInstanceOf(InternableReflection::class, $memberAnnotation);
				$this->assertSame($internal, $memberAnnotation->isInternal());
			}
		}
	}

	public function testInternalUserFunctions(): void
	{
		require_once __DIR__ . '/data/annotations-internal.php';

		/** @var Broker $broker */
		$broker = self::getContainer()->getByType(Broker::class);

		$this->assertFalse($broker->getFunction(new Name\FullyQualified('InternalAnnotations\foo'), null)->isInternal());
		$this->assertTrue($broker->getFunction(new Name\FullyQualified('InternalAnnotations\internalFoo'), null)->isInternal());
	}

}
