<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Annotations;

use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class ThrowsAnnotationsTest extends \PHPStan\Testing\TestCase
{

	public function dataThrowsAnnotations(): array
	{
		return [
			[
				\ThrowsAnnotations\Foo::class,
				[
					'withoutThrows' => [null, []],
					'throwsRuntime' => [
						\RuntimeException::class,
						[
							[\RuntimeException::class, 'Class instance method description 1.'],
							[\RuntimeException::class, 'Class instance method description 2.'],
						],
					],
					'staticThrowsRuntime' => [
						\RuntimeException::class,
						[
							[\RuntimeException::class, 'Class static method description.'],
						],
					],
				],
			],
			[
				\ThrowsAnnotations\FooInterface::class,
				[
					'withoutThrows' => [null, []],
					'throwsRuntime' => [
						\RuntimeException::class,
						[
							[\RuntimeException::class, 'Interface instance method description.'],
						],
					],
					'staticThrowsRuntime' => [
						\RuntimeException::class,
						[
							[\RuntimeException::class, 'Interface static method description 1.'],
							[\RuntimeException::class, 'Interface static method description 2.'],
						],
					],
				],
			],
			[
				\ThrowsAnnotations\FooTrait::class,
				[
					'withoutThrows' => [null, []],
					'throwsRuntime' => [
						\RuntimeException::class,
						[
							[\RuntimeException::class, 'Trait instance method description 1.'],
							[\RuntimeException::class, 'Trait instance method description 2.'],
						],
					],
					'staticThrowsRuntime' => [
						\RuntimeException::class,
						[
							[\RuntimeException::class, 'Trait static method description.'],
						],
					],
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

		foreach ($throwsAnnotations as $methodName => [$type, $descriptions]) {
			$methodAnnotation = $class->getMethod($methodName, $scope);
			// @todo Expect ThrowableReflection as soon as getThrowDescriptions() is added
			$this->assertInstanceOf(PhpMethodReflection::class, $methodAnnotation);
			$throwType = $methodAnnotation->getThrowType();
			$this->assertSame($type, $throwType !== null ? $throwType->describe(VerbosityLevel::typeOnly()) : null);

			$throwDescriptions = $methodAnnotation->getThrowDescriptions();

			$this->assertSameSize($descriptions, $throwDescriptions);

			foreach ($descriptions as $index => [$expectedThrowType, $expectedDescription]) {
				$this->assertArrayHasKey($index, $throwDescriptions);

				[$actualThrowType, $actualThrowDescription] = $throwDescriptions[$index];

				$this->assertInstanceOf(Type::class, $actualThrowType);

				$this->assertSame($expectedThrowType, $actualThrowType->describe(VerbosityLevel::typeOnly()));
				$this->assertSame($expectedDescription, $actualThrowDescription);
			}
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
