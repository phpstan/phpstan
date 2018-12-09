<?php declare(strict_types = 1);

namespace PHPStan\Dependency;

use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Broker\Broker;
use PHPStan\File\FileFinder;
use PHPStan\File\FileHelper;
use PHPStan\Parser\Parser;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Testing\TestCase;
use Tests\Dependency\Child;
use Tests\Dependency\GrandChild;
use Tests\Dependency\ParentClass;

class DependencyDumperTest extends TestCase
{

	public function testDumpDependencies(): void
	{
		$container = self::getContainer();

		/** @var NodeScopeResolver $nodeScopeResolver */
		$nodeScopeResolver = $container->getByType(NodeScopeResolver::class);

		/** @var Parser $realParser */
		$realParser = $container->getByType(Parser::class);

		$mockParser = $this->createMock(Parser::class);
		$mockParser->method('parseFile')
			->willReturnCallback(
				static function (string $file) use ($realParser): array {
					if (\file_exists($file)) {
						return $realParser->parseFile($file);
					}

					return [];
				}
			);

		/** @var Broker $realBroker */
		$realBroker = $container->getByType(Broker::class);

		$fileHelper = new FileHelper(__DIR__);

		$mockBroker = $this->createMock(Broker::class);
		$mockBroker->method('getClass')
			->willReturnCallback(
				function (string $class) use ($realBroker, $fileHelper): ClassReflection {
					if (\in_array(
						$class,
						[
							GrandChild::class,
							Child::class,
							ParentClass::class,
						],
						true
					)) {
						return $realBroker->getClass($class);
					}

					$nameParts = \explode('\\', $class);
					$shortClass = \array_pop($nameParts);

					$classReflection = $this->createMock(ClassReflection::class);
					$classReflection->method('getInterfaces')->willReturn([]);
					$classReflection->method('getTraits')->willReturn([]);
					$classReflection->method('getParentClass')->willReturn(false);
					$classReflection->method('getFilename')->willReturn(
						$fileHelper->normalizePath(__DIR__ . '/data/' . $shortClass . '.php')
					);

					return $classReflection;
				}
			);

		$expectedDependencyTree = $this->getExpectedDependencyTree($fileHelper);

		/** @var ScopeFactory $scopeFactory */
		$scopeFactory = $container->getByType(ScopeFactory::class);

		/** @var FileFinder $fileFinder */
		$fileFinder = $container->getByType(FileFinder::class);

		$dumper = new DependencyDumper(
			new DependencyResolver($mockBroker),
			$nodeScopeResolver,
			$fileHelper,
			$mockParser,
			$scopeFactory,
			$fileFinder
		);

		$dependencies = $dumper->dumpDependencies(
			\array_merge(
				[$fileHelper->normalizePath(__DIR__ . '/data/GrandChild.php')],
				\array_keys($expectedDependencyTree)
			),
			static function (): void {
			},
			static function (): void {
			},
			null
		);

		$this->assertCount(\count($expectedDependencyTree), $dependencies);
		foreach ($expectedDependencyTree as $file => $files) {
			$this->assertArrayHasKey($file, $dependencies);
			$this->assertSame($files, $dependencies[$file]);
		}
	}

	/**
	 * @param FileHelper $fileHelper
	 * @return string[][]
	 */
	private function getExpectedDependencyTree(FileHelper $fileHelper): array
	{
		$tree = [
			'Child.php' => [
				'GrandChild.php',
			],
			'Parent.php' => [
				'GrandChild.php',
				'Child.php',
			],
			'MethodNativeReturnTypehint.php' => [
				'GrandChild.php',
			],
			'MethodPhpDocReturnTypehint.php' => [
				'GrandChild.php',
			],
			'ParamNativeReturnTypehint.php' => [
				'GrandChild.php',
			],
			'ParamPhpDocReturnTypehint.php' => [
				'GrandChild.php',
			],
		];

		$expectedTree = [];
		foreach ($tree as $file => $files) {
			$expectedTree[$fileHelper->normalizePath(__DIR__ . '/data/' . $file)] = \array_map(
				static function (string $file) use ($fileHelper): string {
					return $fileHelper->normalizePath(__DIR__ . '/data/' . $file);
				},
				$files
			);
		}

		return $expectedTree;
	}

}
