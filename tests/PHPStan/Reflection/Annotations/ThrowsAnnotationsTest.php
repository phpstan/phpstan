<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Annotations;

use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ThrowableReflection;
use PHPStan\Type\VerbosityLevel;

class ThrowsAnnotationsTest extends \PHPStan\Testing\TestCase
{

	public function dataThrowsAnnotations(): array
	{
		return [
			[
				\ThrowsAnnotations\Foo::class,
				[
					'withoutThrows' => null,
					'throwsRuntime' => \RuntimeException::class,
					'staticThrowsRuntime' => \RuntimeException::class,

				],
			],
			[
				\ThrowsAnnotations\FooInterface::class,
				[
					'withoutThrows' => null,
					'throwsRuntime' => \RuntimeException::class,
					'staticThrowsRuntime' => \RuntimeException::class,

				],
			],
			[
				\ThrowsAnnotations\FooTrait::class,
				[
					'withoutThrows' => null,
					'throwsRuntime' => \RuntimeException::class,
					'staticThrowsRuntime' => \RuntimeException::class,

				],
			],
		];
	}

	/**
	 * @dataProvider dataThrowsAnnotations
	 * @param string $className
	 * @param array<string, mixed> $throwsAnnotations
	 */
	public function testThrowsAnnotations(string $className, array $throwsAnnotations): void
	{
		/** @var Broker $broker */
		$broker = self::getContainer()->getByType(Broker::class);
		$class = $broker->getClass($className);
		$scope = $this->createMock(Scope::class);

		foreach ($throwsAnnotations as $methodName => $type) {
			$methodAnnotation = $class->getMethod($methodName, $scope);
			$this->assertInstanceOf(ThrowableReflection::class, $methodAnnotation);
			$throwType = $methodAnnotation->getThrowType();
			$this->assertSame($type, $throwType !== null ? $throwType->describe(VerbosityLevel::typeOnly()) : null);
		}
	}

	public function testThrowsOnUserFunctions(): void
	{
		require_once __DIR__ . '/data/annotations-throws.php';

		/** @var Broker $broker */
		$broker = self::getContainer()->getByType(Broker::class);

		$this->assertNull($broker->getFunction(new Name\FullyQualified('ThrowsAnnotations\withoutThrows'), null)->getThrowType());

		$throwType = $broker->getFunction(new Name\FullyQualified('ThrowsAnnotations\throwsRuntime'), null)->getThrowType();
		$this->assertNotNull($throwType);
		$this->assertSame(\RuntimeException::class, $throwType->describe(VerbosityLevel::typeOnly()));
	}

	public function testThrowsOnNativeFunctions(): void
	{
		/** @var Broker $broker */
		$broker = self::getContainer()->getByType(Broker::class);

		$this->assertNull($broker->getFunction(new Name('str_replace'), null)->getThrowType());
		$this->assertNull($broker->getFunction(new Name('get_class'), null)->getThrowType());
		$this->assertNull($broker->getFunction(new Name('function_exists'), null)->getThrowType());
	}

}
