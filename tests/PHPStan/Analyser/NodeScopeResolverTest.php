<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Broker\AnonymousClassNameHelper;
use PHPStan\Cache\Cache;
use PHPStan\File\FileHelper;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Tests\AssertionClassMethodTypeSpecifyingExtension;
use PHPStan\Tests\AssertionClassStaticMethodTypeSpecifyingExtension;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\ErrorType;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use SomeNodeScopeResolverNamespace\Foo;

class NodeScopeResolverTest extends \PHPStan\Testing\TestCase
{

	public function testClassMethodScope(): void
	{
		$this->processFile(__DIR__ . '/data/class.php', function (\PhpParser\Node $node, Scope $scope): void {
			if (!($node instanceof Exit_)) {
				return;
			}

			$this->assertSame('SomeNodeScopeResolverNamespace', $scope->getNamespace());
			$this->assertTrue($scope->isInClass());
			$this->assertSame(Foo::class, $scope->getClassReflection()->getName());
			$this->assertSame('doFoo', $scope->getFunctionName());
			$this->assertSame('$this(SomeNodeScopeResolverNamespace\Foo)', $scope->getVariableType('this')->describe(VerbosityLevel::value()));
			$this->assertTrue($scope->hasVariableType('baz')->yes());
			$this->assertTrue($scope->hasVariableType('lorem')->yes());
			$this->assertFalse($scope->hasVariableType('ipsum')->yes());
			$this->assertTrue($scope->hasVariableType('i')->yes());
			$this->assertTrue($scope->hasVariableType('val')->yes());
			$this->assertSame('SomeNodeScopeResolverNamespace\InvalidArgumentException', $scope->getVariableType('exception')->describe(VerbosityLevel::value()));
			$this->assertTrue($scope->hasVariableType('staticVariable')->yes());
		});
	}

	private function getFileScope(string $filename): Scope
	{
		/** @var \PHPStan\Analyser\Scope $testScope */
		$testScope = null;
		$this->processFile($filename, function (\PhpParser\Node $node, Scope $scope) use (&$testScope): void {
			if (!($node instanceof Exit_)) {
				return;
			}

			$testScope = $scope;
		});

		return $testScope;
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
				'*ERROR*',
				'$this->union->bar',
			],
			[
				'UnionIntersection\Foo',
				'$foo->foo',
			],
			[
				'*ERROR*',
				'$foo->bar',
			],
			[
				'UnionIntersection\Foo',
				'$this->union->doFoo()',
			],
			[
				'*ERROR*',
				'$this->union->doBar()',
			],
			[
				'UnionIntersection\Foo',
				'$foo->doFoo()',
			],
			[
				'*ERROR*',
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
				'1',
				'$this->union::FOO_CONSTANT',
			],
			[
				'*ERROR*',
				'$this->union::BAR_CONSTANT',
			],
			[
				'1',
				'$foo::FOO_CONSTANT',
			],
			[
				'*ERROR*',
				'$foo::BAR_CONSTANT',
			],
			[
				'1',
				'$foobar::FOO_CONSTANT',
			],
			[
				'1',
				'$foobar::BAR_CONSTANT',
			],
			[
				'\'foo\'',
				'self::IPSUM_CONSTANT',
			],
			[
				'array(1, 2, 3)',
				'parent::PARENT_CONSTANT',
			],
			[
				'UnionIntersection\Foo',
				'$foo::doStaticFoo()',
			],
			[
				'*ERROR*',
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
				'*ERROR*',
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

	public function dataAssignInIf(): array
	{
		$testScope = $this->getFileScope(__DIR__ . '/data/if.php');

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
				'1',
			],
			[
				$testScope,
				'callParameter',
				TrinaryLogic::createYes(),
				'3',
			],
			[
				$testScope,
				'arrOne',
				TrinaryLogic::createYes(),
				'array(\'one\')',
			],
			[
				$testScope,
				'arrTwo',
				TrinaryLogic::createYes(),
				'array(\'test\' => \'two\', 0 => Foo)',
			],
			[
				$testScope,
				'arrThree',
				TrinaryLogic::createYes(),
				'array(\'three\')',
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
				'1',
			],
			[
				$testScope,
				'i',
				TrinaryLogic::createYes(),
				'int',
			],
			[
				$testScope,
				'f',
				TrinaryLogic::createMaybe(),
				'int',
			],
			[
				$testScope,
				'anotherF',
				TrinaryLogic::createYes(),
				'int',
			],
			[
				$testScope,
				'matches',
				TrinaryLogic::createYes(),
				'mixed',
			],
			[
				$testScope,
				'anotherArray',
				TrinaryLogic::createYes(),
				'array(\'test\' => array(\'another\'))',
			],
			[
				$testScope,
				'ifVar',
				TrinaryLogic::createYes(),
				'1|2|3',
			],
			[
				$testScope,
				'ifNotVar',
				TrinaryLogic::createMaybe(),
				'1|2',
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
				'1|2|3',
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
				'mixed',
			],
			[
				$testScope,
				'inTry',
				TrinaryLogic::createYes(),
				'1',
			],
			[
				$testScope,
				'matches3',
				TrinaryLogic::createYes(),
				'mixed',
			],
			[
				$testScope,
				'matches4',
				TrinaryLogic::createMaybe(),
				'mixed',
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
				'1',
			],
			[
				$testScope,
				'switchVar',
				TrinaryLogic::createYes(),
				'1|2|3',
			],
			[
				$testScope,
				'noSwitchVar',
				TrinaryLogic::createMaybe(),
				'1',
			],
			[
				$testScope,
				'anotherNoSwitchVar',
				TrinaryLogic::createMaybe(),
				'1',
			],
			[
				$testScope,
				'inTryTwo',
				TrinaryLogic::createYes(),
				'1',
			],
			[
				$testScope,
				'ternaryMatches',
				TrinaryLogic::createYes(),
				'mixed',
			],
			[
				$testScope,
				'previousI',
				TrinaryLogic::createYes(),
				'int',
			],
			[
				$testScope,
				'previousJ',
				TrinaryLogic::createYes(),
				'0',
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
				'1',
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
				'1|1.0',
			],
			[
				$testScope,
				'nullableIntegerFromTryCatch',
				TrinaryLogic::createYes(),
				'1|null',
			],
			[
				$testScope,
				'anotherNullableIntegerFromTryCatch',
				TrinaryLogic::createYes(),
				'1|null',
			],
			[
				$testScope,
				'nullableIntegers',
				TrinaryLogic::createYes(),
				'array(1, 2, 3, null)',
			],
			[
				$testScope,
				'union',
				TrinaryLogic::createYes(),
				'array(1, 2, 3, \'foo\')',
				'1|2|3|\'foo\'',
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
				'1',
			],
			[
				$testScope,
				'integerOrNullFromFor',
				TrinaryLogic::createYes(),
				'1|null',
			],
			[
				$testScope,
				'nonexistentVariableOutsideWhile',
				TrinaryLogic::createMaybe(),
				'1',
			],
			[
				$testScope,
				'integerOrNullFromWhile',
				TrinaryLogic::createYes(),
				'1|null',
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
				'1|null',
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
				'\'str\'|int',
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
				'2|null',
			],
			[
				$testScope,
				'arrayOfIntegers',
				TrinaryLogic::createYes(),
				'array(1, 2, 3)',
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
				'2.0',
			],
			[
				$testScope,
				'someVariableThatWillGetOverrideInFinally',
				TrinaryLogic::createYes(),
				'\'foo\'',
			],
			[
				$testScope,
				'maybeDefinedButLaterCertainlyDefined',
				TrinaryLogic::createYes(),
				'2|3',
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
				'1|null',
			],
			[
				$testScope,
				'exceptionFromTryCatch',
				TrinaryLogic::createYes(),
				'(AnotherException&Throwable)|(Throwable&YetAnotherException)|null',
			],
		];
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
		?string $typeDescription = null,
		?string $iterableValueTypeDescription = null
	): void
	{
		$this->assertVariables(
			$scope,
			$variableName,
			$expectedCertainty,
			$typeDescription,
			$iterableValueTypeDescription
		);
	}

	public function dataConstantTypes(): array
	{
		$testScope = $this->getFileScope(__DIR__ . '/data/constantTypes.php');

		return [
			[
				$testScope,
				'postIncrement',
				'2',
			],
			[
				$testScope,
				'postDecrement',
				'4',
			],
			[
				$testScope,
				'preIncrement',
				'2',
			],
			[
				$testScope,
				'preDecrement',
				'4',
			],
			[
				$testScope,
				'literalArray',
				'array(\'a\' => 2, \'b\' => 4, \'c\' => 2, \'d\' => 4)',
			],
			[
				$testScope,
				'nullIncremented',
				'1',
			],
			[
				$testScope,
				'nullDecremented',
				'null',
			],
			[
				$testScope,
				'incrementInIf',
				'1|2|3',
			],
			[
				$testScope,
				'anotherIncrementInIf',
				'2|3',
			],
			[
				$testScope,
				'valueOverwrittenInIf',
				'1|2',
			],
			[
				$testScope,
				'incrementInForLoop',
				'int',
			],
			[
				$testScope,
				'valueOverwrittenInForLoop',
				'1|2',
			],
			[
				$testScope,
				'arrayOverwrittenInForLoop',
				'array(\'a\' => int, \'b\' => \'bar\'|\'foo\')',
			],
			[
				$testScope,
				'anotherValueOverwrittenInIf',
				'5|10',
			],
			[
				$testScope,
				'intProperty',
				'int',
			],
			[
				$testScope,
				'staticIntProperty',
				'int',
			],
			[
				$testScope,
				'anotherIntProperty',
				'1|2',
			],
			[
				$testScope,
				'anotherStaticIntProperty',
				'1|2',
			],
		];
	}

	/**
	 * @dataProvider dataConstantTypes
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param string $variableName
	 * @param string $typeDescription
	 */
	public function testConstantTypes(
		Scope $scope,
		string $variableName,
		string $typeDescription
	): void
	{
		$this->assertVariables(
			$scope,
			$variableName,
			TrinaryLogic::createYes(),
			$typeDescription,
			null
		);
	}

	private function assertVariables(
		Scope $scope,
		string $variableName,
		TrinaryLogic $expectedCertainty,
		?string $typeDescription = null,
		?string $iterableValueTypeDescription = null
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
				$scope->getVariableType($variableName)->describe(VerbosityLevel::value()),
				sprintf('Type of variable $%s does not match the expected one.', $variableName)
			);

			if ($iterableValueTypeDescription !== null) {
				$this->assertSame(
					$iterableValueTypeDescription,
					$scope->getVariableType($variableName)->getIterableValueType()->describe(VerbosityLevel::value()),
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
			if (!($node instanceof Exit_)) {
				return;
			}

			$this->assertTrue($scope->hasVariableType('a')->yes());
			$this->assertTrue($scope->hasVariableType('b')->yes());
			$this->assertTrue($scope->hasVariableType('c')->yes());
			$this->assertTrue($scope->hasVariableType('d')->yes());
			$this->assertTrue($scope->hasVariableType('e')->yes());
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
			[
				'TypesNamespaceCasts\Foo',
				'$castedFoo',
			],
			[
				'stdClass|TypesNamespaceCasts\Foo',
				'$castedArrayOrObject',
			],
			[
				'0|1',
				'(int) $bool',
			],
			[
				'0.0|1.0',
				'(float) $bool',
			],
			[
				'*ERROR*',
				'(int) $foo',
			],
			[
				'true',
				'(bool) $foo',
			],
			[
				'1',
				'(int) true',
			],
			[
				'0',
				'(int) false',
			],
			[
				'5',
				'(int) 5.25',
			],
			[
				'5.0',
				'(float) 5',
			],
			[
				'5',
				'(int) "5"',
			],
			[
				'5.0',
				'(float) "5"',
			],
			[
				'*ERROR*',
				'(int) "blabla"',
			],
			[
				'*ERROR*',
				'(float) "blabla"',
			],
			[
				'0',
				'(int) null',
			],
			[
				'0.0',
				'(float) null',
			],
			[
				'int',
				'(int) $str',
			],
			[
				'float',
				'(float) $str',
			],
			[
				'array(\'\' . "\0" . \'TypesNamespaceCasts\\\\Foo\' . "\0" . \'foo\' => TypesNamespaceCasts\Foo, \'\' . "\0" . \'TypesNamespaceCasts\\\\Foo\' . "\0" . \'int\' => int, \'\' . "\0" . \'*\' . "\0" . \'protectedInt\' => int, \'publicInt\' => int, \'\' . "\0" . \'TypesNamespaceCasts\\\\Bar\' . "\0" . \'barProperty\' => TypesNamespaceCasts\Bar)',
				'(array) $foo',
			],
			[
				'array(1, 2, 3)',
				'(array) [1, 2, 3]',
			],
			[
				'array(1)',
				'(array) 1',
			],
			[
				'array(1.0)',
				'(array) 1.0',
			],
			[
				'array(true)',
				'(array) true',
			],
			[
				'array(\'blabla\')',
				'(array) "blabla"',
			],
			[
				'array(int)',
				'(array) $castedInteger',
			],
			[
				'array<string, DateTimeImmutable>',
				'(array) $iterable',
			],
			[
				'array',
				'(array) new stdClass()',
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
				'1',
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
				'\'foo\'',
				'$stringLiteral',
			],
			[
				'1.0',
				'$floatLiteral',
			],
			[
				'1.0',
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
				'array()',
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
				'1',
				'\TypesNamespaceDeductedTypes\Foo::INTEGER_CONSTANT',
			],
			[
				'1',
				'self::INTEGER_CONSTANT',
			],
			[
				'1.0',
				'self::FLOAT_CONSTANT',
			],
			[
				'\'foo\'',
				'self::STRING_CONSTANT',
			],
			[
				'array()',
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
				'1',
				'$foo::INTEGER_CONSTANT',
			],
			[
				'1.0',
				'$foo::FLOAT_CONSTANT',
			],
			[
				'\'foo\'',
				'$foo::STRING_CONSTANT',
			],
			[
				'array()',
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
				'*ERROR*',
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
				'array',
				'$this->yetAnotherAnotherMixedParameter',
			],
			[
				'mixed',
				'$this->yetAnotherAnotherAnotherMixedParameter',
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
		$typeCallback = function ($value): string {
			if (is_int($value)) {
				return (new ConstantIntegerType($value))->describe(VerbosityLevel::value());
			} elseif (is_float($value)) {
				return (new ConstantFloatType($value))->describe(VerbosityLevel::value());
			} elseif (is_bool($value)) {
				return (new ConstantBooleanType($value))->describe(VerbosityLevel::value());
			} elseif (is_string($value)) {
				return (new ConstantStringType($value))->describe(VerbosityLevel::value());
			}

			throw new \PHPStan\ShouldNotHappenException();
		};

		return [
			[
				'false',
				'true && false',
			],
			[
				'true',
				'true || false',
			],
			[
				'true',
				'true xor false',
			],
			[
				'true',
				'false xor true',
			],
			[
				'false',
				'true xor true',
			],
			[
				'false',
				'true xor true',
			],
			[
				'bool',
				'$bool xor true',
			],
			[
				'bool',
				'$bool xor false',
			],
			[
				'false',
				'true and false',
			],
			[
				'true',
				'true or false',
			],
			[
				'false',
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
			[
				'*ERROR*',
				'+"blabla"',
			],
			[
				'123.2',
				'+"123.2"',
			],
			[
				'*ERROR*',
				'-"blabla"',
			],
			[
				'-5',
				'-5',
			],
			[
				'5',
				'-(-5)',
			],
			[
				'int',
				'-$integer',
			],
			[
				'-2|-1',
				'-$conditionalInt',
			],
			[
				'*ERROR*',
				'-$string',
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
				'float|int',
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
			[
				'int',
				'$otherInteger + 1',
			],
			[
				'float',
				'$otherInteger + 1.0',
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
				'int',
				'$float %= 2.4',
			],
			[
				'float',
				'$float **= 2.4',
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
				'1',
				'true + false',
			],
			// string
			[
				"'ab'",
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
				'float|int',
				'1 + $mixed',
			],
			[
				'float|int',
				'1 + $number',
			],
			[
				'float|int',
				'$integer + $number',
			],
			[
				'float',
				'$float + $float',
			],
			[
				'float',
				'$float + $number',
			],
			[
				'float|int',
				'1 / $mixed',
			],
			[
				'float|int',
				'1 / $number',
			],
			[
				'float',
				'1.0 / $mixed',
			],
			[
				'float',
				'1.0 / $number',
			],
			[
				'float|int',
				'$mixed / 1',
			],
			[
				'float|int',
				'$number / 1',
			],
			[
				'float',
				'$mixed / 1.0',
			],
			[
				'float',
				'$number / 1.0',
			],
			[
				'float',
				'1.0 + $mixed',
			],
			[
				'float',
				'1.0 + $number',
			],
			[
				'float|int',
				'$mixed + 1',
			],
			[
				'float|int',
				'$number + 1',
			],
			[
				'float',
				'$mixed + 1.0',
			],
			[
				'float',
				'$number + 1.0',
			],
			[
				'\'foo\'|null',
				'$mixed ? "foo" : null',
			],
			[
				'12',
				'12 ?: null',
			],
			[
				'1',
				'true ? 1 : 2',
			],
			[
				'2',
				'false ? 1 : 2',
			],
			[
				'12|string',
				'$string ?: 12',
			],
			[
				'12|string',
				'$stringOrNull ?: 12',
			],
			[
				'int',
				'$integer ?: 12',
			],
			[
				'\'foo\'',
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
				'\'Foo\'',
				'\Foo::class',
			],
			[
				'72',
				'$line',
			],
			[
				(new ConstantStringType(__DIR__))->describe(VerbosityLevel::value()),
				'$dir',
			],
			[
				(new ConstantStringType(__DIR__))->describe(VerbosityLevel::value()),
				'$file',
			],
			[
				'\'BinaryOperations\'',
				'$namespace',
			],
			[
				'\'BinaryOperations\\\\Foo\'',
				'$class',
			],
			[
				'\'BinaryOperations…\'',
				'$method',
			],
			[
				'\'doFoo\'',
				'$function',
			],
			[
				'1',
				'min([1, 2, 3])',
			],
			[
				'array(1, 2, 3)',
				'min([1, 2, 3], [4, 5, 5])',
			],
			[
				'1',
				'min(...[1, 2, 3])',
			],
			[
				'1',
				'min(...[2, 3, 4], ...[5, 1, 8])',
			],
			[
				'0',
				'min(0, ...[1, 2, 3])',
			],
			[
				'array(5, 6, 9)',
				'max([1, 10, 8], [5, 6, 9])',
			],
			[
				'array(1, 1, 1, 1)',
				'max(array(2, 2, 2), array(1, 1, 1, 1))',
			],
			[
				'array<int>',
				'max($arrayOfUnknownIntegers, $arrayOfUnknownIntegers)',
			],
			/*[
				'array(1, 1, 1, 1)',
				'max(array(2, 2, 2), 5, array(1, 1, 1, 1))',
			],
			[
				'array<int>',
				'max($arrayOfUnknownIntegers, $integer, $arrayOfUnknownIntegers)',
			],*/
			[
				'1.1',
				'min(...[1.1, 2.2, 3.3])',
			],
			[
				'1.1',
				'min(...[1.1, 2, 3])',
			],
			[
				'3',
				'max(...[1, 2, 3])',
			],
			[
				'3.3',
				'max(...[1.1, 2.2, 3.3])',
			],
			[
				'1',
				'min(1, 2, 3)',
			],
			[
				'3',
				'max(1, 2, 3)',
			],
			[
				'1.1',
				'min(1.1, 2.2, 3.3)',
			],
			[
				'3.3',
				'max(1.1, 2.2, 3.3)',
			],
			[
				'1',
				'min(1, 1)',
			],
			[
				'*ERROR*',
				'min(1)',
			],
			[
				'int|string',
				'min($integer, $string)',
			],
			[
				'int|string',
				'min([$integer, $string])',
			],
			[
				'int|string',
				'min(...[$integer, $string])',
			],
			[
				'\'a\'',
				'min(\'a\', \'b\')',
			],
			[
				'DateTimeImmutable',
				'max(new \DateTimeImmutable("today"), new \DateTimeImmutable("tomorrow"))',
			],
			[
				'1',
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
				'3',
				'count($arrayOfIntegers)',
			],
			[
				'6',
				'count($arrayOfIntegers) + count($arrayOfIntegers)',
			],
			[
				'bool',
				'$string === "foo"',
			],
			[
				'true',
				'$fooString === "foo"',
			],
			[
				'bool',
				'$string !== "foo"',
			],
			[
				'false',
				'$fooString !== "foo"',
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
				'$foo instanceof \BinaryOperations\Foo',
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
				'array(int, int, int)',
				'$arrayOfIntegers + $arrayOfIntegers',
			],
			[
				'array(int, int, int)',
				'$arrayOfIntegers += $arrayOfIntegers',
			],
			[
				'array(0 => 1, 1 => 1, 2 => 1, 3 => 1|2, 4 => 1|3, ?5 => 2, ?6 => 3)',
				'$conditionalArray + $unshiftedConditionalArray',
			],
			[
				'array(0 => \'lorem\', 1 => stdClass, 2 => 1, 3 => 1, 4 => 1, ?5 => 2, ?6 => 3)',
				'$unshiftedConditionalArray + $conditionalArray',
			],
			[
				'array(int, int, int)',
				'$arrayOfIntegers += ["foo"]',
			],
			[
				'*ERROR*',
				'$arrayOfIntegers += "foo"',
			],
			[
				'3',
				'@count($arrayOfIntegers)',
			],
			[
				'array(int, int, int)',
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
			[
				'bool|string',
				'highlight_string()',
			],
			[
				'bool',
				'highlight_string($string)',
			],
			[
				'bool',
				'highlight_string($string, false)',
			],
			[
				'string',
				'highlight_string($string, true)',
			],
			[
				'bool|string',
				'highlight_file()',
			],
			[
				'bool',
				'highlight_file($string)',
			],
			[
				'bool',
				'highlight_file($string, false)',
			],
			[
				'string',
				'highlight_file($string, true)',
			],
			[
				'1',
				'$one++',
			],
			[
				'1',
				'$one--',
			],
			[
				'2',
				'++$one',
			],
			[
				'0',
				'--$one',
			],
			[
				'3',
				'count($array)',
			],
			[
				'int',
				'count()',
			],
			[
				'int',
				'count($appendingToArrayInBranches)',
			],
			[
				'3|5',
				'count($conditionalArray)',
			],
			[
				'2',
				'$array[1]',
			],
			[
				'float|int',
				'$integer / $integer',
			],
			[
				'float|int',
				'$otherInteger / $integer',
			],
			[
				'float|int',
				'$mixed + 1',
			],
			[
				'float',
				'$mixed + 1.0',
			],
			[
				'float|int',
				'$mixed + $mixed',
			],
			[
				'*ERROR*',
				'$mixed + []',
			],
			[
				'124',
				'1 + "123"',
			],
			[
				'124.2',
				'1 + "123.2"',
			],
			[
				'*ERROR*',
				'1 + $string',
			],
			[
				'*ERROR*',
				'1 + "blabla"',
			],
			[
				'array(1, 2, 3)',
				'[1, 2, 3] + [4, 5, 6]',
			],
			[
				'array<int>',
				'$arrayOfUnknownIntegers + [1, 2, 3]',
			],
			[
				'1',
				'5 & 3',
			],
			[
				'int',
				'$integer & 3',
			],
			[
				'*ERROR*',
				'"bla" & 3',
			],
			[
				'7',
				'5 | 3',
			],
			[
				'int',
				'$integer | 3',
			],
			[
				'*ERROR*',
				'"bla" | 3',
			],
			[
				'6',
				'5 ^ 3',
			],
			[
				'int',
				'$integer ^ 3',
			],
			[
				'*ERROR*',
				'"bla" ^ 3',
			],
			[
				'int',
				'$integer &= 3',
			],
			[
				'*ERROR*',
				'$string &= 3',
			],
			[
				'int',
				'$integer |= 3',
			],
			[
				'*ERROR*',
				'$string |= 3',
			],
			[
				'int',
				'$integer ^= 3',
			],
			[
				'*ERROR*',
				'$string ^= 3',
			],
			[
				'\'f\'',
				'$fooString[0]',
			],
			[
				'*ERROR*',
				'$fooString[4]',
			],
			[
				'string',
				'$fooString[$integer]',
			],
			[
				'\'foo   bar\'',
				'$foobarString',
			],
			[
				'\'foo bar\'',
				'"$fooString bar"',
			],
			[
				'*ERROR*',
				'"$std bar"',
			],
			[
				'array<\'foo\'|int|stdClass>',
				'$arrToPush',
			],
			[
				'array<\'foo\'|int|stdClass>',
				'$arrToPush2',
			],
			[
				'array(0 => \'lorem\', 1 => 5, \'foo\' => stdClass, 2 => \'test\')',
				'$arrToUnshift',
			],
			[
				'array<\'lorem\'|int|stdClass>',
				'$arrToUnshift2',
			],
			[
				'array(0 => \'lorem\', 1 => stdClass, 2 => 1, 3 => 1, 4 => 1, ?5 => 2, ?6 => 3)',
				'$unshiftedConditionalArray',
			],
			[
				'array(\'dirname\' => string, \'basename\' => string, \'extension\' => string, \'filename\' => string)',
				'pathinfo($string)',
			],
			[
				'string',
				'pathinfo($string, PATHINFO_DIRNAME)',
			],
			[
				'string',
				'$string++',
			],
			[
				'string',
				'$string--',
			],
			[
				'string',
				'++$string',
			],
			[
				'string',
				'--$string',
			],
			[
				'string',
				'$incrementedString',
			],
			[
				'string',
				'$decrementedString',
			],
			[
				'\'foo\'',
				'$fooString++',
			],
			[
				'\'foo\'',
				'$fooString--',
			],
			[
				'\'fop\'',
				'++$fooString',
			],
			[
				'\'foo\'',
				'--$fooString',
			],
			[
				'\'fop\'',
				'$incrementedFooString',
			],
			[
				'\'foo\'',
				'$decrementedFooString',
			],
			[
				'\'barbar\'|\'barfoo\'|\'foobar\'|\'foofoo\'',
				'$conditionalString . $conditionalString',
			],
			[
				'\'baripsum\'|\'barlorem\'|\'fooipsum\'|\'foolorem\'',
				'$conditionalString . $anotherConditionalString',
			],
			[
				'\'ipsumbar\'|\'ipsumfoo\'|\'lorembar\'|\'loremfoo\'',
				'$anotherConditionalString . $conditionalString',
			],
			[
				'6|8',
				'count($conditionalArray) + count($array)',
			],
			[
				'bool',
				'is_numeric($string)',
			],
			[
				'false',
				'is_numeric($fooString)',
			],
			[
				'bool',
				'is_int($mixed)',
			],
			[
				'true',
				'is_int($integer)',
			],
			[
				'false',
				'is_int($string)',
			],
			[
				'array(2, 3)',
				'$arrToShift',
			],
			[
				'array(1, 2)',
				'$arrToPop',
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
				'0',
				'$integers[0]',
			],
			[
				'1',
				'$integers[1]',
			],
			[
				'\'foo\'',
				'$strings[0]',
			],
			[
				'*ERROR*',
				'$emptyArray[0]',
			],
			[
				'0',
				'$mixedArray[0]',
			],
			[
				'bool',
				'$integers[0] >= $integers[1] - 1',
			],
			[
				'array(\'foo\' => array(\'foo\' => array(\'foo\' => \'bar\')), \'bar\' => array(), \'baz\' => array(\'lorem\' => array()))',
				'$nestedArray',
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
				'0|1|2',
				"'NoKeysArray'",
			],
			[
				'0|1|2',
				"'IntegersAndNoKeysArray'",
			],
			[
				'0|1|\'foo\'',
				"'StringsAndNoKeysArray'",
			],
			[
				'1|2|3',
				"'IntegersAsStringsAndNoKeysArray'",
			],
			[
				'1|2',
				"'IntegersAsStringsArray'",
			],
			[
				'1|2',
				"'IntegersArray'",
			],
			[
				'1|2|3',
				"'IntegersWithFloatsArray'",
			],
			[
				'\'bar\'|\'foo\'',
				"'StringsArray'",
			],
			[
				'\'\'|\'bar\'|\'baz\'',
				"'StringsWithNullArray'",
			],
			[
				'1|2|string',
				"'IntegersWithStringFromMethodArray'",
			],
			[
				'1|2|\'foo\'',
				"'IntegersAndStringsArray'",
			],
			[
				'0|1',
				"'BooleansArray'",
			],
			[
				'int|string',
				"'UnknownConstantArray'",
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
				'MethodPhpDocsNamespace\Bar|MethodPhpDocsNamespace\Foo',
				'$unionTypeParameter',
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
				'*ERROR*',
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
			[
				'MethodPhpDocsNamespace\Foo',
				'$inlineSelf',
			],
			[
				'MethodPhpDocsNamespace\Bar',
				'$inlineBar',
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
	public function testTypeFromTraitPhpDocs(
		string $description,
		string $expression,
		bool $replaceClass = true
	): void
	{
		$description = str_replace('static(MethodPhpDocsNamespace\Foo)', 'static(MethodPhpDocsNamespace\FooWithTrait)', $description);

		if ($replaceClass && $expression !== '$this->doFoo()') {
			$description = str_replace('$this(MethodPhpDocsNamespace\Foo)', '$this(MethodPhpDocsNamespace\FooWithTrait)', $description);
			if ($description === 'MethodPhpDocsNamespace\Foo') {
				$description = 'MethodPhpDocsNamespace\FooWithTrait';
			}
		}
		$this->assertTypes(
			__DIR__ . '/data/methodPhpDocs-trait.php',
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
			if ($expression === '$inlineSelf') {
				$description = 'MethodPhpDocsNamespace\FooInheritDocChild';
			}
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
				'*ERROR*',
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
			[
				'InstanceOfNamespace\BarParent',
				'$parent',
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
			'*ERROR*',
			'$foo'
		);
	}

	public function dataSwitchInstanceOf(): array
	{
		return [
			[
				'*ERROR*',
				'$foo',
			],
			[
				'*ERROR*',
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
				"'normalName'",
			],
			[
				'SwitchGetClass\Foo',
				'$lorem',
				"'selfReferentialName'",
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
			[],
			[],
			$evaluatedPointExpression
		);
	}

	public function dataDynamicMethodReturnTypeExtensions(): array
	{
		return [
			[
				'*ERROR*',
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
				'*ERROR*',
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
				'*ERROR*',
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
				'*ERROR*',
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
							return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
						}

						$arg = $args[0]->value;
						if (!($arg instanceof \PhpParser\Node\Expr\ClassConstFetch)) {
							return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
						}

						if (!($arg->class instanceof \PhpParser\Node\Name)) {
							return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
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
							return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
						}

						$arg = $args[0]->value;
						if (!($arg instanceof \PhpParser\Node\Expr\ClassConstFetch)) {
							return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
						}

						if (!($arg->class instanceof \PhpParser\Node\Name)) {
							return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
						}

						return new ObjectType((string) $arg->class);
					}
				},
			]
		);
	}

	public function dataDynamicReturnTypeExtensionsOnCompoundTypes(): array
	{
		return [
			[
				'DynamicMethodReturnCompoundTypes\Collection',
				'$collection->getSelf()',
			],
			[
				'DynamicMethodReturnCompoundTypes\Collection|DynamicMethodReturnCompoundTypes\Foo',
				'$collectionOrFoo->getSelf()',
			],
		];
	}

	/**
	 * @dataProvider dataDynamicReturnTypeExtensionsOnCompoundTypes
	 * @param string $description
	 * @param string $expression
	 */
	public function testDynamicReturnTypeExtensionsOnCompoundTypes(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/dynamic-method-return-compound-types.php',
			$description,
			$expression,
			[
				new class () implements DynamicMethodReturnTypeExtension {

					public function getClass(): string
					{
						return \DynamicMethodReturnCompoundTypes\Collection::class;
					}

					public function isMethodSupported(MethodReflection $methodReflection): bool
					{
						return $methodReflection->getName() === 'getSelf';
					}

					public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
					{
						return new ObjectType(\DynamicMethodReturnCompoundTypes\Collection::class);
					}

				},
				new class () implements DynamicMethodReturnTypeExtension {

					public function getClass(): string
					{
						return \DynamicMethodReturnCompoundTypes\Foo::class;
					}

					public function isMethodSupported(MethodReflection $methodReflection): bool
					{
						return $methodReflection->getName() === 'getSelf';
					}

					public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
					{
						return new ObjectType(\DynamicMethodReturnCompoundTypes\Foo::class);
					}

				},
			]
		);
	}

	public function dataOverwritingVariable(): array
	{
		return [
			[
				'*ERROR*',
				'$var',
				'new \OverwritingVariable\Bar()',
			],
			[
				'OverwritingVariable\Bar',
				'$var',
				'$var->methodFoo()',
			],
			[
				'OverwritingVariable\Foo',
				'$var',
				'die',
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
				'*ERROR*',
				'$lorem',
			],
			[
				'*ERROR*',
				'$dolor',
			],
			[
				'*ERROR*',
				'$sit',
			],
			[
				'*ERROR*',
				'$mixedFoo',
			],
			[
				'*ERROR*',
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
				'1',
				'$integer',
			],
			[
				'*ERROR*',
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
				'0',
				'self::ARRAY_CONSTANT[0]',
			],
			[
				__DIR__ . '/data/foreach/array-object-type.php',
				'\'foo\'',
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
				'1|2|3',
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
				"'insideFirstForeach'",
			],
			[
				__DIR__ . '/data/foreach/object-type.php',
				'ObjectType\\MyValue',
				'$valueFromIterator',
				"'insideFirstForeach'",
			],
			[
				__DIR__ . '/data/foreach/object-type.php',
				'ObjectType\\MyKey',
				'$keyFromAggregate',
				"'insideSecondForeach'",
			],
			[
				__DIR__ . '/data/foreach/object-type.php',
				'ObjectType\\MyValue',
				'$valueFromAggregate',
				"'insideSecondForeach'",
			],
			[
				__DIR__ . '/data/foreach/object-type.php',
				'*ERROR*',
				'$keyFromRecursiveAggregate',
				"'insideThirdForeach'",
			],
			[
				__DIR__ . '/data/foreach/object-type.php',
				'*ERROR*',
				'$valueFromRecursiveAggregate',
				"'insideThirdForeach'",
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
			[],
			[],
			$evaluatedPointExpression
		);
	}

	public function dataArrayFunctions(): array
	{
		return [
			[
				'1',
				'$integers[0]',
			],
			[
				'array(string, string, string)',
				'$mappedStrings',
			],
			[
				'string',
				'$mappedStrings[0]',
			],
			[
				'1|2|3',
				'$filteredIntegers[0]',
			],
			[
				'123',
				'$filteredMixed[0]',
			],
			[
				'1|2|3',
				'$uniquedIntegers[1]',
			],
			[
				'string',
				'$reducedIntegersToString',
			],
			[
				'1|2|3',
				'$reversedIntegers[0]',
			],
			[
				'array(1, 1, 1, 1, 1)',
				'$filledIntegers',
			],
			[
				'array(1)',
				'$filledIntegersWithKeys',
			],
			[
				'array(1, 2)',
				'array_keys($integerKeys)',
			],
			[
				'array(\'foo\', \'bar\')',
				'array_keys($stringKeys)',
			],
			[
				'array(\'foo\', 1)',
				'array_keys($stringOrIntegerKeys)',
			],
			[
				'array<int, string>',
				'array_keys($generalStringKeys)',
			],
			[
				'array(\'foo\', stdClass)',
				'array_values($integerKeys)',
			],
			[
				'array<int, int>',
				'array_values($generalStringKeys)',
			],
			[
				'array(\'foo\' => stdClass, 0 => stdClass)',
				'array_merge($stringOrIntegerKeys)',
			],
			[
				'array<int|string, DateTimeImmutable|int>',
				'array_merge($generalStringKeys, $generalDateTimeValues)',
			],
			[
				'array<1|string, int|stdClass>',
				'array_merge($generalStringKeys, $stringOrIntegerKeys)',
			],
			[
				'array<1|string, int|stdClass>',
				'array_merge($stringOrIntegerKeys, $generalStringKeys)',
			],
			[
				'array(\'foo\' => stdClass, \'bar\' => stdClass, 0 => stdClass)',
				'array_merge($stringKeys, $stringOrIntegerKeys)',
			],
			[
				'array(\'foo\' => \'foo\', 0 => stdClass, \'bar\' => stdClass)',
				'array_merge($stringOrIntegerKeys, $stringKeys)',
			],
			[
				'array(\'color\' => \'green\', 0 => 2, 1 => 4, 2 => \'a\', 3 => \'b\', \'shape\' => \'trapezoid\', 4 => 4)',
				'array_merge(array("color" => "red", 2, 4), array("a", "b", "color" => "green", "shape" => "trapezoid", 4))',
			],
			[
				'array<int|string, DateTimeImmutable|int>',
				'array_merge(...[$generalStringKeys, $generalDateTimeValues])',
			],
			[
				'array(5 => \'banana\', 6 => \'banana\', 7 => \'banana\', 8 => \'banana\', 9 => \'banana\', 10 => \'banana\')',
				'array_fill(5, 6, \'banana\')',
			],
			[
				'array(-2 => \'pear\', 0 => \'pear\', 1 => \'pear\', 2 => \'pear\')',
				'array_fill(-2, 4, \'pear\')',
			],
			[
				'array<int, stdClass>',
				'array_fill($integer, 2, new \stdClass())',
			],
			[
				'array<int, stdClass>',
				'array_fill(2, $integer, new \stdClass())',
			],
			[
				'array<int, stdClass>',
				'array_fill_keys($generalStringKeys, new \stdClass())',
			],
			[
				'array(\'foo\' => \'banana\', 5 => \'banana\', 10 => \'banana\', \'bar\' => \'banana\')',
				'array_fill_keys([\'foo\', 5, 10, \'bar\'], \'banana\')',
			],
			[
				'array<string, stdClass>',
				'$mappedStringKeys',
			],
			[
				'array<string, mixed>',
				'$mappedStringKeysWithUnknownClosureType',
			],
			[
				'array<string>',
				'$mappedWrongArray',
			],
			[
				'array',
				'$unknownArray',
			],
			[
				'array(\'foo\' => \'banana\', \'bar\' => \'banana\', ?\'baz\' => \'banana\', ?\'lorem\' => \'banana\')',
				'array_fill_keys($conditionalArray, \'banana\')',
			],
			[
				'array(\'foo\' => stdClass, \'bar\' => stdClass, ?\'baz\' => stdClass, ?\'lorem\' => stdClass)',
				'array_map(function (): \stdClass {}, $conditionalKeysArray)',
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

	public function dataFunctions(): array
	{
		return [
			[
				'string',
				'$microtimeString',
			],
			[
				'float',
				'$microtimeFloat',
			],
			[
				'float|string',
				'$microtimeDefault',
			],
			[
				'int',
				'$strtotimeNow',
			],
			[
				'false',
				'$strtotimeInvalid',
			],
			[
				'int|false',
				'$strtotimeUnknown',
			],
			[
				'int|false',
				'$strtotimeUnknown2',
			],
		];
	}

	/**
	 * @dataProvider dataFunctions
	 * @param string $description
	 * @param string $expression
	 */
	public function testFunctions(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/functions.php',
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
				'*ERROR*',
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
			[
				'Foo',
				'$subClassOfFoo',
			],
			[
				'\'str\'',
				'$subClassAsString',
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

	public function dataTypeSpecifyingExtensions(): array
	{
		return [
			[
				'string',
				'$foo',
				true,
			],
			[
				'int',
				'$bar',
				true,
			],
			[
				'string|null',
				'$foo',
				false,
			],
			[
				'int|null',
				'$bar',
				false,
			],
			[
				'string',
				'$foo',
				null,
			],
			[
				'int',
				'$bar',
				null,
			],
		];
	}

	/**
	 * @dataProvider dataTypeSpecifyingExtensions
	 * @param string $description
	 * @param string $expression
	 * @param bool|null $nullContext
	 */
	public function testTypeSpecifyingExtensions(
		string $description,
		string $expression,
		?bool $nullContext
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/type-specifying-extensions.php',
			$description,
			$expression,
			[],
			[],
			[new AssertionClassMethodTypeSpecifyingExtension($nullContext)],
			[new AssertionClassStaticMethodTypeSpecifyingExtension($nullContext)]
		);
	}

	public function dataTypeSpecifyingExtensions2(): array
	{
		return [
			[
				'string|null',
				'$foo',
				true,
			],
			[
				'int|null',
				'$bar',
				true,
			],
			[
				'string|null',
				'$foo',
				false,
			],
			[
				'int|null',
				'$bar',
				false,
			],
			[
				'string|null',
				'$foo',
				null,
			],
			[
				'int|null',
				'$bar',
				null,
			],
		];
	}

	/**
	 * @dataProvider dataTypeSpecifyingExtensions2
	 * @param string $description
	 * @param string $expression
	 * @param bool|null $nullContext
	 */
	public function testTypeSpecifyingExtensions2(
		string $description,
		string $expression,
		?bool $nullContext
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/type-specifying-extensions2.php',
			$description,
			$expression,
			[],
			[],
			[new AssertionClassMethodTypeSpecifyingExtension($nullContext)],
			[new AssertionClassStaticMethodTypeSpecifyingExtension($nullContext)]
		);
	}

	public function dataTypeSpecifyingExtensions3(): array
	{
		return [
			[
				'string',
				'$foo',
				false,
			],
			[
				'int',
				'$bar',
				false,
			],
			[
				'string|null',
				'$foo',
				true,
			],
			[
				'int|null',
				'$bar',
				true,
			],
			[
				'string',
				'$foo',
				null,
			],
			[
				'int',
				'$bar',
				null,
			],
		];
	}

	/**
	 * @dataProvider dataTypeSpecifyingExtensions3
	 * @param string $description
	 * @param string $expression
	 * @param bool|null $nullContext
	 */
	public function testTypeSpecifyingExtensions3(
		string $description,
		string $expression,
		?bool $nullContext
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/type-specifying-extensions3.php',
			$description,
			$expression,
			[],
			[],
			[new AssertionClassMethodTypeSpecifyingExtension($nullContext)],
			[new AssertionClassStaticMethodTypeSpecifyingExtension($nullContext)]
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
				'\'foo\'',
				'$heredoc',
			],
			[
				'\'bar\'',
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
				"'nullForSure'",
			],
			[
				'TypeElimination\Foo',
				'$foo',
				"'notNullForSure'",
			],
			[
				'TypeElimination\Foo',
				'$foo',
				"'notNullForSure2'",
			],
			[
				'null',
				'$foo',
				"'nullForSure2'",
			],
			[
				'null',
				'$foo',
				"'nullForSure3'",
			],
			[
				'TypeElimination\Foo',
				'$foo',
				"'notNullForSure3'",
			],
			[
				'null',
				'$foo',
				"'yodaNullForSure'",
			],
			[
				'TypeElimination\Foo',
				'$foo',
				"'yodaNotNullForSure'",
			],
			[
				'false',
				'$intOrFalse',
				"'falseForSure'",
			],
			[
				'int',
				'$intOrFalse',
				"'intForSure'",
			],
			[
				'false',
				'$intOrFalse',
				"'yodaFalseForSure'",
			],
			[
				'int',
				'$intOrFalse',
				"'yodaIntForSure'",
			],
			[
				'true',
				'$intOrTrue',
				"'trueForSure'",
			],
			[
				'int',
				'$intOrTrue',
				"'anotherIntForSure'",
			],
			[
				'true',
				'$intOrTrue',
				"'yodaTrueForSure'",
			],
			[
				'int',
				'$intOrTrue',
				"'yodaAnotherIntForSure'",
			],
			[
				'TypeElimination\Foo',
				'$fooOrBarOrBaz',
				"'fooForSure'",
			],
			[
				'TypeElimination\Bar|TypeElimination\Baz',
				'$fooOrBarOrBaz',
				"'barOrBazForSure'",
			],
			[
				'TypeElimination\Bar',
				'$fooOrBarOrBaz',
				"'barForSure'",
			],
			[
				'TypeElimination\Baz',
				'$fooOrBarOrBaz',
				"'bazForSure'",
			],
			[
				'TypeElimination\Bar|TypeElimination\Baz',
				'$fooOrBarOrBaz',
				"'anotherBarOrBazForSure'",
			],
			[
				'TypeElimination\Foo',
				'$fooOrBarOrBaz',
				"'anotherFooForSure'",
			],
			[
				'string|null',
				'$result',
				"'stringOrNullForSure'",
			],
			[
				'int',
				'$intOrFalse',
				"'yetAnotherIntForSure'",
			],
			[
				'int',
				'$intOrTrue',
				"'yetYetAnotherIntForSure'",
			],
			[
				'TypeElimination\Foo|null',
				'$fooOrStringOrNull',
				"'fooOrNull'",
			],
			[
				'string',
				'$fooOrStringOrNull',
				"'stringForSure'",
			],
			[
				'string',
				'$fooOrStringOrNull',
				"'anotherStringForSure'",
			],
			[
				'null',
				'$this->bar',
				"'propertyNullForSure'",
			],
			[
				'TypeElimination\Bar',
				'$this->bar',
				"'propertyNotNullForSure'",
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
				'1|null',
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
				'1',
				'$foo',
			],
			[
				'*ERROR*',
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
				'1|\'foo\'',
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
				"'begin'",
			],
			[
				'LoopVariables\Foo',
				'$foo',
				"'afterAssign'",
			],
			[
				'LoopVariables\Foo',
				'$foo',
				"'end'",
			],
			[
				'LoopVariables\Bar|LoopVariables\Foo|LoopVariables\Lorem|null',
				'$foo',
				"'afterLoop'",
			],
			[
				'int',
				'$i',
				"'begin'",
			],
			[
				'int',
				'$i',
				"'end'",
			],
			[
				'int',
				'$i',
				"'afterLoop'",
			],
		];
	}

	public function dataForeachLoopVariables(): array
	{
		return [
			[
				'1|2|3',
				'$val',
				"'begin'",
			],
			[
				'0|1|2',
				'$key',
				"'begin'",
			],
			[
				'1|2|3|null',
				'$val',
				"'afterLoop'",
			],
			[
				'0|1|2|null',
				'$key',
				"'afterLoop'",
			],
			[
				'1|2|3|null',
				'$emptyForeachVal',
				"'afterLoop'",
			],
			[
				'0|1|2|null',
				'$emptyForeachKey',
				"'afterLoop'",
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
				"'begin'",
			],
			[
				'LoopVariables\Foo',
				'$foo',
				"'afterAssign'",
			],
			[
				'LoopVariables\Foo',
				'$foo',
				"'end'",
			],
			[
				'LoopVariables\Bar|LoopVariables\Foo|LoopVariables\Lorem',
				'$foo',
				"'afterLoop'",
			],
			[
				'int',
				'$i',
				"'begin'",
			],
			[
				'int',
				'$i',
				"'end'",
			],
			[
				'int',
				'$i',
				"'afterLoop'",
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
				"'Foo'",
			],
			[
				'MultipleClasses\Bar',
				'$self',
				"'Bar'",
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
				'array<int, string>|false',
				'$arrayOrFalse',
			],
			[
				'array<int, string>|false',
				'$anotherArrayOrFalse',
			],
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

	public function dataReset(): array
	{
		return [
			[
				'mixed',
				'reset()',
			],
			[
				'stdClass|false',
				'reset($generalArray)',
			],
			[
				'mixed',
				'reset($somethingElse)',
			],
			[
				'false',
				'reset($emptyConstantArray)',
			],
			[
				'1',
				'reset($constantArray)',
			],
			[
				'\'baz\'|\'foo\'',
				'reset($conditionalArray)',
			],
		];
	}

	/**
	 * @dataProvider dataReset
	 * @param string $description
	 * @param string $expression
	 */
	public function testReset(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/reset.php',
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
				'$expectedString2',
			],
			[
				'string',
				'$anotherExpectedString',
			],
			[
				'array(\'a\' => string, \'b\' => string)',
				'$expectedArray',
			],
			[
				'array(\'a\' => string, \'b\' => string)',
				'$expectedArray2',
			],
			[
				'array(\'a\' => string, \'b\' => string)',
				'$anotherExpectedArray',
			],
			[
				'array|string',
				'$expectedArrayOrString',
			],
			[
				'array|string',
				'$expectedArrayOrString2',
			],
			[
				'array|string',
				'$anotherExpectedArrayOrString',
			],
			[
				'array(\'a\' => string, \'b\' => string)',
				'preg_replace_callback_array($callbacks, $array)',
			],
			[
				'string',
				'preg_replace_callback_array($callbacks, $string)',
			],
			[
				'string',
				'str_replace(\'.\', \':\', $intOrStringKey)',
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

	public function dataClosureWithUsePassedByReference(): array
	{
		return [
			[
				'false',
				'$progressStarted',
				"'beforeCallback'",
			],
			[
				'false',
				'$anotherVariable',
				"'beforeCallback'",
			],
			[
				'1|bool',
				'$progressStarted',
				"'inCallbackBeforeAssign'",
			],
			[
				'false',
				'$anotherVariable',
				"'inCallbackBeforeAssign'",
			],
			[
				'null',
				'$untouchedPassedByRef',
				"'inCallbackBeforeAssign'",
			],
			[
				'1|true',
				'$progressStarted',
				"'inCallbackAfterAssign'",
			],
			[
				'true',
				'$anotherVariable',
				"'inCallbackAfterAssign'",
			],
			[
				'1|bool',
				'$progressStarted',
				"'afterCallback'",
			],
			[
				'false',
				'$anotherVariable',
				"'afterCallback'",
			],
			[
				'null',
				'$untouchedPassedByRef',
				"'afterCallback'",
			],
		];
	}

	/**
	 * @dataProvider dataClosureWithUsePassedByReference
	 * @param string $description
	 * @param string $expression
	 * @param string $evaluatedPointExpression
	 */
	public function testClosureWithUsePassedByReference(
		string $description,
		string $expression,
		string $evaluatedPointExpression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/closure-passed-by-reference.php',
			$description,
			$expression,
			[],
			[],
			[],
			[],
			$evaluatedPointExpression
		);
	}

	public function dataTraitsPhpDocs(): array
	{
		return [
			[
				'mixed',
				'$this->propertyWithoutPhpDoc',
			],
			[
				'TraitPhpDocsTwo\TraitPropertyType',
				'$this->traitProperty',
			],
			[
				'TraitPhpDocs\PropertyTypeFromClass',
				'$this->conflictingProperty',
			],
			[
				'TraitPhpDocs\AmbiguousPropertyType',
				'$this->bogusProperty',
			],
			[
				'TraitPhpDocs\BogusPropertyType',
				'$this->anotherBogusProperty',
			],
			[
				'TraitPhpDocs\BogusPropertyType', // should be TraitPhpDocsTwo\BogusPropertyType
				'$this->differentBogusProperty',
			],
			[
				'string',
				'$this->methodWithoutPhpDoc()',
			],
			[
				'TraitPhpDocsTwo\TraitMethodType',
				'$this->traitMethod()',
			],
			[
				'TraitPhpDocs\MethodTypeFromClass',
				'$this->conflictingMethod()',
			],
			[
				'TraitPhpDocs\AmbiguousMethodType',
				'$this->bogusMethod()',
			],
			[
				'TraitPhpDocs\BogusMethodType',
				'$this->anotherBogusMethod()',
			],
			[
				'TraitPhpDocsTwo\BogusMethodType',
				'$this->differentBogusMethod()',
			],
			[
				'TraitPhpDocsTwo\DuplicateMethodType',
				'$this->methodInMoreTraits()',
			],
			[
				'TraitPhpDocsThree\AnotherDuplicateMethodType',
				'$this->anotherMethodInMoreTraits()',
			],
			[
				'TraitPhpDocsTwo\YetAnotherDuplicateMethodType',
				'$this->yetAnotherMethodInMoreTraits()',
			],
			[
				'TraitPhpDocsThree\YetAnotherDuplicateMethodType',
				'$this->aliasedYetAnotherMethodInMoreTraits()',
			],
			[
				'TraitPhpDocsThree\YetYetAnotherDuplicateMethodType',
				'$this->yetYetAnotherMethodInMoreTraits()',
			],
			[
				'TraitPhpDocsTwo\YetYetAnotherDuplicateMethodType',
				'$this->aliasedYetYetAnotherMethodInMoreTraits()',
			],
		];
	}

	/**
	 * @dataProvider dataTraitsPhpDocs
	 * @param string $description
	 * @param string $expression
	 */
	public function testTraitsPhpDocs(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/traits/traits.php',
			$description,
			$expression
		);
	}

	public function dataPassedByReference(): array
	{
		return [
			[
				'array(1, 2, 3)',
				'$arr',
			],
			[
				'mixed',
				'$matches',
			],
			[
				'mixed',
				'$s',
			],
		];
	}

	/**
	 * @dataProvider dataPassedByReference
	 * @param string $description
	 * @param string $expression
	 */
	public function testPassedByReference(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/passed-by-reference.php',
			$description,
			$expression
		);
	}

	public function dataCallables(): array
	{
		return [
			[
				'int',
				'$foo()',
			],
			[
				'string',
				'$closure()',
			],
			[
				'Callables\\Bar',
				'$arrayWithStaticMethod()',
			],
			[
				'float',
				'$stringWithStaticMethod()',
			],
			[
				'float',
				'$arrayWithInstanceMethod()',
			],
			[
				'mixed',
				'$closureObject()',
			],
		];
	}

	/**
	 * @dataProvider dataCallables
	 * @param string $description
	 * @param string $expression
	 */
	public function testCallables(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/callables.php',
			$description,
			$expression
		);
	}

	public function dataArrayKeysInBranches(): array
	{
		return [
			[
				'array(\'i\' => int, \'j\' => int, \'k\' => int, \'l\' => 1, \'m\' => 5, \'key\' => DateTimeImmutable)',
				'$array',
			],
			[
				'array',
				'$generalArray',
			],
			[
				'mixed', // should be DateTimeImmutable
				'$generalArray[\'key\']',
			],
			[
				'array(0 => \'foo\', 1 => \'bar\', ?2 => \'baz\')',
				'$arrayAppendedInIf',
			],
			[
				'array<int, \'bar\'|\'baz\'|\'foo\'>',
				'$arrayAppendedInForeach',
			],
			[
				'array<int, \'bar\'|\'baz\'|\'foo\'>',
				'$anotherArrayAppendedInForeach',
			],
		];
	}

	/**
	 * @dataProvider dataArrayKeysInBranches
	 * @param string $description
	 * @param string $expression
	 */
	public function testArrayKeysInBranches(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/array-keys-branches.php',
			$description,
			$expression
		);
	}

	public function dataSpecifiedFunctionCall(): array
	{
		return [
			[
				'true',
				'is_file($autoloadFile)',
				"'first'",
			],
			[
				'true',
				'is_file($autoloadFile)',
				"'second'",
			],
			[
				'true',
				'is_file($autoloadFile)',
				"'third'",
			],
			[
				'bool',
				'is_file($autoloadFile)',
				"'fourth'",
			],
			[
				'true',
				'is_file($autoloadFile)',
				"'fifth'",
			],
		];
	}

	/**
	 * @dataProvider dataSpecifiedFunctionCall
	 * @param string $description
	 * @param string $expression
	 * @param string $evaluatedPointExpression
	 */
	public function testSpecifiedFunctionCall(
		string $description,
		string $expression,
		string $evaluatedPointExpression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/specified-function-call.php',
			$description,
			$expression,
			[],
			[],
			[],
			[],
			$evaluatedPointExpression
		);
	}

	public function dataElementsOnMixed(): array
	{
		return [
			[
				'mixed',
				'$mixed->foo',
			],
			[
				'mixed',
				'$mixed->foo->bar',
			],
			[
				'mixed',
				'$mixed->foo()',
			],
			[
				'mixed',
				'$mixed->foo()->bar()',
			],
			[
				'mixed',
				'$mixed::TEST_CONSTANT',
			],
		];
	}

	/**
	 * @dataProvider dataElementsOnMixed
	 * @param string $description
	 * @param string $expression
	 */
	public function testElementsOnMixed(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/mixed-elements.php',
			$description,
			$expression
		);
	}

	public function dataCaseInsensitivePhpDocTypes(): array
	{
		return [
			[
				'Foo\Bar',
				'$this->bar',
			],
			[
				'Foo\Baz',
				'$this->lorem',
			],
		];
	}

	/**
	 * @dataProvider dataCaseInsensitivePhpDocTypes
	 * @param string $description
	 * @param string $expression
	 */
	public function testCaseInsensitivePhpDocTypes(
		string $description,
		string $expression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/case-insensitive-phpdocs.php',
			$description,
			$expression
		);
	}

	public function dataAnonymousClass(): array
	{
		return [
			[
				'$this(AnonymousClass3301acd9e9d13ba9bbce9581cdb00699)',
				'$this',
				"'inside'",
			],
			[
				'AnonymousClass3301acd9e9d13ba9bbce9581cdb00699',
				'$foo',
				"'outside'",
			],
			[
				'AnonymousClassName\Foo',
				'$this->fooProperty',
				"'inside'",
			],
			[
				'AnonymousClassName\Foo',
				'$foo->fooProperty',
				"'outside'",
			],
			[
				'AnonymousClassName\Foo',
				'$this->doFoo()',
				"'inside'",
			],
			[
				'AnonymousClassName\Foo',
				'$foo->doFoo()',
				"'outside'",
			],
		];
	}

	/**
	 * @dataProvider dataAnonymousClass
	 * @param string $description
	 * @param string $expression
	 * @param string $evaluatedPointExpression
	 */
	public function testAnonymousClassName(
		string $description,
		string $expression,
		string $evaluatedPointExpression
	): void
	{
		$this->assertTypes(
			__DIR__ . '/data/anonymous-class-name.php',
			$description,
			$expression,
			[],
			[],
			[],
			[],
			$evaluatedPointExpression
		);
	}

	private function assertTypes(
		string $file,
		string $description,
		string $expression,
		array $dynamicMethodReturnTypeExtensions = [],
		array $dynamicStaticMethodReturnTypeExtensions = [],
		array $methodTypeSpecifyingExtensions = [],
		array $staticMethodTypeSpecifyingExtensions = [],
		string $evaluatedPointExpression = 'die'
	): void
	{
		$this->processFile($file, function (\PhpParser\Node $node, Scope $scope) use ($description, $expression, $evaluatedPointExpression): void {
			$printer = new \PhpParser\PrettyPrinter\Standard();
			$printedNode = $printer->prettyPrint([$node]);
			if ($printedNode !== $evaluatedPointExpression) {
				return;
			}

			/** @var \PhpParser\Node\Stmt\Expression $expressionNode */
			$expressionNode = $this->getParser()->parseString(sprintf('<?php %s;', $expression))[0];
			$type = $scope->getType($expressionNode->expr);
			$this->assertTypeDescribe(
				$description,
				$type,
				sprintf('%s at %s', $expression, $evaluatedPointExpression)
			);
		}, $dynamicMethodReturnTypeExtensions, $dynamicStaticMethodReturnTypeExtensions, $methodTypeSpecifyingExtensions, $staticMethodTypeSpecifyingExtensions);
	}

	private function processFile(
		string $file,
		\Closure $callback,
		array $dynamicMethodReturnTypeExtensions = [],
		array $dynamicStaticMethodReturnTypeExtensions = [],
		array $methodTypeSpecifyingExtensions = [],
		array $staticMethodTypeSpecifyingExtensions = []
	): void
	{
		$phpDocStringResolver = $this->getContainer()->getByType(PhpDocStringResolver::class);

		$printer = new \PhpParser\PrettyPrinter\Standard();
		$broker = $this->createBroker($dynamicMethodReturnTypeExtensions, $dynamicStaticMethodReturnTypeExtensions);
		$typeSpecifier = $this->createTypeSpecifier($printer, $broker, $methodTypeSpecifyingExtensions, $staticMethodTypeSpecifyingExtensions);
		$resolver = new NodeScopeResolver(
			$broker,
			$this->getParser(),
			new FileTypeMapper($this->getParser(), $phpDocStringResolver, $this->createMock(Cache::class), new AnonymousClassNameHelper($this->getCurrentWorkingDirectory())),
			new FileHelper('/'),
			$typeSpecifier,
			true,
			true,
			[
				\EarlyTermination\Foo::class => [
					'doFoo',
					'doBar',
				],
			]
		);
		$resolver->setAnalysedFiles([
			$file,
			__DIR__ . '/data/methodPhpDocs-trait-defined.php',
		]);
		$resolver->processNodes(
			$this->getParser()->parseFile($file),
			new Scope(
				$broker,
				$printer,
				$typeSpecifier,
				ScopeContext::create($file)
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
			if (!($node instanceof Exit_)) {
				return;
			}

			$this->assertSame($result, $scope->isDeclareStrictTypes());
		});
	}

	public function testEarlyTermination(): void
	{
		$this->processFile(__DIR__ . '/data/early-termination.php', function (\PhpParser\Node $node, Scope $scope): void {
			if (!($node instanceof Exit_)) {
				return;
			}

			$this->assertTrue($scope->hasVariableType('something')->yes());
			$this->assertTrue($scope->hasVariableType('var')->yes());
			$this->assertTrue($scope->hasVariableType('foo')->no());
		});
	}

	private function assertTypeDescribe(
		string $expectedDescription,
		Type $actualType,
		string $label = ''
	): void
	{
		$actualDescription = $actualType->describe(VerbosityLevel::value());
		if ($actualType instanceof ErrorType) {
			$actualDescription = '*ERROR*';
		}
		$this->assertSame(
			$expectedDescription,
			$actualDescription,
			$label
		);
	}

}
