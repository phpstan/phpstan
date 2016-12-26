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
use PHPStan\Type\IterableIterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionIterableType;
use PHPStan\Type\VoidType;
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
			$this->getParser(),
			$this->printer,
			new FileTypeMapper($this->getParser(), $this->createMock(\Nette\Caching\Cache::class)),
			new TypeSpecifier($this->printer),
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
				$this->assertSame('doFoo', $scope->getFunctionName());
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

				$this->assertInstanceOf(ArrayType::class, $variables['nullableIntegers']);

				/** @var $nullableIntegers \PHPStan\Type\ArrayType */
				$nullableIntegers = $variables['nullableIntegers'];
				$this->assertInstanceOf(IntegerType::class, $nullableIntegers->getItemType());
				$this->assertTrue($nullableIntegers->getItemType()->isNullable());

				$this->assertInstanceOf(ArrayType::class, $variables['mixeds']);

				/** @var $mixeds \PHPStan\Type\ArrayType */
				$mixeds = $variables['mixeds'];
				$this->assertInstanceOf(MixedType::class, $mixeds->getItemType());
				$this->assertFalse($mixeds->getItemType()->isNullable());
			}
		});
	}

	/**
	 * @requires PHP 7.1
	 */
	public function testArrayDestructuringShortSyntax()
	{
		if (self::isObsoletePhpParserVersion()) {
			$this->markTestSkipped('Test requires PHP-Parser ^3.0.0');
		}
		$this->processFile(__DIR__ . '/data/array-destructuring-short.php', function (\PhpParser\Node $node, Scope $scope) {
			if ($node instanceof Exit_) {
				$variables = $scope->getVariableTypes();
				$this->assertArrayHasKey('a', $variables);
				$this->assertArrayHasKey('b', $variables);
				$this->assertArrayHasKey('c', $variables);
				$this->assertArrayHasKey('d', $variables);
				$this->assertArrayHasKey('e', $variables);
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
			[
				ArrayType::class,
				false,
				null,
				'$variadicStrings',
			],
			[
				StringType::class,
				false,
				null,
				'$variadicStrings[0]',
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

	/**
	 * @dataProvider dataVarAnnotations
	 * @param string $typeClass
	 * @param boolean $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testVarAnnotationsAlt(
		string $typeClass,
		bool $nullable,
		string $class = null,
		string $expression
	)
	{
		if (\Nette\Utils\Strings::startsWith($class, 'VarAnnotations\\')) {
			$class = 'VarAnnotationsAlt\\' . substr($class, strlen('VarAnnotations\\'));
		}
		$this->assertTypes(
			__DIR__ . '/data/var-annotations-alt.php',
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
			[
				MixedType::class,
				true,
				null,
				'$yetAnotherAnotherAnotherAnotherMixedParameter',
			],
			[
				StringType::class,
				false,
				null,
				'self::$staticStringProperty',
			],
			[
				ObjectType::class,
				false,
				'SomeGroupNamespace\One',
				'$this->groupUseProperty',
			],
			[
				ObjectType::class,
				false,
				'SomeGroupNamespace\Two',
				'$this->anotherGroupUseProperty',
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
			[
				$typeCallback('foo' <=> 'bar'),
				false,
				null,
				"'foo' <=> 'bar'",
			],
			[
				MixedType::class,
				false,
				null,
				'1 + doFoo()',
			],
			[
				MixedType::class,
				false,
				null,
				'1 / doFoo()',
			],
			[
				MixedType::class,
				false,
				null,
				'1.0 / doFoo()',
			],
			[
				MixedType::class,
				false,
				null,
				'doFoo() / 1',
			],
			[
				MixedType::class,
				false,
				null,
				'doFoo() / 1.0',
			],
			[
				MixedType::class,
				false,
				null,
				'1.0 + doFoo()',
			],
			[
				MixedType::class,
				false,
				null,
				'1.0 + doFoo()',
			],
			[
				MixedType::class,
				false,
				null,
				'doFoo() + 1',
			],
			[
				MixedType::class,
				false,
				null,
				'doFoo() + 1.0',
			],
			[
				StringType::class,
				true,
				null,
				"doFoo() ? 'foo' : null",
			],
			[
				IntegerType::class,
				true,
				null,
				'12 ?: null',
			],
			[
				StringType::class,
				true,
				null,
				"'foo' ?? null",
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

	public function dataLiteralArrays(): array
	{
		return [
			[
				IntegerType::class,
				false,
				null,
				'$integers[0]',
			],
			[
				StringType::class,
				false,
				null,
				'$strings[0]',
			],
			[
				MixedType::class,
				true,
				null,
				'$emptyArray[0]',
			],
			[
				MixedType::class,
				false,
				null,
				'$mixedArray[0]',
			],
		];
	}

	/**
	 * @dataProvider dataLiteralArrays
	 * @param string $typeClass
	 * @param boolean $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testLiteralArrays(
		string $typeClass,
		bool $nullable,
		string $class = null,
		string $expression
	)
	{
		$this->assertTypes(
			__DIR__ . '/data/literal-arrays.php',
			$typeClass,
			$nullable,
			$class,
			$expression
		);
	}

	public function dataTypeFromFunctionPhpDocs(): array
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
				NullType::class,
				true,
				null,
				'$nullType',
			],
			[
				ObjectType::class,
				false,
				'MethodPhpDocsNamespace\Bar',
				'$barObject->doBar()',
			],
			[
				ObjectType::class,
				false,
				'MethodPhpDocsNamespace\Bar',
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
			[
				VoidType::class,
				false,
				null,
				'$voidParameter',
			],
			[
				ObjectType::class,
				false,
				'SomeNamespace\Consecteur',
				'$useWithoutAlias',
			],

		];
	}

	public function dataTypeFromFunctionFunctionPhpDocs(): array
	{
		return [
			[
				ObjectType::class,
				false,
				'MethodPhpDocsNamespace\Foo',
				'$fooFunctionResult',
			],
			[
				ObjectType::class,
				false,
				'MethodPhpDocsNamespace\Bar',
				'$barFunctionResult',
			],
		];
	}

	/**
	 * @dataProvider dataTypeFromFunctionPhpDocs
	 * @dataProvider dataTypeFromFunctionFunctionPhpDocs
	 * @param string $typeClass
	 * @param boolean $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testTypeFromFunctionPhpDocs(
		string $typeClass,
		bool $nullable,
		string $class = null,
		string $expression
	)
	{
		require_once __DIR__ . '/data/functionPhpDocs.php';
		$this->assertTypes(
			__DIR__ . '/data/functionPhpDocs.php',
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
				ObjectType::class,
				false,
				'MethodPhpDocsNamespace\Foo',
				'$this->doFoo()',
			],
			[
				ObjectType::class,
				false,
				'MethodPhpDocsNamespace\Bar',
				'static::doSomethingStatic()',
			],
			[
				StaticType::class,
				false,
				null,
				'parent::doLorem()',
			],
			[
				ObjectType::class,
				false,
				'MethodPhpDocsNamespace\FooParent',
				'$parent->doLorem()',
			],
			[
				ObjectType::class,
				false,
				'MethodPhpDocsNamespace\Foo',
				'$this->doLorem()',
			],
			[
				StaticType::class,
				false,
				null,
				'parent::doIpsum()',
			],
			[
				ObjectType::class,
				false,
				'MethodPhpDocsNamespace\FooParent',
				'$parent->doIpsum()',
			],
			[
				ObjectType::class,
				false,
				'MethodPhpDocsNamespace\Foo',
				'$this->doIpsum()',
			],
			[
				ObjectType::class,
				false,
				'MethodPhpDocsNamespace\Foo',
				'$this->doBar()[0]',
			],
			[
				ObjectType::class,
				false,
				'MethodPhpDocsNamespace\Bar',
				'self::doSomethingStatic()',
			],
			[
				ObjectType::class,
				false,
				'MethodPhpDocsNamespace\Bar',
				'\MethodPhpDocsNamespace\Foo::doSomethingStatic()',
			],
		];
	}

	/**
	 * @dataProvider dataTypeFromFunctionPhpDocs
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
			[
				ObjectType::class,
				false,
				'InstanceOfNamespace\Lorem',
				'$lorem',
			],
			[
				ObjectType::class,
				false,
				'InstanceOfNamespace\Dolor',
				'$dolor',
			],
			[
				ObjectType::class,
				false,
				'InstanceOfNamespace\Sit',
				'$sit',
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
				MixedType::class,
				true,
				null,
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
			[
				MixedType::class,
				true,
				null,
				'$mixedFoo',
			],
			[
				MixedType::class,
				true,
				null,
				'$mixedBar',
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

	public function dataForeachArrayType(): array
	{
		return [
			[
				__DIR__ . '/data/foreach/array-object-type.php',
				ObjectType::class,
				false,
				'AnotherNamespace\Foo',
				'$foo',
			],
			[
				__DIR__ . '/data/foreach/array-object-type.php',
				ObjectType::class,
				false,
				'AnotherNamespace\Foo',
				'$foos[0]',
			],
			[
				__DIR__ . '/data/foreach/array-object-type.php',
				IntegerType::class,
				false,
				null,
				'self::ARRAY_CONSTANT[0]',
			],
			[
				__DIR__ . '/data/foreach/array-object-type.php',
				MixedType::class,
				false,
				null,
				'self::MIXED_CONSTANT[0]',
			],
			[
				__DIR__ . '/data/foreach/nested-object-type.php',
				ObjectType::class,
				false,
				'AnotherNamespace\Foo',
				'$foo',
			],
			[
				__DIR__ . '/data/foreach/nested-object-type.php',
				ObjectType::class,
				false,
				'AnotherNamespace\Foo',
				'$foos[0]',
			],
			[
				__DIR__ . '/data/foreach/nested-object-type.php',
				ObjectType::class,
				false,
				'AnotherNamespace\Foo',
				'$fooses[0][0]',
			],
			[
				__DIR__ . '/data/foreach/integer-type.php',
				IntegerType::class,
				false,
				null,
				'$integer',
			],
		];
	}

	/**
	 * @dataProvider dataForeachArrayType
	 * @param string $file
	 * @param string $typeClass
	 * @param bool $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testForeachArrayType(
		string $file,
		string $typeClass,
		bool $nullable,
		string $class = null,
		string $expression
	)
	{
		$this->assertTypes(
			$file,
			$typeClass,
			$nullable,
			$class,
			$expression
		);
	}

	public function dataArrayFunctions(): array
	{
		return [
			[
				IntegerType::class,
				false,
				null,
				'$integers[0]',
			],
			[
				StringType::class,
				false,
				null,
				'$mappedStrings[0]',
			],
			[
				IntegerType::class,
				false,
				null,
				'$filteredIntegers[0]',
			],
			[
				IntegerType::class,
				false,
				null,
				'$uniquedIntegers[0]',
			],
			[
				StringType::class,
				false,
				null,
				'$reducedIntegersToString',
			],
			[
				IntegerType::class,
				false,
				null,
				'$reversedIntegers[0]',
			],
		];
	}

	/**
	 * @dataProvider dataArrayFunctions
	 * @param string $typeClass
	 * @param bool $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testArrayFunctions(
		string $typeClass,
		bool $nullable,
		string $class = null,
		string $expression
	)
	{
		$this->assertTypes(
			__DIR__ . '/data/array-functions.php',
			$typeClass,
			$nullable,
			$class,
			$expression
		);
	}

	public function dataSpecifiedTypesUsingIsFunctions(): array
	{
		return [
			[
				IntegerType::class,
				false,
				null,
				'$integer',
			],
			[
				IntegerType::class,
				false,
				null,
				'$anotherInteger',
			],
			[
				IntegerType::class,
				false,
				null,
				'$longInteger',
			],
			[
				FloatType::class,
				false,
				null,
				'$float',
			],
			[
				FloatType::class,
				false,
				null,
				'$doubleFloat',
			],
			[
				FloatType::class,
				false,
				null,
				'$realFloat',
			],
			[
				NullType::class,
				true,
				null,
				'$null',
			],
			[
				ArrayType::class,
				false,
				null,
				'$array',
			],
			[
				BooleanType::class,
				false,
				null,
				'$bool',
			],
			[
				CallableType::class,
				false,
				null,
				'$callable',
			],
			[
				ResourceType::class,
				false,
				null,
				'$resource',
			],
			[
				IntegerType::class,
				false,
				null,
				'$yetAnotherInteger',
			],
			[
				MixedType::class,
				true,
				null,
				'$mixedInteger',
			],
		];
	}

	/**
	 * @dataProvider dataSpecifiedTypesUsingIsFunctions
	 * @param string $typeClass
	 * @param bool $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testSpecifiedTypesUsingIsFunctions(
		string $typeClass,
		bool $nullable,
		string $class = null,
		string $expression
	)
	{
		$this->assertTypes(
			__DIR__ . '/data/specifiedTypesUsingIsFunctions.php',
			$typeClass,
			$nullable,
			$class,
			$expression
		);
	}

	public function dataIterable(): array
	{
		return [
			[
				IterableIterableType::class,
				false,
				null,
				'$this->iterableProperty',
			],
			[
				IterableIterableType::class,
				false,
				null,
				'$iterableSpecifiedLater',
			],
			[
				IterableIterableType::class,
				false,
				null,
				'$iterableWithoutTypehint',
			],
			[
				MixedType::class,
				false,
				null,
				'$iterableWithoutTypehint[0]',
			],
			[
				IterableIterableType::class,
				false,
				null,
				'$iterableWithIterableTypehint',
			],
			[
				MixedType::class,
				false,
				null,
				'$iterableWithIterableTypehint[0]',
			],
			[
				MixedType::class,
				true,
				null,
				'$mixed',
			],
			[
				IterableIterableType::class,
				false,
				null,
				'$iterableWithConcreteTypehint',
			],
			[
				MixedType::class,
				false,
				null,
				'$iterableWithConcreteTypehint[0]',
			],
			[
				ObjectType::class,
				false,
				'Iterables\Bar',
				'$bar',
			],
			[
				IterableIterableType::class,
				false,
				null,
				'$this->doBar()',
			],
			[
				IterableIterableType::class,
				false,
				null,
				'$this->doBaz()',
			],
			[
				ObjectType::class,
				false,
				'Iterables\Baz',
				'$baz',
			],
			[
				ArrayType::class,
				false,
				null,
				'$arrayWithIterableTypehint',
			],
			[
				MixedType::class,
				true,
				null,
				'$arrayWithIterableTypehint[0]',
			],
			[
				UnionIterableType::class,
				false,
				'Iterables\Collection',
				'$unionIterableType',
			],
			[
				ObjectType::class,
				false,
				'Iterables\Bar',
				'$unionBar',
			],
			[
				MixedType::class,
				false,
				null,
				'$mixedUnionIterableType',
			],
			[
				MixedType::class,
				true,
				null,
				'$mixedBar',
			],
			[
				ObjectType::class,
				false,
				'Iterables\Bar',
				'$iterableUnionBar',
			],
			[
				ObjectType::class,
				false,
				'Iterables\Bar',
				'$unionBarFromMethod',
			],
		];
	}

	/**
	 * @requires PHP 7.1
	 * @dataProvider dataIterable
	 * @param string $typeClass
	 * @param bool $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testIterable(
		string $typeClass,
		bool $nullable,
		string $class = null,
		string $expression
	)
	{
		if (self::isObsoletePhpParserVersion()) {
			$this->markTestSkipped('Test requires PHP-Parser ^3.0.0');
		}
		$this->assertTypes(
			__DIR__ . '/data/iterable.php',
			$typeClass,
			$nullable,
			$class,
			$expression
		);
	}

	public function dataVoid(): array
	{
		return [
			[
				VoidType::class,
				false,
				null,
				'$this->doFoo()',
			],
			[
				VoidType::class,
				false,
				null,
				'$this->doBar()',
			],
			[
				VoidType::class,
				false,
				null,
				'$this->doConflictingVoid()',
			],
		];
	}

	/**
	 * @requires PHP 7.1
	 * @dataProvider dataVoid
	 * @param string $typeClass
	 * @param bool $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testVoid(
		string $typeClass,
		bool $nullable,
		string $class = null,
		string $expression
	)
	{
		if (self::isObsoletePhpParserVersion()) {
			$this->markTestSkipped('Test requires PHP-Parser ^3.0.0');
		}
		$this->assertTypes(
			__DIR__ . '/data/void.php',
			$typeClass,
			$nullable,
			$class,
			$expression
		);
	}

	public function dataNullableReturnTypes(): array
	{
		return [
			[
				IntegerType::class,
				true,
				null,
				'$this->doFoo()',
			],
			[
				IntegerType::class,
				true,
				null,
				'$this->doBar()',
			],
			[
				IntegerType::class,
				true,
				null,
				'$this->doConflictingNullable()',
			],
			[
				IntegerType::class,
				false,
				null,
				'$this->doAnotherConflictingNullable()',
			],
		];
	}

	/**
	 * @requires PHP 7.1
	 * @dataProvider dataNullableReturnTypes
	 * @param string $typeClass
	 * @param bool $nullable
	 * @param string|null $class
	 * @param string $expression
	 */
	public function testNullableReturnTypes(
		string $typeClass,
		bool $nullable,
		string $class = null,
		string $expression
	)
	{
		if (self::isObsoletePhpParserVersion()) {
			$this->markTestSkipped('Test requires PHP-Parser ^3.0.0');
		}
		$this->assertTypes(
			__DIR__ . '/data/nullable-returnTypes.php',
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
