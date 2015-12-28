<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node\Expr\Exit_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use SomeNodeScopeResolverNamespace\Foo;

class NodeScopeResolverTest extends \PHPStan\TestCase
{

	/** @var \PHPStan\Analyser\NodeScopeResolver */
	private $resolver;

	protected function setUp()
	{
		$this->resolver = new NodeScopeResolver($this->getBroker(), true, true, false);
	}

	public function testClassMethodScope()
	{
		$this->processFile(__DIR__ . '/data/class.php', function (\PhpParser\Node $node, Scope $scope) {
			if ($node instanceof Exit_) {
				$this->assertSame('SomeNodeScopeResolverNamespace', $scope->getNamespace());
				$this->assertSame(Foo::class, $scope->getClass());
				$this->assertSame('doFoo', $scope->getFunction());
				$this->assertSame(Foo::class, $scope->getVariableType('this')->getClass());
				$this->assertTrue($scope->hasVariableType('baz'));
				$this->assertTrue($scope->hasVariableType('lorem'));
				$this->assertFalse($scope->hasVariableType('ipsum'));
				$this->assertTrue($scope->hasVariableType('i'));
				$this->assertTrue($scope->hasVariableType('val'));
				$this->assertSame('SomeNodeScopeResolverNamespace\InvalidArgumentException', $scope->getVariableType('exception')->getClass());
				$this->assertTrue($scope->hasVariableType('staticVariable'));
			}
		});
	}

	public function testAssignInIf()
	{
		$this->processFile(__DIR__ . '/data/if.php', function (\PhpParser\Node $node, Scope $scope) {
			if ($node instanceof Exit_) {
				$variables = $scope->getVariableTypes();
				$this->assertArrayHasKey('foo', $variables);
				$this->assertArrayHasKey('lorem', $variables);
				$this->assertArrayHasKey('callParameter', $variables);
				$this->assertArrayHasKey('arrOne', $variables);
				$this->assertArrayHasKey('arrTwo', $variables);
				$this->assertArrayHasKey('arrTwo', $variables);
				$this->assertArrayHasKey('listedOne', $variables);
				$this->assertArrayHasKey('listedTwo', $variables);
				$this->assertArrayHasKey('listedThree', $variables);
				$this->assertArrayHasKey('listedFour', $variables);
				$this->assertArrayHasKey('inArray', $variables);
				$this->assertArrayHasKey('i', $variables);
				$this->assertArrayHasKey('f', $variables);
				$this->assertArrayHasKey('matches', $variables);
				$this->assertArrayHasKey('anotherArray', $variables);
				$this->assertArrayHasKey('ifVar', $variables);
				$this->assertArrayNotHasKey('ifNotVar', $variables);
				$this->assertArrayHasKey('ifNestedVar', $variables);
				$this->assertArrayNotHasKey('ifNotNestedVar', $variables);
				$this->assertArrayHasKey('matches2', $variables);
				$this->assertArrayHasKey('inTry', $variables);
				$this->assertArrayHasKey('matches3', $variables);
				$this->assertArrayNotHasKey('matches4', $variables);
				$this->assertArrayHasKey('issetBar', $variables);
				$this->assertArrayHasKey('doWhileVar', $variables);
				$this->assertArrayHasKey('switchVar', $variables);
				$this->assertArrayNotHasKey('noSwitchVar', $variables);
				$this->assertArrayHasKey('inTryTwo', $variables);
				$this->assertArrayHasKey('ternaryMatches', $variables);
				$this->assertArrayHasKey('previousI', $variables);
				$this->assertArrayHasKey('previousJ', $variables);
				$this->assertArrayHasKey('listOne', $variables);
				$this->assertArrayHasKey('listTwo', $variables);
				$this->assertArrayHasKey('e', $variables);
				$this->assertArrayHasKey('exception', $variables);
				$this->assertArrayNotHasKey('inTryNotInCatch', $variables);
				$this->assertArrayHasKey('fooObjectFromTryCatch', $variables);
				$this->assertInstanceOf(ObjectType::class, $variables['fooObjectFromTryCatch']);
				$this->assertSame('InTryCatchFoo', $variables['fooObjectFromTryCatch']->getClass());
				$this->assertArrayHasKey('mixedVarFromTryCatch', $variables);
				$this->assertInstanceOf(MixedType::class, $variables['mixedVarFromTryCatch']);
				$this->assertArrayHasKey('nullableIntegerFromTryCatch', $variables);
				$this->assertInstanceOf(IntegerType::class, $variables['nullableIntegerFromTryCatch']);
				$this->assertTrue($variables['nullableIntegerFromTryCatch']->isNullable());
				$this->assertArrayHasKey('anotherNullableIntegerFromTryCatch', $variables);
				$this->assertInstanceOf(IntegerType::class, $variables['anotherNullableIntegerFromTryCatch']);
				$this->assertTrue($variables['anotherNullableIntegerFromTryCatch']->isNullable());
			}
		});
	}

	public function dataParameterTypes()
	{
		return [
			[
				IntegerType::class,
				false,
				null,
				'$integer',
			],
			[
				BooleanType::class,
				false,
				null,
				'$boolean',
			],
			[
				StringType::class,
				false,
				null,
				'$string',
			],
			[
				FloatType::class,
				false,
				null,
				'$float',
			],
			[
				ObjectType::class,
				false,
				'TypesNamespaceTypehints\Lorem',
				'$loremObject',
			],
			[
				MixedType::class,
				true,
				null,
				'$mixed',
			],
			[
				ArrayType::class,
				false,
				null,
				'$array',
			],
			[
				BooleanType::class,
				true,
				null,
				'$isNullable',
			],
			[
				ObjectType::class,
				false,
				'TypesNamespaceTypehints\Lorem',
				'$loremObjectRef',
			],
			[
				ObjectType::class,
				false,
				'TypesNamespaceTypehints\Bar',
				'$barObject',
			],
			[
				ObjectType::class,
				false,
				'TypesNamespaceTypehints\Foo',
				'$fooObject',
			],
			[
				ObjectType::class,
				false,
				'TypesNamespaceTypehints\Bar',
				'$anotherBarObject',
			],
			[
				CallableType::class,
				false,
				null,
				'$callable',
			],
		];
	}

	/**
	 * @dataProvider dataParameterTypes
	 * @param string $typeClass
	 * @param boolean $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testTypehints(
		string $typeClass,
		bool $nullable,
		$class,
		string $expression
	)
	{
		$this->assertTypes(
			__DIR__ . '/data/typehints.php',
			$typeClass,
			$nullable,
			$class,
			$expression
		);
	}

	public function dataCasts()
	{
		return [
			[
				IntegerType::class,
				false,
				null,
				'$castedInteger',
			],
			[
				BooleanType::class,
				false,
				null,
				'$castedBoolean',
			],
			[
				FloatType::class,
				false,
				null,
				'$castedFloat',
			],
			[
				StringType::class,
				false,
				null,
				'$castedString',
			],
			[
				ArrayType::class,
				false,
				null,
				'$castedArray',
			],
			[
				ObjectType::class,
				false,
				'stdClass',
				'$castedObject',
			],
			[
				NullType::class,
				true,
				null,
				'$castedNull',
			],
		];
	}

	/**
	 * @dataProvider dataCasts
	 * @param string $typeClass
	 * @param boolean $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testCasts(
		string $typeClass,
		bool $nullable,
		$class,
		string $expression
	)
	{
		$this->assertTypes(
			__DIR__ . '/data/casts.php',
			$typeClass,
			$nullable,
			$class,
			$expression
		);
	}

	public function dataDeductedTypes()
	{
		return [
			[
				IntegerType::class,
				false,
				null,
				'$integerLiteral',
			],
			[
				BooleanType::class,
				false,
				null,
				'$booleanLiteral',
			],
			[
				BooleanType::class,
				false,
				null,
				'$anotherBooleanLiteral',
			],
			[
				StringType::class,
				false,
				null,
				'$stringLiteral',
			],
			[
				FloatType::class,
				false,
				null,
				'$floatLiteral',
			],
			[
				NullType::class,
				true,
				null,
				'$nullLiteral',
			],
			[
				ObjectType::class,
				false,
				'TypesNamespaceDeductedTypes\Lorem',
				'$loremObjectLiteral',
			],
			[
				MixedType::class,
				false,
				null,
				'$mixedObjectLiteral',
			],
			[
				MixedType::class,
				false,
				null,
				'$anotherMixedObjectLiteral',
			],
			[
				ArrayType::class,
				false,
				null,
				'$arrayLiteral',
			],
			[
				StringType::class,
				false,
				null,
				'$stringFromFunction',
			],
			[
				ObjectType::class,
				false,
				'TypesNamespaceFunctions\Foo',
				'$fooObjectFromFunction',
			],
			[
				MixedType::class,
				true,
				null,
				'$mixedFromFunction',
			],
		];
	}

	/**
	 * @dataProvider dataDeductedTypes
	 * @param string $typeClass
	 * @param boolean $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testDeductedTypes(
		string $typeClass,
		bool $nullable,
		$class,
		string $expression
	)
	{
		require_once __DIR__ . '/data/function-definitions.php';
		$this->assertTypes(
			__DIR__ . '/data/deducted-types.php',
			$typeClass,
			$nullable,
			$class,
			$expression
		);
	}

	public function dataProperties()
	{
		return [
			[
				MixedType::class,
				false,
				null,
				'$this->mixedProperty',
			],
			[
				MixedType::class,
				false,
				null,
				'$this->alsoMixedProperty',
			],
			[
				MixedType::class,
				false,
				null,
				'$this->anotherMixedProperty',
			],
			[
				MixedType::class,
				false,
				null,
				'$this->yetAnotherMixedProperty',
			],
			[
				IntegerType::class,
				false,
				null,
				'$this->integerProperty',
			],
			[
				IntegerType::class,
				false,
				null,
				'$this->anotherIntegerProperty',
			],
			[
				ArrayType::class,
				false,
				null,
				'$this->arrayPropertyOne',
			],
			[
				ArrayType::class,
				false,
				null,
				'$this->arrayPropertyOther',
			],
			[
				ObjectType::class,
				false,
				'PropertiesNamespace\\Lorem',
				'$this->objectRelative',
			],
			[
				ObjectType::class,
				false,
				'SomeOtherNamespace\\Ipsum',
				'$this->objectFullyQualified',
			],
			[
				ObjectType::class,
				false,
				'SomeNamespace\\Amet',
				'$this->objectUsed',
			],
			[
				MixedType::class,
				true,
				null,
				'$this->nonexistentProperty',
			],
			[
				IntegerType::class,
				true,
				null,
				'$this->nullableInteger',
			],
			[
				ObjectType::class,
				true,
				'SomeNamespace\Amet',
				'$this->nullableObject',
			],
			[
				ObjectType::class,
				false,
				'PropertiesNamespace\\Foo',
				'$this->selfType',
			],
			[
				MixedType::class,
				false,
				null,
				'$this->staticType',
			],
			[
				NullType::class,
				true,
				null,
				'$this->nullType',
			],
			[
				ObjectType::class,
				false,
				'SomeNamespace\Sit',
				'$this->inheritedProperty',
			],
			[
				ObjectType::class,
				false,
				'PropertiesNamespace\Bar',
				'$this->barObject->doBar()',
			],
		];
	}

	/**
	 * @dataProvider dataProperties
	 * @param string $typeClass
	 * @param boolean $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testProperties(
		string $typeClass,
		bool $nullable,
		$class,
		string $expression
	)
	{
		$this->assertTypes(
			__DIR__ . '/data/properties.php',
			$typeClass,
			$nullable,
			$class,
			$expression
		);
	}

	public function dataBinaryOperations()
	{
		$typeCallback = function ($value) {
			$type = gettype($value);
			if ($type === 'integer') {
				return IntegerType::class;
			} elseif ($type === 'double') {
				return FloatType::class;
			} elseif ($type === 'boolean') {
				return BooleanType::class;
			}

			return MixedType::class;
		};

		return [
			[
				$typeCallback('foo' && 'bar'),
				false,
				null,
				"'foo' && 'bar'",
			],
			[
				$typeCallback('foo' || 'bar'),
				false,
				null,
				"'foo' || 'bar'",
			],
			[
				$typeCallback('foo' xor 'bar'),
				false,
				null,
				"'foo' xor 'bar'",
			],
			[
				$typeCallback(!2),
				false,
				null,
				'!2',
			],
			[
				$typeCallback(-1),
				false,
				null,
				'-1',
			],
			[
				$typeCallback(+1),
				false,
				null,
				'+1',
			],
			// integer + integer
			[
				$typeCallback(1 + 1),
				false,
				null,
				'1 + 1',
			],
			[
				$typeCallback(1 - 1),
				false,
				null,
				'1 - 1',
			],
			[
				$typeCallback(1 / 2),
				false,
				null,
				'1 / 2',
			],
			[
				$typeCallback(1 * 1),
				false,
				null,
				'1 * 1',
			],
			[
				$typeCallback(1 ** 1),
				false,
				null,
				'1 ** 1',
			],
			[
				$typeCallback(1 % 1),
				false,
				null,
				'1 % 1',
			],
			[
				$typeCallback(1 / 2),
				false,
				null,
				'$integer /= 2',
			],
			[
				$typeCallback(1 * 1),
				false,
				null,
				'$integer *= 1',
			],
			// float + float
			[
				$typeCallback(1.2 + 1.4),
				false,
				null,
				'1.2 + 1.4',
			],
			[
				$typeCallback(1.2 - 1.4),
				false,
				null,
				'1.2 - 1.4',
			],
			[
				$typeCallback(1.2 / 2.4),
				false,
				null,
				'1.2 / 2.4',
			],
			[
				$typeCallback(1.2 * 1.4),
				false,
				null,
				'1.2 * 1.4',
			],
			[
				$typeCallback(1.2 ** 1.4),
				false,
				null,
				'1.2 ** 1.4',
			],
			[
				$typeCallback(3.2 % 2.4),
				false,
				null,
				'3.2 % 2.4',
			],
			[
				$typeCallback(1.2 / 2.4),
				false,
				null,
				'$float /= 2.4',
			],
			[
				$typeCallback(1.2 * 2.4),
				false,
				null,
				'$float *= 2.4',
			],
			// integer + float
			[
				$typeCallback(1 + 1.4),
				false,
				null,
				'1 + 1.4',
			],
			[
				$typeCallback(1 - 1.4),
				false,
				null,
				'1 - 1.4',
			],
			[
				$typeCallback(1 / 2.4),
				false,
				null,
				'1 / 2.4',
			],
			[
				$typeCallback(1 * 1.4),
				false,
				null,
				'1 * 1.4',
			],
			[
				$typeCallback(1 ** 1.4),
				false,
				null,
				'1 ** 1.4',
			],
			[
				$typeCallback(3 % 2.4),
				false,
				null,
				'3 % 2.4',
			],
			[
				$typeCallback(1 / 2.4),
				false,
				null,
				'$integer /= 2.4',
			],
			[
				$typeCallback(1 * 2.4),
				false,
				null,
				'$integer *= 2.4',
			],
			// float + integer
			[
				$typeCallback(1.2 + 1),
				false,
				null,
				'1.2 + 1',
			],
			[
				$typeCallback(1.2 - 1),
				false,
				null,
				'1.2 - 1',
			],
			[
				$typeCallback(1.2 / 2),
				false,
				null,
				'1.2 / 2',
			],
			[
				$typeCallback(1.2 * 1),
				false,
				null,
				'1.2 * 1',
			],
			[
				$typeCallback(1.2 ** 1),
				false,
				null,
				'1.2 ** 1',
			],
			[
				$typeCallback(3.2 % 2),
				false,
				null,
				'3.2 % 2',
			],
			[
				$typeCallback(1.2 / 2.4),
				false,
				null,
				'$float /= 2.4',
			],
			[
				$typeCallback(1.2 * 2),
				false,
				null,
				'$float *= 2',
			],
			// boolean
			[
				$typeCallback(true + false),
				false,
				null,
				'true + false',
			],
		];
	}

	/**
	 * @dataProvider dataBinaryOperations
	 * @param string $typeClass
	 * @param boolean $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testBinaryOperations(
		string $typeClass,
		bool $nullable,
		$class,
		string $expression
	)
	{
		$this->assertTypes(
			__DIR__ . '/data/binary.php',
			$typeClass,
			$nullable,
			$class,
			$expression
		);
	}

	private function assertTypes(
		string $file,
		string $typeClass,
		bool $nullable,
		$class,
		string $expression
	)
	{
		$this->processFile($file, function (\PhpParser\Node $node, Scope $scope) use ($typeClass, $nullable, $class, $expression) {
			if ($node instanceof Exit_) {
				$type = $scope->getType(
					$this->getParser()->parseString(sprintf('<?php %s;', $expression))[0]
				);
				$this->assertInstanceOf($typeClass, $type);
				$this->assertSame($nullable, $type->isNullable());
				$this->assertSame($class, $type->getClass());
			}
		});
	}

	private function processFile(string $file, \Closure $callback)
	{
		$this->resolver->processNodes(
			$this->getParser()->parseFile($file),
			new Scope($this->getBroker(), $file),
			$callback
		);
	}

}
