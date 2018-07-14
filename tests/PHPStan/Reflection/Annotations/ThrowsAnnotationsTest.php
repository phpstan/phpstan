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
			self::assertInstanceOf(ThrowableReflection::class, $methodAnnotation);
			$throwType = $methodAnnotation->getThrowType();
			self::assertSame($type, $throwType !== null ? $throwType->describe(VerbosityLevel::typeOnly()) : null);
		}
	}

	public function testThrowsOnUserFunctions(): void
	{
		require_once __DIR__ . '/data/annotations-throws.php';

		/** @var Broker $broker */
		$broker = self::getContainer()->getByType(Broker::class);

		self::assertNull($broker->getFunction(new Name('\ThrowsAnnotations\withoutThrows'), null)->getThrowType());

		$throwType = $broker->getFunction(new Name('\ThrowsAnnotations\throwsRuntime'), null)->getThrowType();
		self::assertNotNull($throwType);
		self::assertSame(\RuntimeException::class, $throwType->describe(VerbosityLevel::typeOnly()));
	}

	public function testThrowsOnNativeFunctions(): void
	{
		/** @var Broker $broker */
		$broker = self::getContainer()->getByType(Broker::class);

		self::assertNull($broker->getFunction(new Name('str_replace'), null)->getThrowType());
		self::assertNull($broker->getFunction(new Name('get_class'), null)->getThrowType());
		self::assertNull($broker->getFunction(new Name('function_exists'), null)->getThrowType());
	}

}
