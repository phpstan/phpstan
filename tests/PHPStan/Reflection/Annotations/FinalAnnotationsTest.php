<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Annotations;

use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\FinalizableReflection;

class FinalAnnotationsTest extends \PHPStan\Testing\TestCase
{

	public function dataFinalAnnotations(): array
	{
		return [
			[
				false,
				\FinalAnnotations\Foo::class,
				[
					'method' => [
						'foo',
						'staticFoo',
					],
				],
			],
			[
				true,
				\FinalAnnotations\FinalFoo::class,
				[
					'method' => [
						'finalFoo',
						'finalStaticFoo',
					],
				],
			],
		];
	}

	/**
	 * @dataProvider dataFinalAnnotations
	 * @param bool $final
	 * @param string $className
	 * @param array<string, mixed> $finalAnnotations
	 */
	public function testFinalAnnotations(bool $final, string $className, array $finalAnnotations): void
	{
		/** @var Broker $broker */
		$broker = self::getContainer()->getByType(Broker::class);
		$class = $broker->getClass($className);
		$scope = $this->createMock(Scope::class);
		$scope->method('isInClass')->willReturn(true);
		$scope->method('getClassReflection')->willReturn($class);
		$scope->method('canAccessProperty')->willReturn(true);

		$this->assertSame($final, $class->isFinal());

		foreach ($finalAnnotations as $memberType => $members) {
			foreach ($members as $memberName) {
				$memberAnnotation = $class->{'get' . ucfirst($memberType)}($memberName, $scope);
				$this->assertInstanceOf(FinalizableReflection::class, $memberAnnotation);
				$this->assertSame($final, $memberAnnotation->isFinal());
			}
		}
	}

	public function testFinalUserFunctions(): void
	{
		require_once __DIR__ . '/data/annotations-final.php';

		/** @var Broker $broker */
		$broker = self::getContainer()->getByType(Broker::class);

		$this->assertFalse($broker->getFunction(new Name\FullyQualified('FinalAnnotations\foo'), null)->isFinal());
		$this->assertTrue($broker->getFunction(new Name\FullyQualified('FinalAnnotations\finalFoo'), null)->isFinal());
	}

}
