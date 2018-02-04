<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Cache\Cache;
use PHPStan\File\FileHelper;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\Reflection\MethodReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use SomeNodeScopeResolverNamespace\Foo;

class NodeScopeResolverTest extends \PHPStan\Testing\TestCase
{

	public function testClassMethodScope(): void
	{
		$this->processFile(__DIR__ . '/data/class.php', function (\PhpParser\Node $node, Scope $scope): void {
			if ($node instanceof Exit_) {
				$this->assertSame('SomeNodeScopeResolverNamespace', $scope->getNamespace());
				$this->assertSame(Foo::class, $scope->getClassReflection()->getName());
				$this->assertSame('doFoo', $scope->getFunctionName());
				$this->assertSame('$this(SomeNodeScopeResolverNamespace\Foo)', $scope->getVariableType('this')->describe());
				$this->assertTrue($scope->hasVariableType('baz')->yes());
				$this->assertTrue($scope->hasVariableType('lorem')->yes());
				$this->assertFalse($scope->hasVariableType('ipsum')->yes());
				$this->assertTrue($scope->hasVariableType('i')->yes());
				$this->assertTrue($scope->hasVariableType('val')->yes());
				$this->assertSame('SomeNodeScopeResolverNamespace\InvalidArgumentException', $scope->getVariableType('exception')->describe());
				$this->assertTrue($scope->hasVariableType('staticVariable')->yes());
			}
		});
	}

	public function dataAssignInIf(): array
	{
		/** @var \PHPStan\Analyser\Scope $testScope */
		$testScope = null;
		$this->processFile(__DIR__ . '/data/if.php', function (\PhpParser\Node $node, Scope $scope) use (&$testScope): void {
			if ($node instanceof Exit_) {
				$testScope = $scope;
			}
		});

		return [
			[
				$testScope,
				'nonexistentVariable',
				TrinaryLogic::createNo(),
			],
			[
				$testScope,
				'foo',
				TrinaryLogic::createYes(),
				'bool', // mixed?
			],
			[
				$testScope,
				'lorem',
				TrinaryLogic::createYes(),
				'int(1)',
			],
			[
				$testScope,
				'callParameter',
				TrinaryLogic::createYes(),
				'int(3)',
			],
			[
				$testScope,
				'arrOne',
				TrinaryLogic::createYes(),
				'array<int(0), string>',
			],
			[
				$testScope,
				'arrTwo',
				TrinaryLogic::createYes(),
				'array<int(0)|string, Foo|string>',
			],
			[
				$testScope,
				'arrThree',
				TrinaryLogic::createYes(),
				'array<int(0), string>',
			],
			[
				$testScope,
				'listedOne',
				TrinaryLogic::createYes(),
				'mixed',
			],
			[
				$testScope,
				'listedTwo',
				TrinaryLogic::createYes(),
				'mixed',
			],
			[
				$testScope,
				'listedThree',
				TrinaryLogic::createYes(),
				'mixed',
			],
			[
				$testScope,
				'listedFour',
				TrinaryLogic::createYes(),
				'mixed',
			],
			[
				$testScope,
				'inArray',
				TrinaryLogic::createYes(),
				'int(1)',
			],
			[
				$testScope,
				'i',
				TrinaryLogic::createYes(),
				'int(0)',
			],
			[
				$testScope,
				'f',
				TrinaryLogic::createMaybe(),
				'int(0)',
			],
			[
				$testScope,
				'anotherF',
				TrinaryLogic::createYes(),
				'int(0)|int(1)',
			],
			[
				$testScope,
				'matches',
				TrinaryLogic::createYes(),
				'mixed', // string[]
			],
			[
				$testScope,
				'anotherArray',
				TrinaryLogic::createYes(),
				'array<string, array<int(0), string>>',
			],
			[
				$testScope,
				'ifVar',
				TrinaryLogic::createYes(),
				'int(1)|int(2)|int(3)',
			],
			[
				$testScope,
				'ifNotVar',
				TrinaryLogic::createMaybe(),
				'int(1)|int(2)',
			],
			[
				$testScope,
				'ifNestedVar',
				TrinaryLogic::createYes(),
				'mixed',
			],
			[
				$testScope,
				'ifNotNestedVar',
				TrinaryLogic::createMaybe(),
				'int(1)|int(2)|int(3)',
			],
			[
				$testScope,
				'variableOnlyInEarlyTerminatingElse',
				TrinaryLogic::createNo(),
			],
			[
				$testScope,
				'matches2',
				TrinaryLogic::createYes(),
				'mixed', // string[]
			],
			[
				$testScope,
				'inTry',
				TrinaryLogic::createYes(),
				'int(1)',
			],
			[
				$testScope,
				'matches3',
				TrinaryLogic::createYes(),
				'mixed', // string[]
			],
			[
				$testScope,
				'matches4',
				TrinaryLogic::createMaybe(),
				'mixed', // string[]
			],
			[
				$testScope,
				'issetFoo',
				TrinaryLogic::createYes(),
				'Foo',
			],
			[
				$testScope,
				'issetBar',
				TrinaryLogic::createYes(),
				'mixed',
			],
			[
				$testScope,
				'issetBaz',
				TrinaryLogic::createYes(),
				'mixed',
			],
			[
				$testScope,
				'doWhileVar',
				TrinaryLogic::createYes(),
				'int(1)',
			],
			[
				$testScope,
				'switchVar',
				TrinaryLogic::createYes(),
				'int(1)|int(2)|int(3)',
			],
			[
				$testScope,
				'noSwitchVar',
				TrinaryLogic::createMaybe(),
				'int(1)',
			],
			[
				$testScope,
				'anotherNoSwitchVar',
				TrinaryLogic::createMaybe(),
				'int(1)',
			],
			[
				$testScope,
				'inTryTwo',
				TrinaryLogic::createYes(),
				'int(1)',
			],
			[
				$testScope,
				'ternaryMatches',
				TrinaryLogic::createYes(),
				'mixed', // string[]
			],
			[
				$testScope,
				'previousI',
				TrinaryLogic::createYes(),
				'int(0)',
			],
			[
				$testScope,
				'previousJ',
				TrinaryLogic::createYes(),
				'int(0)',
			],
			[
				$testScope,
				'frame',
				TrinaryLogic::createYes(),
				'mixed',
			],
			[
				$testScope,
				'listOne',
				TrinaryLogic::createYes(),
				'mixed', // int
			],
			[
				$testScope,
				'listTwo',
				TrinaryLogic::createYes(),
				'mixed', // int
			],
			[
				$testScope,
				'e',
				TrinaryLogic::createYes(),
				'Exception',
			],
			[
				$testScope,
				'exception',
				TrinaryLogic::createYes(),
				'Exception',
			],
			[
				$testScope,
				'inTryNotInCatch',
				TrinaryLogic::createMaybe(),
				'int(1)',
			],
			[
				$testScope,
				'fooObjectFromTryCatch',
				TrinaryLogic::createYes(),
				'InTryCatchFoo',
			],
			[
				$testScope,
				'mixedVarFromTryCatch',
				TrinaryLogic::createYes(),
				'float(1.000000)|int(1)',
			],
			[
				$testScope,
				'nullableIntegerFromTryCatch',
				TrinaryLogic::createYes(),
				'int(1)|null',
			],
			[
				$testScope,
				'anotherNullableIntegerFromTryCatch',
				TrinaryLogic::createYes(),
				'int(1)|null',
			],
			[
				$testScope,
				'nullableIntegers',
				TrinaryLogic::createYes(),
				'array<int(0)|int(1)|int(2)|int(3), int(1)|int(2)|int(3)|null>',
			],
			[
				$testScope,
				'union',
				TrinaryLogic::createYes(),
				'array<int(0)|int(1)|int(2)|int(3), int(1)|int(2)|int(3)|string>',
				'int(1)|int(2)|int(3)|string',
			],
			[
				$testScope,
				'trueOrFalse',
				TrinaryLogic::createYes(),
				'bool',
			],
			[
				$testScope,
				'falseOrTrue',
				TrinaryLogic::createYes(),
				'bool',
			],
			[
				$testScope,
				'true',
				TrinaryLogic::createYes(),
				'true',
			],
			[
				$testScope,
				'false',
				TrinaryLogic::createYes(),
				'false',
			],
			[
				$testScope,
				'trueOrFalseFromSwitch',
				TrinaryLogic::createYes(),
				'bool',
			],
			[
				$testScope,
				'trueOrFalseInSwitchWithDefault',
				TrinaryLogic::createYes(),
				'bool',
			],
			[
				$testScope,
				'trueOrFalseInSwitchInAllCases',
				TrinaryLogic::createYes(),
				'bool',
			],
			[
				$testScope,
				'trueOrFalseInSwitchInAllCasesWithDefault',
				TrinaryLogic::createYes(),
				'bool',
			],
			[
				$testScope,
				'trueOrFalseInSwitchInAllCasesWithDefaultCase',
				TrinaryLogic::createYes(),
				'true',
			],
			[
				$testScope,
				'variableDefinedInSwitchWithOtherCasesWithEarlyTermination',
				TrinaryLogic::createYes(),
				'true',
			],
			[
				$testScope,
				'anotherVariableDefinedInSwitchWithOtherCasesWithEarlyTermination',
				TrinaryLogic::createYes(),
				'true',
			],
			[
				$testScope,
				'variableDefinedOnlyInEarlyTerminatingSwitchCases',
				TrinaryLogic::createNo(),
			],
			[
				$testScope,
				'nullableTrueOrFalse',
				TrinaryLogic::createYes(),
				'bool|null',
			],
			[
				$testScope,
				'nonexistentVariableOutsideFor',
				TrinaryLogic::createMaybe(),
				'int(1)',
			],
			[
				$testScope,
				'integerOrNullFromFor',
				TrinaryLogic::createYes(),
				'int(1)|null',
			],
			[
				$testScope,
				'nonexistentVariableOutsideWhile',
				TrinaryLogic::createMaybe(),
				'int(1)',
			],
			[
				$testScope,
				'integerOrNullFromWhile',
				TrinaryLogic::createYes(),
				'int(1)|null',
			],
			[
				$testScope,
				'nonexistentVariableOutsideForeach',
				TrinaryLogic::createMaybe(),
				'null',
			],
			[
				$testScope,
				'integerOrNullFromForeach',
				TrinaryLogic::createYes(),
				'int(1)|null',
			],
			[
				$testScope,
				'notNullableString',
				TrinaryLogic::createYes(),
				'string',
			],
			[
				$testScope,
				'anotherNotNullableString',
				TrinaryLogic::createYes(),
				'string',
			],
			[
				$testScope,
				'notNullableObject',
				TrinaryLogic::createYes(),
				'Foo',
			],
			[
				$testScope,
				'nullableString',
				TrinaryLogic::createYes(),
				'string|null',
			],
			[
				$testScope,
				'alsoNotNullableString',
				TrinaryLogic::createYes(),
				'string',
			],
			[
				$testScope,
				'integerOrString',
				TrinaryLogic::createYes(),
				'int|string',
			],
			[
				$testScope,
				'nullableIntegerAfterNeverCondition',
				TrinaryLogic::createYes(),
				'int|null',
			],
			[
				$testScope,
				'stillNullableInteger',
				TrinaryLogic::createYes(),
				'int(2)|null',
			],
			[
				$testScope,
				'arrayOfIntegers',
				TrinaryLogic::createYes(),
				'array<int(0)|int(1)|int(2), int(1)|int(2)|int(3)>',
			],
			[
				$testScope,
				'arrayAccessObject',
				TrinaryLogic::createYes(),
				\ObjectWithArrayAccess\Foo::class,
			],
			[
				$testScope,
				'width',
				TrinaryLogic::createYes(),
				'float(2.000000)',
			],
			[
				$testScope,
				'someVariableThatWillGetOverrideInFinally',
				TrinaryLogic::createYes(),
				'string',
			],
			[
				$testScope,
				'maybeDefinedButLaterCertainlyDefined',
				TrinaryLogic::createYes(),
				'int(2)|int(3)',
			],
			[
				$testScope,
				'mixed',
				TrinaryLogic::createYes(),
				'mixed',
			],
			[
				$testScope,
				'variableDefinedInSwitchWithoutEarlyTermination',
				TrinaryLogic::createMaybe(),
				'false',
			],
			[
				$testScope,
				'anotherVariableDefinedInSwitchWithoutEarlyTermination',
				TrinaryLogic::createMaybe(),
				'bool',
			],
			[
				$testScope,
				'alwaysDefinedFromSwitch',
				TrinaryLogic::createYes(),
				'int(1)|null',
			],
			[
				$testScope,
				'exceptionFromTryCatch',
				TrinaryLogic::createYes(),
				'(AnotherException&Throwable)|(Throwable&YetAnotherException)|null',
			],
		];
	}

	public function dataUnionInCatch(): array
	{
		return [
			[
				'CatchUnion\BarException|CatchUnion\FooException',
				'$e',
			],
		];
	}

	/**
	 * @dataProvider dataUnionInCatch
	 * @param string $description
	 * @param string $expression
	 */
	public function testUnionInCatch(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/catch-union.php',
			$description,
			$expression
		);
	}

	public function dataUnionAndIntersection(): array
	{
		return [
			[
				'UnionIntersection\Foo',
				'$this->union->foo',
			],
			[
				'mixed',
				'$this->union->bar',
			],
			[
				'UnionIntersection\Foo',
				'$foo->foo',
			],
			[
				'mixed',
				'$foo->bar',
			],
			[
				'UnionIntersection\Foo',
				'$this->union->doFoo()',
			],
			[
				'mixed',
				'$this->union->doBar()',
			],
			[
				'UnionIntersection\Foo',
				'$foo->doFoo()',
			],
			[
				'mixed',
				'$foo->doBar()',
			],
			[
				'UnionIntersection\Foo',
				'$foobar->doFoo()',
			],
			[
				'UnionIntersection\Bar',
				'$foobar->doBar()',
			],
			[
				'int(1)',
				'$this->union::FOO_CONSTANT',
			],
			[
				'mixed',
				'$this->union::BAR_CONSTANT',
			],
			[
				'int(1)',
				'$foo::FOO_CONSTANT',
			],
			[
				'mixed',
				'$foo::BAR_CONSTANT',
			],
			[
				'int(1)',
				'$foobar::FOO_CONSTANT',
			],
			[
				'int(1)',
				'$foobar::BAR_CONSTANT',
			],
			[
				'string',
				'self::IPSUM_CONSTANT',
			],
			[
				'array<int(0)|int(1)|int(2), int(1)|int(2)|int(3)>',
				'parent::PARENT_CONSTANT',
			],
			[
				'UnionIntersection\Foo',
				'$foo::doStaticFoo()',
			],
			[
				'mixed',
				'$foo::doStaticBar()',
			],
			[
				'UnionIntersection\Foo',
				'$foobar::doStaticFoo()',
			],
			[
				'UnionIntersection\Bar',
				'$foobar::doStaticBar()',
			],
			[
				'UnionIntersection\Foo',
				'$this->union::doStaticFoo()',
			],
			[
				'mixed',
				'$this->union::doStaticBar()',
			],
			[
				'object',
				'$this->objectUnion',
			],
			[
				'UnionIntersection\SomeInterface',
				'$object',
			],
		];
	}

	/**
	 * @dataProvider dataUnionAndIntersection
	 * @param string $description
	 * @param string $expression
	 */
	public function testUnionAndIntersection(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/union-intersection.php',
			$description,
			$expression
		);
	}

	/**
	 * @dataProvider dataAssignInIf
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param string $variableName
	 * @param \PHPStan\TrinaryLogic $expectedCertainty
	 * @param string|null $typeDescription
	 * @param string|null $iterableValueTypeDescription
	 */
	public function testAssignInIf(
		Scope $scope,
		string $variableName,
		TrinaryLogic $expectedCertainty,
		string $typeDescription = null,
		string $iterableValueTypeDescription = null
	): void
	{
		$certainty = $scope->hasVariableType($variableName);
		$this->assertTrue(
			$expectedCertainty->equals($certainty),
			sprintf(
				'Certainty of variable $%s is %s, expected %s',
				$variableName,
				$certainty->describe(),
				$expectedCertainty->describe()
			)
		);
		if (!$expectedCertainty->no()) {
			if ($typeDescription === null) {
				$this->fail(sprintf('Missing expected type for defined variable $%s.', $variableName));
			}

			$this->assertSame(
				$typeDescription,
				$scope->getVariableType($variableName)->describe(),
				sprintf('Type of variable $%s does not match the expected one.', $variableName)
			);

			if ($iterableValueTypeDescription !== null) {
				$this->assertSame(
					$iterableValueTypeDescription,
					$scope->getVariableType($variableName)->getIterableValueType()->describe(),
					sprintf('Iterable value type of variable $%s does not match the expected one.', $variableName)
				);
			}
		} elseif ($typeDescription !== null) {
			$this->fail(
				sprintf(
					'No type should be asserted for an undefined variable $%s, %s given.',
					$variableName,
					$typeDescription
				)
			);
		}
	}

	public function testArrayDestructuringShortSyntax(): void
	{
		$this->processFile(__DIR__ . '/data/array-destructuring-short.php', function (\PhpParser\Node $node, Scope $scope): void {
			if ($node instanceof Exit_) {
				$this->assertTrue($scope->hasVariableType('a')->yes());
				$this->assertTrue($scope->hasVariableType('b')->yes());
				$this->assertTrue($scope->hasVariableType('c')->yes());
				$this->assertTrue($scope->hasVariableType('d')->yes());
				$this->assertTrue($scope->hasVariableType('e')->yes());
			}
		});
	}

	public function dataParameterTypes(): array
	{
		return [
			[
				'int',
				'$integer',
			],
			[
				'bool',
				'$boolean',
			],
			[
				'string',
				'$string',
			],
			[
				'float',
				'$float',
			],
			[
				'TypesNamespaceTypehints\Lorem',
				'$loremObject',
			],
			[
				'mixed',
				'$mixed',
			],
			[
				'array',
				'$array',
			],
			[
				'bool|null',
				'$isNullable',
			],
			[
				'TypesNamespaceTypehints\Lorem',
				'$loremObjectRef',
			],
			[
				'TypesNamespaceTypehints\Bar',
				'$barObject',
			],
			[
				'TypesNamespaceTypehints\Foo',
				'$fooObject',
			],
			[
				'TypesNamespaceTypehints\Bar',
				'$anotherBarObject',
			],
			[
				'callable',
				'$callable',
			],
			[
				'array<int, string>',
				'$variadicStrings',
			],
			[
				'string',
				'$variadicStrings[0]',
			],
		];
	}

	/**
	 * @dataProvider dataParameterTypes
	 * @param string $typeClass
	 * @param string $expression
	 */
	public function testTypehints(
		string $typeClass,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/typehints.php',
			$typeClass,
			$expression
		);
	}

	public function dataAnonymousFunctionParameterTypes(): array
	{
		return [
			[
				'int',
				'$integer',
			],
			[
				'bool',
				'$boolean',
			],
			[
				'string',
				'$string',
			],
			[
				'float',
				'$float',
			],
			[
				'TypesNamespaceTypehints\Lorem',
				'$loremObject',
			],
			[
				'mixed',
				'$mixed',
			],
			[
				'array',
				'$array',
			],
			[
				'bool|null',
				'$isNullable',
			],
			[
				'callable',
				'$callable',
			],
			[
				'TypesNamespaceTypehints\FooWithAnonymousFunction',
				'$self',
			],
		];
	}

	/**
	 * @dataProvider dataAnonymousFunctionParameterTypes
	 * @param string $description
	 * @param string $expression
	 */
	public function testAnonymousFunctionTypehints(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/typehints-anonymous-function.php',
			$description,
			$expression
		);
	}

	public function dataVarAnnotations(): array
	{
		return [
			[
				'int',
				'$integer',
			],
			[
				'bool',
				'$boolean',
			],
			[
				'string',
				'$string',
			],
			[
				'float',
				'$float',
			],
			[
				'VarAnnotations\Lorem',
				'$loremObject',
			],
			[
				'AnotherNamespace\Bar',
				'$barObject',
			],
			[
				'mixed',
				'$mixed',
			],
			[
				'array',
				'$array',
			],
			[
				'bool|null',
				'$isNullable',
			],
			[
				'callable',
				'$callable',
			],
			[
				'VarAnnotations\Foo',
				'$self',
			],
			[
				'float',
				'$invalidInteger',
			],
			[
				'static(VarAnnotations\Foo)',
				'$static',
			],
		];
	}

	/**
	 * @dataProvider dataVarAnnotations
	 * @param string $description
	 * @param string $expression
	 */
	public function testVarAnnotations(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/var-annotations.php',
			$description,
			$expression
		);
	}

	public function dataCasts(): array
	{
		return [
			[
				'int',
				'$castedInteger',
			],
			[
				'bool',
				'$castedBoolean',
			],
			[
				'float',
				'$castedFloat',
			],
			[
				'string',
				'$castedString',
			],
			[
				'array',
				'$castedArray',
			],
			[
				'stdClass',
				'$castedObject',
			],
		];
	}

	/**
	 * @dataProvider dataCasts
	 * @param string $desciptiion
	 * @param string $expression
	 */
	public function testCasts(
		string $desciptiion,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/casts.php',
			$desciptiion,
			$expression
		);
	}

	public function dataUnsetCast(): array
	{
		return [
			[
				'null',
				'$castedNull',
			],
		];
	}

	/**
	 * @dataProvider dataUnsetCast
	 * @param string $desciptiion
	 * @param string $expression
	 */
	public function testUnsetCast(
		string $desciptiion,
		string $expression
	): void
	{
		if (PHP_VERSION_ID >= 70200) {
			$this->markTestSkipped(
				'Test cannot be run on PHP 7.2 and higher - (unset) cast is deprecated.'
			);
		}
		$this->assertTypes(
			__DIR__ . '/data/cast-unset.php',
			$desciptiion,
			$expression
		);
	}

	public function dataDeductedTypes(): array
	{
		return [
			[
				'int(1)',
				'$integerLiteral',
			],
			[
				'true',
				'$booleanLiteral',
			],
			[
				'false',
				'$anotherBooleanLiteral',
			],
			[
				'string',
				'$stringLiteral',
			],
			[
				'float(1.000000)',
				'$floatLiteral',
			],
			[
				'float(1.000000)',
				'$floatAssignedByRef',
			],
			[
				'null',
				'$nullLiteral',
			],
			[
				'TypesNamespaceDeductedTypes\Lorem',
				'$loremObjectLiteral',
			],
			[
				'mixed',
				'$mixedObjectLiteral',
			],
			[
				'static(TypesNamespaceDeductedTypes\Foo)',
				'$newStatic',
			],
			[
				'array',
				'$arrayLiteral',
			],
			[
				'string',
				'$stringFromFunction',
			],
			[
				'TypesNamespaceFunctions\Foo',
				'$fooObjectFromFunction',
			],
			[
				'mixed',
				'$mixedFromFunction',
			],
			[
				'int(1)',
				'\TypesNamespaceDeductedTypes\Foo::INTEGER_CONSTANT',
			],
			[
				'int(1)',
				'self::INTEGER_CONSTANT',
			],
			[
				'float(1.000000)',
				'self::FLOAT_CONSTANT',
			],
			[
				'string',
				'self::STRING_CONSTANT',
			],
			[
				'array',
				'self::ARRAY_CONSTANT',
			],
			[
				'true',
				'self::BOOLEAN_CONSTANT',
			],
			[
				'null',
				'self::NULL_CONSTANT',
			],
			[
				'int(1)',
				'$foo::INTEGER_CONSTANT',
			],
			[
				'float(1.000000)',
				'$foo::FLOAT_CONSTANT',
			],
			[
				'string',
				'$foo::STRING_CONSTANT',
			],
			[
				'array',
				'$foo::ARRAY_CONSTANT',
			],
			[
				'true',
				'$foo::BOOLEAN_CONSTANT',
			],
			[
				'null',
				'$foo::NULL_CONSTANT',
			],
		];
	}

	/**
	 * @dataProvider dataDeductedTypes
	 * @param string $description
	 * @param string $expression
	 */
	public function testDeductedTypes(
		string $description,
		string $expression
	): void
	{
		require_once __DIR__ . '/data/function-definitions.php';
		$this->assertTypes(
			__DIR__ . '/data/deducted-types.php',
			$description,
			$expression
		);
	}

	public function dataProperties(): array
	{
		return [
			[
				'mixed',
				'$this->mixedProperty',
			],
			[
				'mixed',
				'$this->alsoMixedProperty',
			],
			[
				'mixed',
				'$this->anotherMixedProperty',
			],
			[
				'mixed',
				'$this->yetAnotherMixedProperty',
			],
			[
				'int',
				'$this->integerProperty',
			],
			[
				'int',
				'$this->anotherIntegerProperty',
			],
			[
				'array',
				'$this->arrayPropertyOne',
			],
			[
				'array',
				'$this->arrayPropertyOther',
			],
			[
				'PropertiesNamespace\\Lorem',
				'$this->objectRelative',
			],
			[
				'SomeOtherNamespace\\Ipsum',
				'$this->objectFullyQualified',
			],
			[
				'SomeNamespace\\Amet',
				'$this->objectUsed',
			],
			[
				'mixed',
				'$this->nonexistentProperty',
			],
			[
				'int|null',
				'$this->nullableInteger',
			],
			[
				'SomeNamespace\Amet|null',
				'$this->nullableObject',
			],
			[
				'PropertiesNamespace\\Foo',
				'$this->selfType',
			],
			[
				'static(PropertiesNamespace\Foo)',
				'$this->staticType',
			],
			[
				'null',
				'$this->nullType',
			],
			[
				'SomeNamespace\Sit',
				'$this->inheritedProperty',
			],
			[
				'PropertiesNamespace\Bar',
				'$this->barObject->doBar()',
			],
			[
				'mixed',
				'$this->invalidTypeProperty',
			],
			[
				'resource',
				'$this->resource',
			],
			[
				'mixed',
				'$yetAnotherAnotherMixedParameter',
			],
			[
				'mixed',
				'$yetAnotherAnotherAnotherMixedParameter',
			],
			[
				'mixed',
				'$yetAnotherAnotherAnotherAnotherMixedParameter',
			],
			[
				'string',
				'self::$staticStringProperty',
			],
			[
				'SomeGroupNamespace\One',
				'$this->groupUseProperty',
			],
			[
				'SomeGroupNamespace\Two',
				'$this->anotherGroupUseProperty',
			],
			[
				'PropertiesNamespace\Bar',
				'$this->inheritDocProperty',
			],
			[
				'PHPUnit_Framework_MockObject_MockObject&PropertiesNamespace\Foo',
				'$this->phpunitProperty',
			],
			[
				'PHPUnit_Framework_MockObject_MockObject&PropertiesNamespace\Foo',
				'$this->anotherPhpunitProperty',
			],
			[
				'PHPUnit\Framework\MockObject\MockObject&PropertiesNamespace\Foo',
				'$this->yetAnotherPhpunitProperty',
			],
			[
				'PHPUnit\Framework\MockObject\MockObject&PropertiesNamespace\Foo',
				'$this->yetYetAnotherPhpunitProperty',
			],
		];
	}

	/**
	 * @dataProvider dataProperties
	 * @param string $description
	 * @param string $expression
	 */
	public function testProperties(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/properties.php',
			$description,
			$expression
		);
	}

	public function dataBinaryOperations(): array
	{
		$typeCallback = function ($value) {
			$type = gettype($value);
			if ($type === 'integer') {
				return sprintf('int(%d)', $value);
			} elseif ($type === 'double') {
				return sprintf('float(%f)', $value);
			} elseif ($type === 'boolean') {
				return 'bool';
			} elseif ($type === 'string') {
				return $type;
			}

			return MixedType::class;
		};

		return [
			[
				$typeCallback(true && false),
				'true && false',
			],
			[
				$typeCallback(true || false),
				'true || false',
			],
			[
				$typeCallback(true xor false),
				'true xor false',
			],
			[
				$typeCallback(true and false),
				'true and false',
			],
			[
				$typeCallback(true or false),
				'true or false',
			],
			[
				$typeCallback(!true),
				'!true',
			],
			[
				$typeCallback(-1),
				'-1',
			],
			[
				$typeCallback(+1),
				'+1',
			],
			// integer + integer
			[
				$typeCallback(1 + 1),
				'1 + 1',
			],
			[
				$typeCallback(1 - 1),
				'1 - 1',
			],
			[
				$typeCallback(1 / 2),
				'1 / 2',
			],
			[
				$typeCallback(1 * 1),
				'1 * 1',
			],
			[
				$typeCallback(1 ** 1),
				'1 ** 1',
			],
			[
				$typeCallback(1 % 1),
				'1 % 1',
			],
			[
				'float',
				'$integer /= 2',
			],
			[
				'int',
				'$integer *= 1',
			],
			// float + float
			[
				$typeCallback(1.2 + 1.4),
				'1.2 + 1.4',
			],
			[
				$typeCallback(1.2 - 1.4),
				'1.2 - 1.4',
			],
			[
				$typeCallback(1.2 / 2.4),
				'1.2 / 2.4',
			],
			[
				$typeCallback(1.2 * 1.4),
				'1.2 * 1.4',
			],
			[
				$typeCallback(1.2 ** 1.4),
				'1.2 ** 1.4',
			],
			[
				$typeCallback(3.2 % 2.4),
				'3.2 % 2.4',
			],
			[
				'float',
				'$float /= 2.4',
			],
			[
				'float',
				'$float *= 2.4',
			],
			// integer + float
			[
				$typeCallback(1 + 1.4),
				'1 + 1.4',
			],
			[
				$typeCallback(1 - 1.4),
				'1 - 1.4',
			],
			[
				$typeCallback(1 / 2.4),
				'1 / 2.4',
			],
			[
				$typeCallback(1 * 1.4),
				'1 * 1.4',
			],
			[
				$typeCallback(1 ** 1.4),
				'1 ** 1.4',
			],
			[
				$typeCallback(3 % 2.4),
				'3 % 2.4',
			],
			[
				'float',
				'$integer /= 2.4',
			],
			[
				'float',
				'$integer *= 2.4',
			],
			// float + integer
			[
				$typeCallback(1.2 + 1),
				'1.2 + 1',
			],
			[
				$typeCallback(1.2 - 1),
				'1.2 - 1',
			],
			[
				$typeCallback(1.2 / 2),
				'1.2 / 2',
			],
			[
				$typeCallback(1.2 * 1),
				'1.2 * 1',
			],
			[
				$typeCallback(1.2 ** 1),
				'1.2 ** 1',
			],
			[
				$typeCallback(3.2 % 2),
				'3.2 % 2',
			],
			[
				'float',
				'$float /= 2.4',
			],
			[
				'float',
				'$float *= 2',
			],
			// boolean
			[
				$typeCallback(true + false),
				'true + false',
			],
			// string
			[
				$typeCallback('a' . 'b'),
				"'a' . 'b'",
			],
			[
				$typeCallback(1 . 'b'),
				"1 . 'b'",
			],
			[
				$typeCallback(1.0 . 'b'),
				"1.0 . 'b'",
			],
			[
				$typeCallback(1.0 . 2.0),
				'1.0 . 2.0',
			],
			[
				$typeCallback('foo' <=> 'bar'),
				"'foo' <=> 'bar'",
			],
			[
				'mixed',
				'1 + doFoo()',
			],
			[
				'mixed',
				'1 / doFoo()',
			],
			[
				'mixed',
				'1.0 / doFoo()',
			],
			[
				'mixed',
				'doFoo() / 1',
			],
			[
				'mixed',
				'doFoo() / 1.0',
			],
			[
				'mixed',
				'1.0 + doFoo()',
			],
			[
				'mixed',
				'1.0 + doFoo()',
			],
			[
				'mixed',
				'doFoo() + 1',
			],
			[
				'mixed',
				'doFoo() + 1.0',
			],
			[
				'string|null',
				"doFoo() ? 'foo' : null",
			],
			[
				'int(12)|null',
				'12 ?: null',
			],
			[
				'int(12)|string',
				'$string ?: 12',
			],
			[
				'int(12)|string',
				'$stringOrNull ?: 12',
			],
			[
				'int',
				'$integer ?: 12',
			],
			[
				'string',
				"'foo' ?? null", // "else" never gets executed
			],
			[
				'string|null',
				'$stringOrNull ?? null',
			],
			[
				'string',
				'$string ?? \'foo\'',
			],
			[
				'string',
				'$stringOrNull ?? \'foo\'',
			],
			[
				'int|string',
				'$string ?? $integer',
			],
			[
				'int|string',
				'$stringOrNull ?? $integer',
			],
			[
				'string',
				'\Foo::class',
			],
			[
				'int',
				'__LINE__',
			],
			[
				'string',
				'__DIR__',
			],
			[
				'int(1)|int(2)|int(3)', // if the only argument in min is array, lowest value in that array is returned
				'min([1, 2, 3])',
			],
			[
				'array<int(0)|int(1)|int(2), int(1)|int(2)|int(3)|int(4)|int(5)>',
				'min([1, 2, 3], [4, 5, 5])',
			],
			[
				'int(1)|int(2)|int(3)',
				'min(...[1, 2, 3])',
			],
			[
				'float(1.100000)|float(2.200000)|float(3.300000)',
				'min(...[1.1, 2.2, 3.3])',
			],
			[
				'float(1.100000)|int(2)|int(3)',
				'min(...[1.1, 2, 3])',
			],
			[
				'int(1)|int(2)|int(3)',
				'max(...[1, 2, 3])',
			],
			[
				'float(1.100000)|float(2.200000)|float(3.300000)',
				'max(...[1.1, 2.2, 3.3])',
			],
			[
				'int(1)|int(2)|int(3)',
				'min(1, 2, 3)',
			],
			[
				'int(1)|int(2)|int(3)',
				'max(1, 2, 3)',
			],
			[
				'float(1.100000)|float(2.200000)|float(3.300000)',
				'min(1.1, 2.2, 3.3)',
			],
			[
				'float(1.100000)|float(2.200000)|float(3.300000)',
				'max(1.1, 2.2, 3.3)',
			],
			[
				'DateTimeImmutable',
				'max(new \DateTimeImmutable("today"), new \DateTimeImmutable("tomorrow"))',
			],
			[
				'float(2.200000)|float(3.300000)|int(1)',
				'min(1, 2.2, 3.3)',
			],
			[
				'string',
				'"Hello $world"',
			],
			[
				'string',
				'$string .= "str"',
			],
			[
				'int',
				'$integer <<= 2.2',
			],
			[
				'int',
				'$float >>= 2.2',
			],
			[
				'int',
				'count($arrayOfIntegers)',
			],
			[
				'int',
				'count($arrayOfIntegers) + count($arrayOfIntegers)',
			],
			[
				'true',
				'$string === "foo"',
			],
			[
				'false',
				'$string !== "foo"',
			],
			[
				'bool',
				'$string == "foo"',
			],
			[
				'bool',
				'$string != "foo"',
			],
			[
				'true',
				'$foo instanceof Foo',
			],
			[
				'bool',
				'$foo instanceof Bar',
			],
			[
				'bool',
				'isset($foo)',
			],
			[
				'bool',
				'!isset($foo)',
			],
			[
				'bool',
				'empty($foo)',
			],
			[
				'bool',
				'!empty($foo)',
			],
			[
				'array<int(0)|int(1)|int(2), int>',
				'$arrayOfIntegers + $arrayOfIntegers',
			],
			[
				'array<int(0)|int(1)|int(2), int>',
				'$arrayOfIntegers += $arrayOfIntegers',
			],
			[
				'array<int(0)|int(1)|int(2), int|string>',
				'$arrayOfIntegers += ["foo"]',
			],
			[
				'mixed',
				'$arrayOfIntegers += "foo"',
			],
			[
				'int',
				'@count($arrayOfIntegers)',
			],
			[
				'array<int(0)|int(1)|int(2), int>',
				'$anotherArray = $arrayOfIntegers',
			],
			[
				'string|null',
				'var_export()',
			],
			[
				'null',
				'var_export($string)',
			],
			[
				'null',
				'var_export($string, false)',
			],
			[
				'string',
				'var_export($string, true)',
			],
		];
	}

	/**
	 * @dataProvider dataBinaryOperations
	 * @param string $description
	 * @param string $expression
	 */
	public function testBinaryOperations(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/binary.php',
			$description,
			$expression
		);
	}

	public function dataCloneOperators(): array
	{
		return [
			[
				'CloneOperators\Foo',
				'clone $fooObject',
			],
		];
	}

	/**
	 * @dataProvider dataCloneOperators
	 * @param string $description
	 * @param string $expression
	 */
	public function testCloneOperators(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/clone.php',
			$description,
			$expression
		);
	}

	public function dataLiteralArrays(): array
	{
		return [
			[
				'int(0)',
				'$integers[0]',
			],
			[
				'int(1)',
				'$integers[1]',
			],
			[
				'string',
				'$strings[0]',
			],
			[
				'mixed',
				'$emptyArray[0]',
			],
			[
				'int(0)',
				'$mixedArray[0]',
			],
			[
				'bool',
				'$integers[0] >= $integers[1] - 1',
			],
		];
	}

	/**
	 * @dataProvider dataLiteralArrays
	 * @param string $description
	 * @param string $expression
	 */
	public function testLiteralArrays(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/literal-arrays.php',
			$description,
			$expression
		);
	}

	public function dataLiteralArraysKeys(): array
	{
		return [
			[
				'int(0)|int(1)|int(2)',
				"'NoKeysArray';",
			],
			[
				'int(0)|int(1)|int(2)',
				"'IntegersAndNoKeysArray';",
			],
			[
				'int(0)|int(1)|string',
				"'StringsAndNoKeysArray';",
			],
			[
				'int(1)|int(2)|int(3)',
				"'IntegersAsStringsAndNoKeysArray';",
			],
			[
				'int(1)|int(2)',
				"'IntegersAsStringsArray';",
			],
			[
				'int(1)|int(2)',
				"'IntegersArray';",
			],
			[
				'int(1)|int(2)|int(3)',
				"'IntegersWithFloatsArray';",
			],
			[
				'string',
				"'StringsArray';",
			],
			[
				'string',
				"'StringsWithNullArray';",
			],
			[
				'int|string',
				"'IntegersWithStringFromMethodArray';",
			],
			[
				'int(1)|int(2)|string',
				"'IntegersAndStringsArray';",
			],
			[
				'int(0)|int(1)',
				"'BooleansArray';",
			],
			[
				'mixed',
				"'UnknownConstantArray';",
			],
		];
	}

	/**
	 * @dataProvider dataLiteralArraysKeys
	 * @param string $description
	 * @param string $evaluatedPointExpressionType
	 */
	public function testLiteralArraysKeys(
		string $description,
		string $evaluatedPointExpressionType
	): void
	{
		if (!defined('STRING_ONE')) {
			define('STRING_ONE', '1');
			define('INT_ONE', 1);
			define('STRING_FOO', 'foo');
		}

		$this->assertTypes(
			__DIR__ . '/data/literal-arrays-keys.php',
			$description,
			'$key',
			[],
			[],
			$evaluatedPointExpressionType
		);
	}

	public function dataTypeFromFunctionPhpDocs(): array
	{
		return [
			[
				'mixed',
				'$mixedParameter',
			],
			[
				'mixed',
				'$alsoMixedParameter',
			],
			[
				'mixed',
				'$anotherMixedParameter',
			],
			[
				'mixed',
				'$yetAnotherMixedParameter',
			],
			[
				'int',
				'$integerParameter',
			],
			[
				'int',
				'$anotherIntegerParameter',
			],
			[
				'array',
				'$arrayParameterOne',
			],
			[
				'array',
				'$arrayParameterOther',
			],
			[
				'MethodPhpDocsNamespace\\Lorem',
				'$objectRelative',
			],
			[
				'SomeOtherNamespace\\Ipsum',
				'$objectFullyQualified',
			],
			[
				'SomeNamespace\\Amet',
				'$objectUsed',
			],
			[
				'mixed',
				'$nonexistentParameter',
			],
			[
				'int|null',
				'$nullableInteger',
			],
			[
				'SomeNamespace\Amet|null',
				'$nullableObject',
			],
			[
				'SomeNamespace\Amet|null',
				'$anotherNullableObject',
			],
			[
				'null',
				'$nullType',
			],
			[
				'MethodPhpDocsNamespace\Bar',
				'$barObject->doBar()',
			],
			[
				'MethodPhpDocsNamespace\Bar',
				'$conflictedObject',
			],
			[
				'MethodPhpDocsNamespace\Baz',
				'$moreSpecifiedObject',
			],
			[
				'MethodPhpDocsNamespace\Baz',
				'$moreSpecifiedObject->doFluent()',
			],
			[
				'MethodPhpDocsNamespace\Baz|null',
				'$moreSpecifiedObject->doFluentNullable()',
			],
			[
				'MethodPhpDocsNamespace\Baz',
				'$moreSpecifiedObject->doFluentArray()[0]',
			],
			[
				'iterable<MethodPhpDocsNamespace\Baz>&MethodPhpDocsNamespace\Collection',
				'$moreSpecifiedObject->doFluentUnionIterable()',
			],
			[
				'MethodPhpDocsNamespace\Baz',
				'$fluentUnionIterableBaz',
			],
			[
				'resource',
				'$resource',
			],
			[
				'mixed',
				'$yetAnotherAnotherMixedParameter',
			],
			[
				'mixed',
				'$yetAnotherAnotherAnotherMixedParameter',
			],
			[
				'void',
				'$voidParameter',
			],
			[
				'SomeNamespace\Consecteur',
				'$useWithoutAlias',
			],
			[
				'true',
				'$true',
			],
			[
				'false',
				'$false',
			],
			[
				'true',
				'$boolTrue',
			],
			[
				'false',
				'$boolFalse',
			],
			[
				'bool',
				'$trueBoolean',
			],
			[
				'bool',
				'$parameterWithDefaultValueFalse',
			],
		];
	}

	public function dataTypeFromFunctionFunctionPhpDocs(): array
	{
		return [
			[
				'MethodPhpDocsNamespace\Foo',
				'$fooFunctionResult',
			],
			[
				'MethodPhpDocsNamespace\Bar',
				'$barFunctionResult',
			],
		];
	}

	/**
	 * @dataProvider dataTypeFromFunctionPhpDocs
	 * @dataProvider dataTypeFromFunctionFunctionPhpDocs
	 * @param string $description
	 * @param string $expression
	 */
	public function testTypeFromFunctionPhpDocs(
		string $description,
		string $expression
	): void
	{
		require_once __DIR__ . '/data/functionPhpDocs.php';
		$this->assertTypes(
			__DIR__ . '/data/functionPhpDocs.php',
			$description,
			$expression
		);
	}

	public function dataTypeFromMethodPhpDocs(): array
	{
		return [
			[
				'MethodPhpDocsNamespace\\Foo',
				'$selfType',
			],
			[
				'static(MethodPhpDocsNamespace\Foo)',
				'$staticType',
				false,
			],
			[
				'MethodPhpDocsNamespace\Foo',
				'$this->doFoo()',
			],
			[
				'MethodPhpDocsNamespace\Bar',
				'static::doSomethingStatic()',
			],
			[
				'static(MethodPhpDocsNamespace\Foo)',
				'parent::doLorem()',
			],
			[
				'MethodPhpDocsNamespace\FooParent',
				'$parent->doLorem()',
				false,
			],
			[
				'static(MethodPhpDocsNamespace\Foo)',
				'$this->doLorem()',
			],
			[
				'MethodPhpDocsNamespace\Foo',
				'$differentInstance->doLorem()',
			],
			[
				'static(MethodPhpDocsNamespace\Foo)',
				'parent::doIpsum()',
			],
			[
				'MethodPhpDocsNamespace\FooParent',
				'$parent->doIpsum()',
				false,
			],
			[
				'MethodPhpDocsNamespace\Foo',
				'$differentInstance->doIpsum()',
			],
			[
				'static(MethodPhpDocsNamespace\Foo)',
				'$this->doIpsum()',
			],
			[
				'MethodPhpDocsNamespace\Foo',
				'$this->doBar()[0]',
			],
			[
				'MethodPhpDocsNamespace\Bar',
				'self::doSomethingStatic()',
			],
			[
				'MethodPhpDocsNamespace\Bar',
				'\MethodPhpDocsNamespace\Foo::doSomethingStatic()',
			],
			[
				'$this(MethodPhpDocsNamespace\Foo)',
				'parent::doThis()',
			],
			[
				'$this(MethodPhpDocsNamespace\Foo)|null',
				'parent::doThisNullable()',
			],
			[
				'$this(MethodPhpDocsNamespace\Foo)|MethodPhpDocsNamespace\Bar|null',
				'parent::doThisUnion()',
			],
			[
				'MethodPhpDocsNamespace\FooParent',
				'$this->returnParent()',
				false,
			],
			[
				'MethodPhpDocsNamespace\FooParent',
				'$this->returnPhpDocParent()',
				false,
			],
			[
				'array<null>',
				'$this->returnNulls()',
			],
			[
				'object',
				'$objectWithoutNativeTypehint',
			],
			[
				'object',
				'$objectWithNativeTypehint',
			],
			[
				'object',
				'$this->returnObject()',
			],
			[
				'MethodPhpDocsNamespace\Foo&PHPUnit_Framework_MockObject_MockObject',
				'$this->returnPhpunitMock()',
			],
			[
				'MethodPhpDocsNamespace\FooParent',
				'new parent()',
			],
		];
	}

	/**
	 * @dataProvider dataTypeFromFunctionPhpDocs
	 * @dataProvider dataTypeFromMethodPhpDocs
	 * @param string $description
	 * @param string $expression
	 */
	public function testTypeFromMethodPhpDocs(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/methodPhpDocs.php',
			$description,
			$expression
		);
	}

	/**
	 * @dataProvider dataTypeFromFunctionPhpDocs
	 * @dataProvider dataTypeFromMethodPhpDocs
	 * @param string $description
	 * @param string $expression
	 * @param bool $replaceClass
	 */
	public function testTypeFromMethodPhpDocsInheritDoc(
		string $description,
		string $expression,
		bool $replaceClass = true
	): void
	{
		if ($replaceClass) {
			$description = str_replace('$this(MethodPhpDocsNamespace\Foo)', '$this(MethodPhpDocsNamespace\FooInheritDocChild)', $description);
			$description = str_replace('static(MethodPhpDocsNamespace\Foo)', 'static(MethodPhpDocsNamespace\FooInheritDocChild)', $description);
			$description = str_replace('MethodPhpDocsNamespace\FooParent', 'MethodPhpDocsNamespace\Foo', $description);
		}
		$this->assertTypes(
			__DIR__ . '/data/method-phpDocs-inheritdoc.php',
			$description,
			$expression
		);
	}

	public function dataInstanceOf(): array
	{
		return [
			[
				'PhpParser\Node\Expr\ArrayDimFetch',
				'$foo',
			],
			[
				'PhpParser\Node\Expr',
				'$bar',
			],
			[
				'mixed',
				'$baz',
			],
			[
				'InstanceOfNamespace\Lorem',
				'$lorem',
			],
			[
				'InstanceOfNamespace\Dolor',
				'$dolor',
			],
			[
				'InstanceOfNamespace\Sit',
				'$sit',
			],
			[
				'InstanceOfNamespace\Foo',
				'$self',
			],
			[
				'static(InstanceOfNamespace\Foo)',
				'$static',
			],
			[
				'static(InstanceOfNamespace\Foo)',
				'clone $static',
			],
			[
				'InstanceOfNamespace\BarInterface&InstanceOfNamespace\Foo',
				'$intersected',
			],
			[
				'$this(InstanceOfNamespace\Foo)&InstanceOfNamespace\BarInterface',
				'$this',
			],
		];
	}

	/**
	 * @dataProvider dataInstanceOf
	 * @param string $description
	 * @param string $expression
	 */
	public function testInstanceOf(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/instanceof.php',
			$description,
			$expression
		);
	}

	public function testNotSwitchInstanceof(): void
	{
		$this->assertTypes(
			__DIR__ . '/data/switch-instanceof-not.php',
			'mixed',
			'$foo'
		);
	}

	public function dataSwitchInstanceOf(): array
	{
		return [
			[
				'mixed',
				'$foo',
			],
			[
				'mixed',
				'$bar',
			],
			[
				'SwitchInstanceOf\Baz',
				'$baz',
			],
		];
	}

	/**
	 * @dataProvider dataSwitchInstanceOf
	 * @param string $description
	 * @param string $expression
	 */
	public function testSwitchInstanceof(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/switch-instanceof.php',
			$description,
			$expression
		);
	}

	public function dataSwitchGetClass(): array
	{
		return [
			[
				'SwitchGetClass\Lorem',
				'$lorem',
				"'normalName';",
			],
			[
				'SwitchGetClass\Foo',
				'$lorem',
				"'selfReferentialName';",
			],
		];
	}

	/**
	 * @dataProvider dataSwitchGetClass
	 * @param string $description
	 * @param string $expression
	 * @param string $evaluatedPointExpression
	 */
	public function testSwitchGetClass(
		string $description,
		string $expression,
		string $evaluatedPointExpression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/switch-get-class.php',
			$description,
			$expression,
			[],
			[],
			$evaluatedPointExpression
		);
	}

	public function dataDynamicMethodReturnTypeExtensions(): array
	{
		return [
			[
				'mixed',
				'$em->getByFoo($foo)',
			],
			[
				'DynamicMethodReturnTypesNamespace\Entity',
				'$em->getByPrimary()',
			],
			[
				'DynamicMethodReturnTypesNamespace\Entity',
				'$em->getByPrimary($foo)',
			],
			[
				'DynamicMethodReturnTypesNamespace\Foo',
				'$em->getByPrimary(DynamicMethodReturnTypesNamespace\Foo::class)',
			],
			[
				'mixed',
				'$iem->getByFoo($foo)',
			],
			[
				'DynamicMethodReturnTypesNamespace\Entity',
				'$iem->getByPrimary()',
			],
			[
				'DynamicMethodReturnTypesNamespace\Entity',
				'$iem->getByPrimary($foo)',
			],
			[
				'DynamicMethodReturnTypesNamespace\Foo',
				'$iem->getByPrimary(DynamicMethodReturnTypesNamespace\Foo::class)',
			],
			[
				'mixed',
				'EntityManager::getByFoo($foo)',
			],
			[
				'DynamicMethodReturnTypesNamespace\EntityManager',
				'\DynamicMethodReturnTypesNamespace\EntityManager::createManagerForEntity()',
			],
			[
				'DynamicMethodReturnTypesNamespace\EntityManager',
				'\DynamicMethodReturnTypesNamespace\EntityManager::createManagerForEntity($foo)',
			],
			[
				'DynamicMethodReturnTypesNamespace\Foo',
				'\DynamicMethodReturnTypesNamespace\EntityManager::createManagerForEntity(DynamicMethodReturnTypesNamespace\Foo::class)',
			],
			[
				'mixed',
				'\DynamicMethodReturnTypesNamespace\InheritedEntityManager::getByFoo($foo)',
			],
			[
				'DynamicMethodReturnTypesNamespace\EntityManager',
				'\DynamicMethodReturnTypesNamespace\InheritedEntityManager::createManagerForEntity()',
			],
			[
				'DynamicMethodReturnTypesNamespace\EntityManager',
				'\DynamicMethodReturnTypesNamespace\InheritedEntityManager::createManagerForEntity($foo)',
			],
			[
				'DynamicMethodReturnTypesNamespace\Foo',
				'\DynamicMethodReturnTypesNamespace\InheritedEntityManager::createManagerForEntity(DynamicMethodReturnTypesNamespace\Foo::class)',
			],
		];
	}

	/**
	 * @dataProvider dataDynamicMethodReturnTypeExtensions
	 * @param string $description
	 * @param string $expression
	 */
	public function testDynamicMethodReturnTypeExtensions(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/dynamic-method-return-types.php',
			$description,
			$expression,
			[
				new class() implements DynamicMethodReturnTypeExtension {

					public function getClass(): string
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

						return new ObjectType((string) $arg->class);
					}
				},
			],
			[
				new class() implements DynamicStaticMethodReturnTypeExtension {

					public function getClass(): string
					{
						return \DynamicMethodReturnTypesNamespace\EntityManager::class;
					}

					public function isStaticMethodSupported(MethodReflection $methodReflection): bool
					{
						return in_array($methodReflection->getName(), ['createManagerForEntity'], true);
					}

					public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): \PHPStan\Type\Type
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

						return new ObjectType((string) $arg->class);
					}
				},
			]
		);
	}

	public function dataOverwritingVariable(): array
	{
		return [
			[
				'mixed',
				'$var',
				'new \OverwritingVariable\Bar();',
			],
			[
				'OverwritingVariable\Bar',
				'$var',
				'$var->methodFoo();',
			],
			[
				'OverwritingVariable\Foo',
				'$var',
				'die;',
			],
		];
	}

	/**
	 * @dataProvider dataOverwritingVariable
	 * @param string $description
	 * @param string $expression
	 * @param string $evaluatedPointExpressionType
	 */
	public function testOverwritingVariable(
		string $description,
		string $expression,
		string $evaluatedPointExpressionType
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/overwritingVariable.php',
			$description,
			$expression,
			[],
			[],
			$evaluatedPointExpressionType
		);
	}

	public function dataNegatedInstanceof(): array
	{
		return [
			[
				'NegatedInstanceOf\Foo',
				'$foo',
			],
			[
				'NegatedInstanceOf\Bar',
				'$bar',
			],
			[
				'mixed',
				'$lorem',
			],
			[
				'mixed',
				'$dolor',
			],
			[
				'mixed',
				'$sit',
			],
			[
				'mixed',
				'$mixedFoo',
			],
			[
				'mixed',
				'$mixedBar',
			],
			[
				'NegatedInstanceOf\Foo',
				'$self',
			],
			[
				'static(NegatedInstanceOf\Foo)',
				'$static',
			],
			[
				'NegatedInstanceOf\Foo',
				'$anotherFoo',
			],
			[
				'NegatedInstanceOf\Bar&NegatedInstanceOf\Foo',
				'$fooAndBar',
			],
		];
	}

	/**
	 * @dataProvider dataNegatedInstanceof
	 * @param string $description
	 * @param string $expression
	 */
	public function testNegatedInstanceof(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/negated-instanceof.php',
			$description,
			$expression
		);
	}

	public function dataAnonymousFunction(): array
	{
		return [
			[
				'string',
				'$str',
			],
			[
				'int(1)',
				'$integer',
			],
			[
				'mixed',
				'$bar',
			],
		];
	}

	/**
	 * @dataProvider dataAnonymousFunction
	 * @param string $description
	 * @param string $expression
	 */
	public function testAnonymousFunction(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/anonymous-function.php',
			$description,
			$expression
		);
	}

	public function dataForeachArrayType(): array
	{
		return [
			[
				__DIR__ . '/data/foreach/array-object-type.php',
				'AnotherNamespace\Foo',
				'$foo',
			],
			[
				__DIR__ . '/data/foreach/array-object-type.php',
				'AnotherNamespace\Foo',
				'$foos[0]',
			],
			[
				__DIR__ . '/data/foreach/array-object-type.php',
				'int(0)',
				'self::ARRAY_CONSTANT[0]',
			],
			[
				__DIR__ . '/data/foreach/array-object-type.php',
				'string',
				'self::MIXED_CONSTANT[1]',
			],
			[
				__DIR__ . '/data/foreach/nested-object-type.php',
				'AnotherNamespace\Foo',
				'$foo',
			],
			[
				__DIR__ . '/data/foreach/nested-object-type.php',
				'AnotherNamespace\Foo',
				'$foos[0]',
			],
			[
				__DIR__ . '/data/foreach/nested-object-type.php',
				'AnotherNamespace\Foo',
				'$fooses[0][0]',
			],
			[
				__DIR__ . '/data/foreach/integer-type.php',
				'int',
				'$integer',
			],
			[
				__DIR__ . '/data/foreach/reusing-specified-variable.php',
				'int(1)|int(2)|int(3)',
				'$business',
			],
			[
				__DIR__ . '/data/foreach/type-in-comment-variable-first.php',
				'mixed',
				'$value',
			],
			[
				__DIR__ . '/data/foreach/type-in-comment-variable-second.php',
				'stdClass',
				'$value',
			],
			[
				__DIR__ . '/data/foreach/type-in-comment-no-variable.php',
				'mixed',
				'$value',
			],
			[
				__DIR__ . '/data/foreach/type-in-comment-wrong-variable.php',
				'mixed',
				'$value',
			],
			[
				__DIR__ . '/data/foreach/type-in-comment-variable-with-reference.php',
				'string',
				'$value',
			],
			[
				__DIR__ . '/data/foreach/foreach-with-specified-key-type.php',
				'array<string, float|int|string>',
				'$list',
			],
			[
				__DIR__ . '/data/foreach/foreach-with-specified-key-type.php',
				'string',
				'$key',
			],
			[
				__DIR__ . '/data/foreach/foreach-with-specified-key-type.php',
				'float|int|string',
				'$value',
			],
			[
				__DIR__ . '/data/foreach/foreach-with-complex-value-type.php',
				'float|ForeachWithComplexValueType\Foo',
				'$value',
			],
			[
				__DIR__ . '/data/foreach/foreach-iterable-with-specified-key-type.php',
				'ForeachWithGenericsPhpDoc\Bar|ForeachWithGenericsPhpDoc\Foo',
				'$key',
			],
			[
				__DIR__ . '/data/foreach/foreach-iterable-with-specified-key-type.php',
				'float|int|string',
				'$value',
			],
			[
				__DIR__ . '/data/foreach/foreach-iterable-with-complex-value-type.php',
				'float|ForeachWithComplexValueType\Foo',
				'$value',
			],
		];
	}

	/**
	 * @dataProvider dataForeachArrayType
	 * @param string $file
	 * @param string $description
	 * @param string $expression
	 */
	public function testForeachArrayType(
		string $file,
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			$file,
			$description,
			$expression
		);
	}

	public function dataOverridingSpecifiedType(): array
	{
		return [
			[
				__DIR__ . '/data/catch-specified-variable.php',
				'TryCatchWithSpecifiedVariable\FooException',
				'$foo',
			],
		];
	}

	/**
	 * @dataProvider dataOverridingSpecifiedType
	 * @param string $file
	 * @param string $description
	 * @param string $expression
	 */
	public function testOverridingSpecifiedType(
		string $file,
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			$file,
			$description,
			$expression
		);
	}

	public function dataForeachObjectType(): array
	{
		return [
			[
				__DIR__ . '/data/foreach/object-type.php',
				'ObjectType\\MyKey',
				'$keyFromIterator',
				"'insideFirstForeach';",
			],
			[
				__DIR__ . '/data/foreach/object-type.php',
				'ObjectType\\MyValue',
				'$valueFromIterator',
				"'insideFirstForeach';",
			],
			[
				__DIR__ . '/data/foreach/object-type.php',
				'ObjectType\\MyKey',
				'$keyFromAggregate',
				"'insideSecondForeach';",
			],
			[
				__DIR__ . '/data/foreach/object-type.php',
				'ObjectType\\MyValue',
				'$valueFromAggregate',
				"'insideSecondForeach';",
			],
			[
				__DIR__ . '/data/foreach/object-type.php',
				'mixed', // *ERROR*
				'$keyFromRecursiveAggregate',
				"'insideThirdForeach';",
			],
			[
				__DIR__ . '/data/foreach/object-type.php',
				'mixed', // *ERROR*
				'$valueFromRecursiveAggregate',
				"'insideThirdForeach';",
			],
		];
	}

	/**
	 * @dataProvider dataForeachObjectType
	 * @param string $file
	 * @param string $description
	 * @param string $expression
	 * @param string $evaluatedPointExpression
	 */
	public function testForeachObjectType(
		string $file,
		string $description,
		string $expression,
		string $evaluatedPointExpression
	): void
	{
		$this->assertTypes(
			$file,
			$description,
			$expression,
			[],
			[],
			$evaluatedPointExpression
		);
	}

	public function dataArrayFunctions(): array
	{
		return [
			[
				'int(1)',
				'$integers[0]',
			],
			[
				'string',
				'$mappedStrings[0]',
			],
			[
				'int(1)|int(2)|int(3)',
				'$filteredIntegers[0]',
			],
			[
				'int(123)',
				'$filteredMixed[0]',
			],
			[
				'int(1)|int(2)|int(3)',
				'$uniquedIntegers[1]',
			],
			[
				'string',
				'$reducedIntegersToString',
			],
			[
				'int(1)|int(2)|int(3)',
				'$reversedIntegers[0]',
			],
			[
				'array<int(1)>',
				'$filledIntegers',
			],
			[
				'array<int(1)>',
				'$filledIntegersWithKeys',
			],
			[
				'array<int, int(1)|int(2)>',
				'array_keys($integerKeys)',
			],
			[
				'array<int, string>',
				'array_keys($stringKeys)',
			],
			[
				'array<int, int(1)|string>',
				'array_keys($stringOrIntegerKeys)',
			],
		];
	}

	/**
	 * @dataProvider dataArrayFunctions
	 * @param string $description
	 * @param string $expression
	 */
	public function testArrayFunctions(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/array-functions.php',
			$description,
			$expression
		);
	}

	public function dataSpecifiedTypesUsingIsFunctions(): array
	{
		return [
			[
				'int',
				'$integer',
			],
			[
				'int',
				'$anotherInteger',
			],
			[
				'int',
				'$longInteger',
			],
			[
				'float',
				'$float',
			],
			[
				'float',
				'$doubleFloat',
			],
			[
				'float',
				'$realFloat',
			],
			[
				'null',
				'$null',
			],
			[
				'array',
				'$array',
			],
			[
				'bool',
				'$bool',
			],
			[
				'callable',
				'$callable',
			],
			[
				'resource',
				'$resource',
			],
			[
				'int',
				'$yetAnotherInteger',
			],
			[
				'mixed',
				'$mixedInteger',
			],
			[
				'string',
				'$string',
			],
			[
				'object',
				'$object',
			],
			[
				'int',
				'$intOrStdClass',
			],
			[
				'Foo',
				'$foo',
			],
			[
				'Foo',
				'$anotherFoo',
			],
		];
	}

	/**
	 * @dataProvider dataSpecifiedTypesUsingIsFunctions
	 * @param string $description
	 * @param string $expression
	 */
	public function testSpecifiedTypesUsingIsFunctions(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/specifiedTypesUsingIsFunctions.php',
			$description,
			$expression
		);
	}

	public function dataIterable(): array
	{
		return [
			[
				'iterable',
				'$this->iterableProperty',
			],
			[
				'iterable',
				'$iterableSpecifiedLater',
			],
			[
				'iterable',
				'$iterableWithoutTypehint',
			],
			[
				'mixed',
				'$iterableWithoutTypehint[0]',
			],
			[
				'iterable',
				'$iterableWithIterableTypehint',
			],
			[
				'mixed',
				'$iterableWithIterableTypehint[0]',
			],
			[
				'mixed',
				'$mixed',
			],
			[
				'iterable<Iterables\Bar>',
				'$iterableWithConcreteTypehint',
			],
			[
				'mixed',
				'$iterableWithConcreteTypehint[0]',
			],
			[
				'Iterables\Bar',
				'$bar',
			],
			[
				'iterable',
				'$this->doBar()',
			],
			[
				'iterable<Iterables\Baz>',
				'$this->doBaz()',
			],
			[
				'Iterables\Baz',
				'$baz',
			],
			[
				'array',
				'$arrayWithIterableTypehint',
			],
			[
				'mixed',
				'$arrayWithIterableTypehint[0]',
			],
			[
				'iterable<Iterables\Bar>&Iterables\Collection',
				'$unionIterableType',
			],
			[
				'Iterables\Bar',
				'$unionBar',
			],
			[
				'array',
				'$mixedUnionIterableType',
			],
			[
				'iterable<Iterables\Bar>&Iterables\Collection',
				'$unionIterableIterableType',
			],
			[
				'mixed',
				'$mixedBar',
			],
			[
				'Iterables\Bar',
				'$iterableUnionBar',
			],
			[
				'Iterables\Bar',
				'$unionBarFromMethod',
			],
			[
				'iterable<string>',
				'$this->stringIterableProperty',
			],
			[
				'iterable',
				'$this->mixedIterableProperty',
			],
			[
				'iterable<int>',
				'$integers',
			],
			[
				'iterable',
				'$mixeds',
			],
			[
				'iterable',
				'$this->returnIterableMixed()',
			],
			[
				'iterable<string>',
				'$this->returnIterableString()',
			],
			[
				'int|iterable<string>',
				'$this->iterablePropertyAlsoWithSomethingElse',
			],
			[
				'int|iterable<int|string>',
				'$this->iterablePropertyWithTwoItemTypes',
			],
			[
				'array<string>|Iterables\CollectionOfIntegers',
				'$this->collectionOfIntegersOrArrayOfStrings',
			],
		];
	}

	/**
	 * @dataProvider dataIterable
	 * @param string $description
	 * @param string $expression
	 */
	public function testIterable(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/iterable.php',
			$description,
			$expression
		);
	}

	public function dataArrayAccess(): array
	{
		return [
			[
				'string',
				'$this->returnArrayOfStrings()[0]',
			],
			[
				'mixed',
				'$this->returnMixed()[0]',
			],
			[
				'int',
				'$this->returnSelfWithIterableInt()[0]',
			],
			[
				'int',
				'$this[0]',
			],
		];
	}

	/**
	 * @dataProvider dataArrayAccess
	 * @param string $description
	 * @param string $expression
	 */
	public function testArrayAccess(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/array-accessable.php',
			$description,
			$expression
		);
	}

	public function dataVoid(): array
	{
		return [
			[
				'void',
				'$this->doFoo()',
			],
			[
				'void',
				'$this->doBar()',
			],
			[
				'void',
				'$this->doConflictingVoid()',
			],
		];
	}

	/**
	 * @dataProvider dataVoid
	 * @param string $description
	 * @param string $expression
	 */
	public function testVoid(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/void.php',
			$description,
			$expression
		);
	}

	public function dataNullableReturnTypes(): array
	{
		return [
			[
				'int|null',
				'$this->doFoo()',
			],
			[
				'int|null',
				'$this->doBar()',
			],
			[
				'int|null',
				'$this->doConflictingNullable()',
			],
			[
				'int',
				'$this->doAnotherConflictingNullable()',
			],
		];
	}

	/**
	 * @dataProvider dataNullableReturnTypes
	 * @param string $description
	 * @param string $expression
	 */
	public function testNullableReturnTypes(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/nullable-returnTypes.php',
			$description,
			$expression
		);
	}

	public function dataTernary(): array
	{
		return [
			[
				'bool|null',
				'$boolOrNull',
			],
			[
				'bool',
				'$boolOrNull !== null ? $boolOrNull : false',
			],
			[
				'bool',
				'$bool',
			],
		];
	}

	/**
	 * @dataProvider dataTernary
	 * @param string $description
	 * @param string $expression
	 */
	public function testTernary(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/ternary.php',
			$description,
			$expression
		);
	}

	public function dataHeredoc(): array
	{
		return [
			[
				'string',
				'$heredoc',
			],
			[
				'string',
				'$nowdoc',
			],
		];
	}

	/**
	 * @dataProvider dataHeredoc
	 * @param string $description
	 * @param string $expression
	 */
	public function testHeredoc(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/heredoc.php',
			$description,
			$expression
		);
	}

	public function dataTypeElimination(): array
	{
		return [
			[
				'null',
				'$foo',
				"'nullForSure';",
			],
			[
				'TypeElimination\Foo',
				'$foo',
				"'notNullForSure';",
			],
			[
				'TypeElimination\Foo',
				'$foo',
				"'notNullForSure2';",
			],
			[
				'null',
				'$foo',
				"'nullForSure2';",
			],
			[
				'null',
				'$foo',
				"'nullForSure3';",
			],
			[
				'TypeElimination\Foo',
				'$foo',
				"'notNullForSure3';",
			],
			[
				'null',
				'$foo',
				"'yodaNullForSure';",
			],
			[
				'TypeElimination\Foo',
				'$foo',
				"'yodaNotNullForSure';",
			],
			[
				'false',
				'$intOrFalse',
				"'falseForSure';",
			],
			[
				'int',
				'$intOrFalse',
				"'intForSure';",
			],
			[
				'false',
				'$intOrFalse',
				"'yodaFalseForSure';",
			],
			[
				'int',
				'$intOrFalse',
				"'yodaIntForSure';",
			],
			[
				'true',
				'$intOrTrue',
				"'trueForSure';",
			],
			[
				'int',
				'$intOrTrue',
				"'anotherIntForSure';",
			],
			[
				'true',
				'$intOrTrue',
				"'yodaTrueForSure';",
			],
			[
				'int',
				'$intOrTrue',
				"'yodaAnotherIntForSure';",
			],
			[
				'TypeElimination\Foo',
				'$fooOrBarOrBaz',
				"'fooForSure';",
			],
			[
				'TypeElimination\Bar|TypeElimination\Baz',
				'$fooOrBarOrBaz',
				"'barOrBazForSure';",
			],
			[
				'TypeElimination\Bar',
				'$fooOrBarOrBaz',
				"'barForSure';",
			],
			[
				'TypeElimination\Baz',
				'$fooOrBarOrBaz',
				"'bazForSure';",
			],
			[
				'TypeElimination\Bar|TypeElimination\Baz',
				'$fooOrBarOrBaz',
				"'anotherBarOrBazForSure';",
			],
			[
				'TypeElimination\Foo',
				'$fooOrBarOrBaz',
				"'anotherFooForSure';",
			],
			[
				'string|null',
				'$result',
				"'stringOrNullForSure';",
			],
			[
				'int',
				'$intOrFalse',
				"'yetAnotherIntForSure';",
			],
			[
				'int',
				'$intOrTrue',
				"'yetYetAnotherIntForSure';",
			],
			[
				'TypeElimination\Foo|null',
				'$fooOrStringOrNull',
				"'fooOrNull';",
			],
			[
				'string',
				'$fooOrStringOrNull',
				"'stringForSure';",
			],
			[
				'string',
				'$fooOrStringOrNull',
				"'anotherStringForSure';",
			],
			[
				'null',
				'$this->bar',
				"'propertyNullForSure';",
			],
			[
				'TypeElimination\Bar',
				'$this->bar',
				"'propertyNotNullForSure';",
			],
		];
	}

	/**
	 * @dataProvider dataTypeElimination
	 * @param string $description
	 * @param string $expression
	 * @param string $evaluatedPointExpression
	 */
	public function testTypeElimination(
		string $description,
		string $expression,
		string $evaluatedPointExpression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/type-elimination.php',
			$description,
			$expression,
			[],
			[],
			$evaluatedPointExpression
		);
	}

	public function dataMisleadingTypes(): array
	{
		return [
			[
				'MisleadingTypes\boolean',
				'$foo->misleadingBoolReturnType()',
			],
			[
				'MisleadingTypes\integer',
				'$foo->misleadingIntReturnType()',
			],
			[
				'MisleadingTypes\mixed',
				'$foo->misleadingMixedReturnType()',
			],
		];
	}

	/**
	 * @dataProvider dataMisleadingTypes
	 * @param string $description
	 * @param string $expression
	 */
	public function testMisleadingTypes(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/misleading-types.php',
			$description,
			$expression
		);
	}

	public function dataMisleadingTypesWithoutNamespace(): array
	{
		return [
			[
				'boolean', // would have been "bool" for a real boolean
				'$foo->misleadingBoolReturnType()',
			],
			[
				'integer',
				'$foo->misleadingIntReturnType()',
			],
		];
	}

	/**
	 * @dataProvider dataMisleadingTypesWithoutNamespace
	 * @param string $description
	 * @param string $expression
	 */
	public function testMisleadingTypesWithoutNamespace(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/misleading-types-without-namespace.php',
			$description,
			$expression
		);
	}

	public function dataUnresolvableTypes(): array
	{
		return [
			[
				'mixed',
				'$arrayWithTooManyArgs',
			],
			[
				'mixed',
				'$iterableWithTooManyArgs',
			],
			[
				'mixed',
				'$genericFoo',
			],
		];
	}

	/**
	 * @dataProvider dataUnresolvableTypes
	 * @param string $description
	 * @param string $expression
	 */
	public function testUnresolvableTypes(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/unresolvable-types.php',
			$description,
			$expression
		);
	}

	public function dataCombineTypes(): array
	{
		return [
			[
				'string|null',
				'$x',
			],
			[
				'int(1)|null',
				'$y',
			],
		];
	}

	/**
	 * @dataProvider dataCombineTypes
	 * @param string $description
	 * @param string $expression
	 */
	public function testCombineTypes(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/combine-types.php',
			$description,
			$expression
		);
	}

	public function dataConstants(): array
	{
		return [
			[
				'int(1)',
				'$foo',
			],
			[
				'mixed',
				'NONEXISTENT_CONSTANT',
			],
		];
	}

	/**
	 * @dataProvider dataConstants
	 * @param string $description
	 * @param string $expression
	 */
	public function testConstants(
		string $description,
		string $expression
	): void
	{
		if (!defined('ConstantsForNodeScopeResolverTest\\FOO_CONSTANT')) {
			define('ConstantsForNodeScopeResolverTest\\FOO_CONSTANT', 1);
		}
		$this->assertTypes(
			__DIR__ . '/data/constants.php',
			$description,
			$expression
		);
	}

	public function dataFinally(): array
	{
		return [
			[
				'int(1)|string',
				'$integerOrString',
			],
			[
				'FinallyNamespace\BarException|FinallyNamespace\FooException|null',
				'$fooOrBarException',
			],
		];
	}

	/**
	 * @dataProvider dataFinally
	 * @param string $description
	 * @param string $expression
	 */
	public function testFinally(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/finally.php',
			$description,
			$expression
		);
	}

	/**
	 * @dataProvider dataFinally
	 * @param string $description
	 * @param string $expression
	 */
	public function testFinallyWithEarlyTermination(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/finally-with-early-termination.php',
			$description,
			$expression
		);
	}

	public function dataInheritDocFromInterface(): array
	{
		return [
			[
				'string',
				'$string',
			],
		];
	}

	/**
	 * @dataProvider dataInheritDocFromInterface
	 * @param string $description
	 * @param string $expression
	 */
	public function testInheritDocFromInterface(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/inheritdoc-from-interface.php',
			$description,
			$expression
		);
	}

	public function dataResolveStatic(): array
	{
		return [
			[
				'ResolveStatic\Foo',
				'\ResolveStatic\Foo::create()',
			],
			[
				'ResolveStatic\Bar',
				'\ResolveStatic\Bar::create()',
			],
		];
	}

	/**
	 * @dataProvider dataResolveStatic
	 * @param string $description
	 * @param string $expression
	 */
	public function testResolveStatic(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/resolve-static.php',
			$description,
			$expression
		);
	}

	public function dataLoopVariables(): array
	{
		return [
			[
				'LoopVariables\Foo|LoopVariables\Lorem|null',
				'$foo',
				"'begin';",
			],
			[
				'LoopVariables\Foo',
				'$foo',
				"'afterAssign';",
			],
			[
				'LoopVariables\Foo',
				'$foo',
				"'end';",
			],
			[
				'LoopVariables\Bar|LoopVariables\Foo|LoopVariables\Lorem|null',
				'$foo',
				"'afterLoop';",
			],
		];
	}

	public function dataForeachLoopVariables(): array
	{
		return [
			[
				'int(1)|int(2)|int(3)',
				'$val',
				"'begin';",
			],
			[
				'int(0)|int(1)|int(2)',
				'$key',
				"'begin';",
			],
			[
				'int(1)|int(2)|int(3)|null',
				'$val',
				"'afterLoop';",
			],
			[
				'int(0)|int(1)|int(2)|null',
				'$key',
				"'afterLoop';",
			],
			[
				'int(1)|int(2)|int(3)|null',
				'$emptyForeachVal',
				"'afterLoop';",
			],
			[
				'int(0)|int(1)|int(2)|null',
				'$emptyForeachKey',
				"'afterLoop';",
			],
		];
	}

	/**
	 * @dataProvider dataLoopVariables
	 * @dataProvider dataForeachLoopVariables
	 * @param string $description
	 * @param string $expression
	 * @param string $evaluatedPointExpression
	 */
	public function testForeachLoopVariables(
		string $description,
		string $expression,
		string $evaluatedPointExpression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/foreach-loop-variables.php',
			$description,
			$expression,
			[],
			[],
			$evaluatedPointExpression
		);
	}

	/**
	 * @dataProvider dataLoopVariables
	 * @param string $description
	 * @param string $expression
	 * @param string $evaluatedPointExpression
	 */
	public function testWhileLoopVariables(
		string $description,
		string $expression,
		string $evaluatedPointExpression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/while-loop-variables.php',
			$description,
			$expression,
			[],
			[],
			$evaluatedPointExpression
		);
	}

	/**
	 * @dataProvider dataLoopVariables
	 * @param string $description
	 * @param string $expression
	 * @param string $evaluatedPointExpression
	 */
	public function testForLoopVariables(
		string $description,
		string $expression,
		string $evaluatedPointExpression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/for-loop-variables.php',
			$description,
			$expression,
			[],
			[],
			$evaluatedPointExpression
		);
	}

	public function dataDoWhileLoopVariables(): array
	{
		return [
			[
				'LoopVariables\Foo|LoopVariables\Lorem|null',
				'$foo',
				"'begin';",
			],
			[
				'LoopVariables\Foo',
				'$foo',
				"'afterAssign';",
			],
			[
				'LoopVariables\Foo',
				'$foo',
				"'end';",
			],
			[
				'LoopVariables\Bar|LoopVariables\Foo|LoopVariables\Lorem',
				'$foo',
				"'afterLoop';",
			],
		];
	}

	/**
	 * @dataProvider dataDoWhileLoopVariables
	 * @param string $description
	 * @param string $expression
	 * @param string $evaluatedPointExpression
	 */
	public function testDoWhileLoopVariables(
		string $description,
		string $expression,
		string $evaluatedPointExpression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/do-while-loop-variables.php',
			$description,
			$expression,
			[],
			[],
			$evaluatedPointExpression
		);
	}

	public function dataMultipleClassesInOneFile(): array
	{
		return [
			[
				'MultipleClasses\Foo',
				'$self',
				"'Foo';",
			],
			[
				'MultipleClasses\Bar',
				'$self',
				"'Bar';",
			],
		];
	}

	/**
	 * @dataProvider dataMultipleClassesInOneFile
	 * @param string $description
	 * @param string $expression
	 * @param string $evaluatedPointExpression
	 */
	public function testMultipleClassesInOneFile(
		string $description,
		string $expression,
		string $evaluatedPointExpression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/multiple-classes-per-file.php',
			$description,
			$expression,
			[],
			[],
			$evaluatedPointExpression
		);
	}

	public function dataCallingMultipleClassesInOneFile(): array
	{
		return [
			[
				'MultipleClasses\Foo',
				'$foo->returnSelf()',
			],
			[
				'MultipleClasses\Bar',
				'$bar->returnSelf()',
			],
		];
	}

	/**
	 * @dataProvider dataCallingMultipleClassesInOneFile
	 * @param string $description
	 * @param string $expression
	 */
	public function testCallingMultipleClassesInOneFile(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/calling-multiple-classes-per-file.php',
			$description,
			$expression
		);
	}

	public function dataExplode(): array
	{
		return [
			[
				'array<int, string>',
				'$sureArray',
			],
			[
				'false',
				'$sureFalse',
			],
			[
				'array<int,string>|false',
				'$arrayOrFalse',
			],
			/*[
				'array<int,string>|false',
				'$anotherArrayOrFalse',
			],*/
		];
	}

	/**
	 * @dataProvider dataExplode
	 * @param string $description
	 * @param string $expression
	 */
	public function testExplode(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/explode.php',
			$description,
			$expression
		);
	}

	public function dataReplaceFunctions(): array
	{
		return [
			[
				'string',
				'$expectedString',
			],
			[
				'string',
				'$anotherExpectedString',
			],
			[
				'array<string, string>',
				'$expectedArray',
			],
			[
				'array<string, string>',
				'$anotherExpectedArray',
			],
			[
				'array|string',
				'$expectedArrayOrString',
			],
			[
				'array|string',
				'$anotherExpectedArrayOrString',
			],
		];
	}

	/**
	 * @dataProvider dataReplaceFunctions
	 * @param string $description
	 * @param string $expression
	 */
	public function testReplaceFunctions(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/replaceFunctions.php',
			$description,
			$expression
		);
	}

	private function assertTypes(
		string $file,
		string $description,
		string $expression,
		array $dynamicMethodReturnTypeExtensions = [],
		array $dynamicStaticMethodReturnTypeExtensions = [],
		string $evaluatedPointExpression = 'die;'
	): void
	{
		$this->processFile($file, function (\PhpParser\Node $node, Scope $scope) use ($description, $expression, $evaluatedPointExpression): void {
			$printer = new \PhpParser\PrettyPrinter\Standard();
			$printedNode = $printer->prettyPrint([$node]);
			if ($printedNode === $evaluatedPointExpression) {
				/** @var \PhpParser\Node\Expr $expressionNode */
				$expressionNode = $this->getParser()->parseString(sprintf('<?php %s;', $expression))[0];
				$type = $scope->getType($expressionNode);
				$this->assertTypeDescribe(
					$description,
					$type->describe(),
					sprintf('%s at %s', $expression, $evaluatedPointExpression)
				);
			}
		}, $dynamicMethodReturnTypeExtensions, $dynamicStaticMethodReturnTypeExtensions);
	}

	private function processFile(string $file, \Closure $callback, array $dynamicMethodReturnTypeExtensions = [], array $dynamicStaticMethodReturnTypeExtensions = []): void
	{
		$phpDocStringResolver = $this->getContainer()->getByType(PhpDocStringResolver::class);

		$printer = new \PhpParser\PrettyPrinter\Standard();
		$resolver = new NodeScopeResolver(
			$this->createBroker(),
			$this->getParser(),
			$printer,
			new FileTypeMapper($this->getParser(), $phpDocStringResolver, $this->createMock(Cache::class)),
			new FileHelper('/'),
			true,
			true,
			[
				\EarlyTermination\Foo::class => [
					'doFoo',
					'doBar',
				],
			]
		);
		$resolver->processNodes(
			$this->getParser()->parseFile($file),
			new Scope(
				$this->createBroker($dynamicMethodReturnTypeExtensions, $dynamicStaticMethodReturnTypeExtensions),
				$printer,
				new TypeSpecifier($printer),
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
	public function testDeclareStrictTypes(string $file, bool $result): void
	{
		$this->processFile($file, function (\PhpParser\Node $node, Scope $scope) use ($result): void {
			if ($node instanceof Exit_) {
				$this->assertSame($result, $scope->isDeclareStrictTypes());
			}
		});
	}

	public function testEarlyTermination(): void
	{
		$this->processFile(__DIR__ . '/data/early-termination.php', function (\PhpParser\Node $node, Scope $scope): void {
			if ($node instanceof Exit_) {
				$this->assertTrue($scope->hasVariableType('something')->yes());
				$this->assertTrue($scope->hasVariableType('var')->yes());
				$this->assertTrue($scope->hasVariableType('foo')->no());
			}
		});
	}

	private function assertTypeDescribe(string $expectedDescription, string $actualDescription, string $label = ''): void
	{
		$this->assertSame(
			$expectedDescription,
			$actualDescription,
			$label
		);
	}

}
