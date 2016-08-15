<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
use SomeNodeScopeResolverNamespace\Foo;

class NodeScopeResolverTest extends \PHPStan\TestCase
{

	/** @var \PHPStan\Analyser\NodeScopeResolver */
	private $resolver;

	/** @var \PhpParser\PrettyPrinter\Standard */
	private $printer;

	protected function setUp()
	{
		$this->printer = new \PhpParser\PrettyPrinter\Standard();
		$this->resolver = new NodeScopeResolver(
			$this->createBroker(),
			$this->printer,
			new FileTypeMapper($this->getParser(), $this->createMock(\Nette\Caching\Cache::class)),
			true,
			true,
			false,
			[
				\EarlyTermination\Foo::class => [
					'doFoo',
				],
			]
		);
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
				$this->assertArrayHasKey('frame', $variables);
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

	public function dataParameterTypes(): array
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
		string $class = null,
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

	public function dataAnonymousFunctionParameterTypes(): array
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
				CallableType::class,
				false,
				null,
				'$callable',
			],
			[
				ObjectType::class,
				false,
				'TypesNamespaceTypehints\FooWithAnonymousFunction',
				'$self',
			],
		];
	}

	/**
	 * @dataProvider dataAnonymousFunctionParameterTypes
	 * @param string $typeClass
	 * @param boolean $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testAnonymousFunctionTypehints(
		string $typeClass,
		bool $nullable,
		string $class = null,
		string $expression
	)
	{
		$this->assertTypes(
			__DIR__ . '/data/typehints-anonymous-function.php',
			$typeClass,
			$nullable,
			$class,
			$expression
		);
	}

	public function dataVarAnnotations(): array
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
				'VarAnnotations\Lorem',
				'$loremObject',
			],
			[
				ObjectType::class,
				false,
				'AnotherNamespace\Bar',
				'$barObject',
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
				CallableType::class,
				false,
				null,
				'$callable',
			],
			[
				ObjectType::class,
				false,
				'VarAnnotations\Foo',
				'$self',
			],
			[
				FloatType::class,
				false,
				null,
				'$invalidInteger',
			],
		];
	}

	/**
	 * @dataProvider dataVarAnnotations
	 * @param string $typeClass
	 * @param boolean $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testVarAnnotations(
		string $typeClass,
		bool $nullable,
		string $class = null,
		string $expression
	)
	{
		$this->assertTypes(
			__DIR__ . '/data/var-annotations.php',
			$typeClass,
			$nullable,
			$class,
			$expression
		);
	}

	public function dataCasts(): array
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
		string $class = null,
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

	public function dataDeductedTypes(): array
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
				FloatType::class,
				false,
				null,
				'$floatAssignedByRef',
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
			[
				IntegerType::class,
				false,
				null,
				'\TypesNamespaceDeductedTypes\Foo::INTEGER_CONSTANT',
			],
			[
				IntegerType::class,
				false,
				null,
				'self::INTEGER_CONSTANT',
			],
			[
				FloatType::class,
				false,
				null,
				'self::FLOAT_CONSTANT',
			],
			[
				StringType::class,
				false,
				null,
				'self::STRING_CONSTANT',
			],
			[
				ArrayType::class,
				false,
				null,
				'self::ARRAY_CONSTANT',
			],
			[
				BooleanType::class,
				false,
				null,
				'self::BOOLEAN_CONSTANT',
			],
			[
				NullType::class,
				true,
				null,
				'self::NULL_CONSTANT',
			],
			[
				IntegerType::class,
				false,
				null,
				'$foo::INTEGER_CONSTANT',
			],
			[
				FloatType::class,
				false,
				null,
				'$foo::FLOAT_CONSTANT',
			],
			[
				StringType::class,
				false,
				null,
				'$foo::STRING_CONSTANT',
			],
			[
				ArrayType::class,
				false,
				null,
				'$foo::ARRAY_CONSTANT',
			],
			[
				BooleanType::class,
				false,
				null,
				'$foo::BOOLEAN_CONSTANT',
			],
			[
				NullType::class,
				true,
				null,
				'$foo::NULL_CONSTANT',
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
		string $class = null,
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

	public function dataProperties(): array
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
				StaticType::class,
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
			[
				MixedType::class,
				false,
				null,
				'$this->invalidTypeProperty',
			],
			[
				ResourceType::class,
				false,
				null,
				'$this->resource',
			],
			[
				MixedType::class,
				true,
				null,
				'$yetAnotherAnotherMixedParameter',
			],
			[
				MixedType::class,
				true,
				null,
				'$yetAnotherAnotherAnotherMixedParameter',
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
		string $class = null,
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

	public function dataBinaryOperations(): array
	{
		$typeCallback = function ($value) {
			$type = gettype($value);
			if ($type === 'integer') {
				return IntegerType::class;
			} elseif ($type === 'double') {
				return FloatType::class;
			} elseif ($type === 'boolean') {
				return BooleanType::class;
			} elseif ($type === 'string') {
				return StringType::class;
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
			// string
			[
				$typeCallback('a' . 'b'),
				false,
				null,
				"'a' . 'b'",
			],
			[
				$typeCallback(1 . 'b'),
				false,
				null,
				"1 . 'b'",
			],
			[
				$typeCallback(1.0 . 'b'),
				false,
				null,
				"1.0 . 'b'",
			],
			[
				$typeCallback(1.0 . 2.0),
				false,
				null,
				'1.0 . 2.0',
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
		string $class = null,
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

	public function dataTypeFromMethodPhpDocs(): array
	{
		return [
			[
				MixedType::class,
				true,
				null,
				'$mixedParameter',
			],
			[
				MixedType::class,
				false,
				null,
				'$alsoMixedParameter',
			],
			[
				MixedType::class,
				true,
				null,
				'$anotherMixedParameter',
			],
			[
				MixedType::class,
				true,
				null,
				'$yetAnotherMixedParameter',
			],
			[
				IntegerType::class,
				false,
				null,
				'$integerParameter',
			],
			[
				IntegerType::class,
				false,
				null,
				'$anotherIntegerParameter',
			],
			[
				ArrayType::class,
				false,
				null,
				'$arrayParameterOne',
			],
			[
				ArrayType::class,
				false,
				null,
				'$arrayParameterOther',
			],
			[
				ObjectType::class,
				false,
				'MethodPhpDocsNamespace\\Lorem',
				'$objectRelative',
			],
			[
				ObjectType::class,
				false,
				'SomeOtherNamespace\\Ipsum',
				'$objectFullyQualified',
			],
			[
				ObjectType::class,
				false,
				'SomeNamespace\\Amet',
				'$objectUsed',
			],
			[
				MixedType::class,
				true,
				null,
				'$nonexistentParameter',
			],
			[
				IntegerType::class,
				true,
				null,
				'$nullableInteger',
			],
			[
				ObjectType::class,
				true,
				'SomeNamespace\Amet',
				'$nullableObject',
			],
			[
				ObjectType::class,
				true,
				'SomeNamespace\Amet',
				'$anotherNullableObject',
			],
			[
				ObjectType::class,
				false,
				'MethodPhpDocsNamespace\\Foo',
				'$selfType',
			],
			[
				StaticType::class,
				false,
				null,
				'$staticType',
			],
			[
				NullType::class,
				true,
				null,
				'$nullType',
			],
			[
				ObjectType::class,
				false,
				'MethodPhpDocsNamespace\Foo',
				'$this->doFoo()',
			],
			[
				ObjectType::class,
				false,
				'MethodPhpDocsNamespace\Bar',
				'$barObject->doBar()',
			],
			[
				MixedType::class,
				false,
				null,
				'$conflictedObject',
			],
			[
				ObjectType::class,
				false,
				'MethodPhpDocsNamespace\Baz',
				'$moreSpecifiedObject',
			],
			[
				ObjectType::class,
				false,
				'MethodPhpDocsNamespace\Baz',
				'$moreSpecifiedObject->doFluent()',
			],
			[
				ObjectType::class,
				true,
				'MethodPhpDocsNamespace\Baz',
				'$moreSpecifiedObject->doFluentNullable()',
			],
			[
				ResourceType::class,
				false,
				null,
				'$resource',
			],
			[
				MixedType::class,
				true,
				null,
				'$yetAnotherAnotherMixedParameter',
			],
			[
				MixedType::class,
				true,
				null,
				'$yetAnotherAnotherAnotherMixedParameter',
			],
		];
	}

	/**
	 * @dataProvider dataTypeFromMethodPhpDocs
	 * @param string $typeClass
	 * @param boolean $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testTypeFromMethodPhpDocs(
		string $typeClass,
		bool $nullable,
		string $class = null,
		string $expression
	)
	{
		$this->assertTypes(
			__DIR__ . '/data/methodPhpDocs.php',
			$typeClass,
			$nullable,
			$class,
			$expression
		);
	}

	public function dataInstanceOf(): array
	{
		return [
			[
				ObjectType::class,
				false,
				'PhpParser\Node\Expr\ArrayDimFetch',
				'$foo',
			],
			[
				ObjectType::class,
				false,
				'PhpParser\Node\Stmt\Function_',
				'$bar',
			],
			[
				MixedType::class,
				true,
				null,
				'$baz',
			],
		];
	}

	/**
	 * @dataProvider dataInstanceOf
	 * @param string $typeClass
	 * @param boolean $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testInstanceOf(
		string $typeClass,
		bool $nullable,
		string $class = null,
		string $expression
	)
	{
		$this->assertTypes(
			__DIR__ . '/data/instanceof.php',
			$typeClass,
			$nullable,
			$class,
			$expression
		);
	}

	public function testNotSwitchInstanceof()
	{
		$this->assertTypes(
			__DIR__ . '/data/switch-instanceof-not.php',
			MixedType::class,
			true,
			null,
			'$foo'
		);
	}

	public function dataSwitchInstanceOf(): array
	{
		return [
			[
				MixedType::class,
				true,
				null,
				'$foo',
			],
			[
				ObjectType::class,
				false,
				'SwitchInstanceOf\Bar',
				'$bar',
			],
			[
				ObjectType::class,
				false,
				'SwitchInstanceOf\Baz',
				'$baz',
			],
		];
	}

	/**
	 * @dataProvider dataSwitchInstanceOf
	 * @param string $typeClass
	 * @param boolean $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testSwitchInstanceof(
		string $typeClass,
		bool $nullable,
		string $class = null,
		string $expression
	)
	{
		$this->assertTypes(
			__DIR__ . '/data/switch-instanceof.php',
			$typeClass,
			$nullable,
			$class,
			$expression
		);
	}

	public function dataDynamicMethodReturnTypeExtensions(): array
	{
		return [
			[
				MixedType::class,
				true,
				null,
				'$em->getByFoo($foo)',
			],
			[
				ObjectType::class,
				false,
				'DynamicMethodReturnTypesNamespace\Entity',
				'$em->getByPrimary()',
			],
			[
				ObjectType::class,
				false,
				'DynamicMethodReturnTypesNamespace\Entity',
				'$em->getByPrimary($foo)',
			],
			[
				ObjectType::class,
				false,
				'DynamicMethodReturnTypesNamespace\Foo',
				'$em->getByPrimary(DynamicMethodReturnTypesNamespace\Foo::class)',
			],
			[
				MixedType::class,
				true,
				null,
				'$iem->getByFoo($foo)',
			],
			[
				ObjectType::class,
				false,
				'DynamicMethodReturnTypesNamespace\Entity',
				'$iem->getByPrimary()',
			],
			[
				ObjectType::class,
				false,
				'DynamicMethodReturnTypesNamespace\Entity',
				'$iem->getByPrimary($foo)',
			],
			[
				ObjectType::class,
				false,
				'DynamicMethodReturnTypesNamespace\Foo',
				'$iem->getByPrimary(DynamicMethodReturnTypesNamespace\Foo::class)',
			],
		];
	}

	/**
	 * @dataProvider dataDynamicMethodReturnTypeExtensions
	 * @param string $typeClass
	 * @param bool $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testDynamicMethodReturnTypeExtensions(
		string $typeClass,
		bool $nullable,
		string $class = null,
		string $expression
	)
	{
		$this->assertTypes(
			__DIR__ . '/data/dynamic-method-return-types.php',
			$typeClass,
			$nullable,
			$class,
			$expression,
			[
				new class() implements DynamicMethodReturnTypeExtension {

			public static function getClass(): string
			{
				return \DynamicMethodReturnTypesNamespace\EntityManager::class;
			}

			public function isMethodSupported(MethodReflection $methodReflection): bool
			{
				return in_array($methodReflection->getName(), ['getByPrimary'], true);
			}

			public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): \PHPStan\Type\Type
			{
				$args = $methodCall->args;
				if (count($args) === 0) {
					return $methodReflection->getReturnType();
				}

				$arg = $args[0]->value;
				if (!($arg instanceof \PhpParser\Node\Expr\ClassConstFetch)) {
					return $methodReflection->getReturnType();
				}

				if (!($arg->class instanceof \PhpParser\Node\Name)) {
					return $methodReflection->getReturnType();
				}

				return new ObjectType((string) $arg->class, false);
			}
				},
			]
		);
	}

	public function dataOverwritingVariable(): array
	{
		return [
			[
				MixedType::class,
				true,
				null,
				'$var',
				New_::class,
			],
			[
				ObjectType::class,
				false,
				'OverwritingVariable\Bar',
				'$var',
				MethodCall::class,
			],
			[
				ObjectType::class,
				false,
				'OverwritingVariable\Foo',
				'$var',
				Exit_::class,
			],
		];
	}

	/**
	 * @dataProvider dataOverwritingVariable
	 * @param string $typeClass
	 * @param boolean $nullable
	 * @param string|null $class
	 * @param string $expression
	 * @param string $evaluatedPointExpressionType
	 */
	public function testOverwritingVariable(
		string $typeClass,
		bool $nullable,
		string $class = null,
		string $expression,
		string $evaluatedPointExpressionType
	)
	{
		$this->assertTypes(
			__DIR__ . '/data/overwritingVariable.php',
			$typeClass,
			$nullable,
			$class,
			$expression,
			[],
			$evaluatedPointExpressionType
		);
	}

	public function dataNegatedInstanceof(): array
	{
		return [
			[
				ObjectType::class,
				false,
				'NegatedInstanceOf\Foo',
				'$foo',
			],
			[
				ObjectType::class,
				false,
				'NegatedInstanceOf\Bar',
				'$bar',
			],
			[
				ObjectType::class,
				false,
				'NegatedInstanceOf\Lorem',
				'$lorem',
			],
			[
				MixedType::class,
				true,
				null,
				'$dolor',
			],
			[
				MixedType::class,
				true,
				null,
				'$sit',
			],
		];
	}

	/**
	 * @dataProvider dataNegatedInstanceof
	 * @param string $typeClass
	 * @param bool $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testNegatedInstanceof(
		string $typeClass,
		bool $nullable,
		string $class = null,
		string $expression
	)
	{
		$this->assertTypes(
			__DIR__ . '/data/negated-instanceof.php',
			$typeClass,
			$nullable,
			$class,
			$expression
		);
	}

	public function dataAnonymousFunction(): array
	{
		return [
			[
				StringType::class,
				false,
				null,
				'$str',
			],
			[
				IntegerType::class,
				false,
				null,
				'$integer',
			],
			[
				MixedType::class,
				true,
				null,
				'$bar',
			],
		];
	}

	/**
	 * @dataProvider dataAnonymousFunction
	 * @param string $typeClass
	 * @param boolean $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testAnonymousFunction(
		string $typeClass,
		bool $nullable,
		string $class = null,
		string $expression
	)
	{
		$this->assertTypes(
			__DIR__ . '/data/anonymous-function.php',
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
		string $class = null,
		string $expression,
		array $dynamicMethodReturnTypeExtensions = [],
		string $evaluatedPointExpressionType = Exit_::class
	)
	{
		$this->processFile($file, function (\PhpParser\Node $node, Scope $scope) use ($typeClass, $nullable, $class, $expression, $evaluatedPointExpressionType) {
			if ($node instanceof $evaluatedPointExpressionType) {
				$type = $scope->getType(
					$this->getParser()->parseString(sprintf('<?php %s;', $expression))[0]
				);
				$this->assertInstanceOf($typeClass, $type);
				$this->assertSame($nullable, $type->isNullable());
				$this->assertSame($class, $type->getClass());
			}
		}, $dynamicMethodReturnTypeExtensions);
	}

	private function processFile(string $file, \Closure $callback, array $dynamicMethodReturnTypeExtensions = [])
	{
		$this->resolver->processNodes(
			$this->getParser()->parseFile($file),
			new Scope(
				$this->createBroker($dynamicMethodReturnTypeExtensions),
				$this->printer,
				$file
			),
			$callback
		);
	}

	public function dataDeclareStrictTypes(): array
	{
		return [
			[
				__DIR__ . '/data/declareWeakTypes.php',
				false,
			],
			[
				__DIR__ . '/data/noDeclare.php',
				false,
			],
			[
				__DIR__ . '/data/declareStrictTypes.php',
				true,
			],
		];
	}

	/**
	 * @dataProvider dataDeclareStrictTypes
	 * @param string $file
	 * @param bool $result
	 */
	public function testDeclareStrictTypes(string $file, bool $result)
	{
		$this->processFile($file, function (\PhpParser\Node $node, Scope $scope) use ($result) {
			if ($node instanceof Exit_) {
				$this->assertSame($result, $scope->isDeclareStrictTypes());
			}
		});
	}

	public function testEarlyTermination()
	{
		$this->processFile(__DIR__ . '/data/early-termination.php', function (\PhpParser\Node $node, Scope $scope) {
			if ($node instanceof Exit_) {
				$this->assertTrue($scope->hasVariableType('something'));
				$this->assertTrue($scope->hasVariableType('var'));
			}
		});
	}

}
