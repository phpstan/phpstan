<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\VarLikeIdentifier;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

class TypeSpecifierTest extends \PHPStan\Testing\TestCase
{

	/** @var \PhpParser\PrettyPrinter\Standard() */
	private $printer;

	/** @var \PHPStan\Analyser\TypeSpecifier */
	private $typeSpecifier;

	/** @var Scope */
	private $scope;

	protected function setUp(): void
	{
		$broker = $this->createBroker();
		$this->printer = new \PhpParser\PrettyPrinter\Standard();
		$this->typeSpecifier = $this->createTypeSpecifier($this->printer, $broker);
		$this->scope = $this->createScopeFactory($broker, $this->typeSpecifier)->create(ScopeContext::create(''));
		$this->scope = $this->scope->enterClass($broker->getClass('DateTime'));
		$this->scope = $this->scope->assignVariable('bar', new ObjectType('Bar'));
		$this->scope = $this->scope->assignVariable('stringOrNull', new UnionType([new StringType(), new NullType()]));
		$this->scope = $this->scope->assignVariable('string', new StringType());
		$this->scope = $this->scope->assignVariable('barOrNull', new UnionType([new ObjectType('Bar'), new NullType()]));
		$this->scope = $this->scope->assignVariable('barOrFalse', new UnionType([new ObjectType('Bar'), new ConstantBooleanType(false)]));
		$this->scope = $this->scope->assignVariable('stringOrFalse', new UnionType([new StringType(), new ConstantBooleanType(false)]));
		$this->scope = $this->scope->assignVariable('array', new ArrayType(new MixedType(), new MixedType()));
		$this->scope = $this->scope->assignVariable('foo', new MixedType());
	}

	/**
	 * @dataProvider dataCondition
	 * @param Expr  $expr
	 * @param array $expectedPositiveResult
	 * @param array $expectedNegatedResult
	 */
	public function testCondition(Expr $expr, array $expectedPositiveResult, array $expectedNegatedResult): void
	{
		$specifiedTypes = $this->typeSpecifier->specifyTypesInCondition($this->scope, $expr, TypeSpecifierContext::createTruthy());
		$actualResult = $this->toReadableResult($specifiedTypes);
		$this->assertSame($expectedPositiveResult, $actualResult, sprintf('if (%s)', $this->printer->prettyPrintExpr($expr)));

		$specifiedTypes = $this->typeSpecifier->specifyTypesInCondition($this->scope, $expr, TypeSpecifierContext::createFalsey());
		$actualResult = $this->toReadableResult($specifiedTypes);
		$this->assertSame($expectedNegatedResult, $actualResult, sprintf('if not (%s)', $this->printer->prettyPrintExpr($expr)));
	}

	public function dataCondition(): array
	{
		return [
			[
				$this->createFunctionCall('is_int'),
				['$foo' => 'int'],
				['$foo' => '~int'],
			],
			[
				$this->createFunctionCall('is_numeric'),
				['$foo' => 'float|int|string'],
				['$foo' => '~float|int'],
			],
			[
				$this->createFunctionCall('is_scalar'),
				['$foo' => 'bool|float|int|string'],
				['$foo' => '~bool|float|int|string'],
			],
			[
				new Expr\BinaryOp\BooleanAnd(
					$this->createFunctionCall('is_int'),
					$this->createFunctionCall('random')
				),
				['$foo' => 'int'],
				[],
			],
			[
				new Expr\BinaryOp\BooleanOr(
					$this->createFunctionCall('is_int'),
					$this->createFunctionCall('random')
				),
				[],
				['$foo' => '~int'],
			],
			[
				new Expr\BinaryOp\LogicalAnd(
					$this->createFunctionCall('is_int'),
					$this->createFunctionCall('random')
				),
				['$foo' => 'int'],
				[],
			],
			[
				new Expr\BinaryOp\LogicalOr(
					$this->createFunctionCall('is_int'),
					$this->createFunctionCall('random')
				),
				[],
				['$foo' => '~int'],
			],
			[
				new Expr\BooleanNot($this->createFunctionCall('is_int')),
				['$foo' => '~int'],
				['$foo' => 'int'],
			],

			[
				new Expr\BinaryOp\BooleanAnd(
					new Expr\BooleanNot($this->createFunctionCall('is_int')),
					$this->createFunctionCall('random')
				),
				['$foo' => '~int'],
				[],
			],
			[
				new Expr\BinaryOp\BooleanOr(
					new Expr\BooleanNot($this->createFunctionCall('is_int')),
					$this->createFunctionCall('random')
				),
				[],
				['$foo' => 'int'],
			],
			[
				new Expr\BooleanNot(new Expr\BooleanNot($this->createFunctionCall('is_int'))),
				['$foo' => 'int'],
				['$foo' => '~int'],
			],
			[
				$this->createInstanceOf('Foo'),
				['$foo' => 'Foo'],
				['$foo' => '~Foo'],
			],
			[
				new Expr\BooleanNot($this->createInstanceOf('Foo')),
				['$foo' => '~Foo'],
				['$foo' => 'Foo'],
			],
			[
				new Expr\Instanceof_(
					new Variable('foo'),
					new Variable('className')
				),
				['$foo' => 'object'],
				[],
			],
			[
				new Equal(
					new FuncCall(new Name('get_class'), [
						new Arg(new Variable('foo')),
					]),
					new String_('Foo')
				),
				['$foo' => 'Foo'],
				['$foo' => '~Foo'],
			],
			[
				new Equal(
					new String_('Foo'),
					new FuncCall(new Name('get_class'), [
						new Arg(new Variable('foo')),
					])
				),
				['$foo' => 'Foo'],
				['$foo' => '~Foo'],
			],
			[
				new BooleanNot(
					new Expr\Instanceof_(
						new Variable('foo'),
						new Variable('className')
					)
				),
				[],
				['$foo' => 'object'],
			],
			[
				new Variable('foo'),
				['$foo' => '~0|0.0|\'\'|array()|false|null'],
				['$foo' => '~object|true|nonEmpty'],
			],
			[
				new Expr\BinaryOp\BooleanAnd(
					new Variable('foo'),
					$this->createFunctionCall('random')
				),
				['$foo' => '~0|0.0|\'\'|array()|false|null'],
				[],
			],
			[
				new Expr\BinaryOp\BooleanOr(
					new Variable('foo'),
					$this->createFunctionCall('random')
				),
				[],
				['$foo' => '~object|true|nonEmpty'],
			],
			[
				new Expr\BooleanNot(new Variable('bar')),
				['$bar' => '~object|true|nonEmpty'],
				['$bar' => '~0|0.0|\'\'|array()|false|null'],
			],

			[
				new PropertyFetch(new Variable('this'), 'foo'),
				['$this->foo' => '~0|0.0|\'\'|array()|false|null'],
				['$this->foo' => '~object|true|nonEmpty'],
			],
			[
				new Expr\BinaryOp\BooleanAnd(
					new PropertyFetch(new Variable('this'), 'foo'),
					$this->createFunctionCall('random')
				),
				['$this->foo' => '~0|0.0|\'\'|array()|false|null'],
				[],
			],
			[
				new Expr\BinaryOp\BooleanOr(
					new PropertyFetch(new Variable('this'), 'foo'),
					$this->createFunctionCall('random')
				),
				[],
				['$this->foo' => '~object|true|nonEmpty'],
			],
			[
				new Expr\BooleanNot(new PropertyFetch(new Variable('this'), 'foo')),
				['$this->foo' => '~object|true|nonEmpty'],
				['$this->foo' => '~0|0.0|\'\'|array()|false|null'],
			],

			[
				new Expr\BinaryOp\BooleanOr(
					$this->createFunctionCall('is_int'),
					$this->createFunctionCall('is_string')
				),
				['$foo' => 'int|string'],
				['$foo' => '~int|string'],
			],
			[
				new Expr\BinaryOp\BooleanOr(
					$this->createFunctionCall('is_int'),
					new Expr\BinaryOp\BooleanOr(
						$this->createFunctionCall('is_string'),
						$this->createFunctionCall('is_bool')
					)
				),
				['$foo' => 'bool|int|string'],
				['$foo' => '~bool|int|string'],
			],
			[
				new Expr\BinaryOp\BooleanOr(
					$this->createFunctionCall('is_int', 'foo'),
					$this->createFunctionCall('is_string', 'bar')
				),
				[],
				['$foo' => '~int', '$bar' => '~string'],
			],
			[
				new Expr\BinaryOp\BooleanAnd(
					new Expr\BinaryOp\BooleanOr(
						$this->createFunctionCall('is_int', 'foo'),
						$this->createFunctionCall('is_string', 'foo')
					),
					$this->createFunctionCall('random')
				),
				['$foo' => 'int|string'],
				[],
			],
			[
				new Expr\BinaryOp\BooleanOr(
					new Expr\BinaryOp\BooleanAnd(
						$this->createFunctionCall('is_int', 'foo'),
						$this->createFunctionCall('is_string', 'foo')
					),
					$this->createFunctionCall('random')
				),
				[],
				['$foo' => '~*NEVER*'],
			],
			[
				new Expr\BinaryOp\BooleanOr(
					new Expr\BinaryOp\BooleanAnd(
						$this->createFunctionCall('is_int', 'foo'),
						$this->createFunctionCall('is_string', 'bar')
					),
					$this->createFunctionCall('random')
				),
				[],
				[],
			],
			[
				new Expr\BinaryOp\BooleanOr(
					new Expr\BinaryOp\BooleanAnd(
						new Expr\BooleanNot($this->createFunctionCall('is_int', 'foo')),
						new Expr\BooleanNot($this->createFunctionCall('is_string', 'foo'))
					),
					$this->createFunctionCall('random')
				),
				[],
				['$foo' => 'int|string'],
			],
			[
				new Expr\BinaryOp\BooleanAnd(
					new Expr\BinaryOp\BooleanOr(
						new Expr\BooleanNot($this->createFunctionCall('is_int', 'foo')),
						new Expr\BooleanNot($this->createFunctionCall('is_string', 'foo'))
					),
					$this->createFunctionCall('random')
				),
				['$foo' => '~*NEVER*'],
				[],
			],

			[
				new Identical(
					new Variable('foo'),
					new Expr\ConstFetch(new Name('true'))
				),
				['$foo' => 'true & ~0|0.0|\'\'|array()|false|null'],
				['$foo' => '~true'],
			],
			[
				new Identical(
					new Variable('foo'),
					new Expr\ConstFetch(new Name('false'))
				),
				['$foo' => 'false & ~object|true|nonEmpty'],
				['$foo' => '~false'],
			],
			[
				new Identical(
					$this->createFunctionCall('is_int'),
					new Expr\ConstFetch(new Name('true'))
				),
				['is_int($foo)' => 'true', '$foo' => 'int'],
				['is_int($foo)' => '~true', '$foo' => '~int'],
			],
			[
				new Identical(
					$this->createFunctionCall('is_int'),
					new Expr\ConstFetch(new Name('false'))
				),
				['is_int($foo)' => 'false', '$foo' => '~int'],
				['$foo' => 'int', 'is_int($foo)' => '~false'],
			],
			[
				new Equal(
					$this->createFunctionCall('is_int'),
					new Expr\ConstFetch(new Name('true'))
				),
				['$foo' => 'int'],
				['$foo' => '~int'],
			],
			[
				new Equal(
					$this->createFunctionCall('is_int'),
					new Expr\ConstFetch(new Name('false'))
				),
				['$foo' => '~int'],
				['$foo' => 'int'],
			],
			[
				new Equal(
					new Variable('foo'),
					new Expr\ConstFetch(new Name('false'))
				),
				['$foo' => '~object|true|nonEmpty'],
				['$foo' => '~0|0.0|\'\'|array()|false|null'],
			],
			[
				new Equal(
					new Variable('foo'),
					new Expr\ConstFetch(new Name('null'))
				),
				['$foo' => '~object|true|nonEmpty'],
				['$foo' => '~0|0.0|\'\'|array()|false|null'],
			],
			[
				new Expr\BinaryOp\Identical(
					new Variable('foo'),
					new Variable('bar')
				),
				['$foo' => 'Bar', '$bar' => 'Bar'],
				[],
			],
			[
				new FuncCall(new Name('is_a'), [
					new Arg(new Variable('foo')),
					new Arg(new String_('Foo')),
				]),
				['$foo' => 'Foo'],
				['$foo' => '~Foo'],
			],
			[
				new FuncCall(new Name('is_a'), [
					new Arg(new Variable('foo')),
					new Arg(new Variable('className')),
				]),
				['$foo' => 'object'],
				[],
			],
			[
				new FuncCall(new Name('is_a'), [
					new Arg(new Variable('foo')),
					new Arg(new Expr\ClassConstFetch(
						new Name('static'),
						'class'
					)),
				]),
				['$foo' => 'static(DateTime)'],
				['$foo' => '~static(DateTime)'],
			],
			[
				new FuncCall(new Name('is_a'), [
					new Arg(new Variable('foo')),
					new Arg(new String_('Foo')),
					new Arg(new Expr\ConstFetch(new Name('true'))),
				]),
				['$foo' => 'Foo|string'],
				['$foo' => '~Foo'],
			],
			[
				new FuncCall(new Name('is_a'), [
					new Arg(new Variable('foo')),
					new Arg(new Variable('className')),
					new Arg(new Expr\ConstFetch(new Name('true'))),
				]),
				['$foo' => 'object|string'],
				[],
			],
			[
				new FuncCall(new Name('is_a'), [
					new Arg(new Variable('foo')),
					new Arg(new String_('Foo')),
					new Arg(new Variable('unknown')),
				]),
				['$foo' => 'Foo|string'],
				['$foo' => '~Foo'],
			],
			[
				new FuncCall(new Name('is_a'), [
					new Arg(new Variable('foo')),
					new Arg(new Variable('className')),
					new Arg(new Variable('unknown')),
				]),
				['$foo' => 'object|string'],
				[],
			],
			[
				new Expr\Assign(
					new Variable('foo'),
					new Variable('stringOrNull')
				),
				['$foo' => '~0|0.0|\'\'|array()|false|null'],
				['$foo' => '~object|true|nonEmpty'],
			],
			[
				new Expr\Assign(
					new Variable('foo'),
					new Variable('stringOrFalse')
				),
				['$foo' => '~0|0.0|\'\'|array()|false|null'],
				['$foo' => '~object|true|nonEmpty'],
			],
			[
				new Expr\Assign(
					new Variable('foo'),
					new Variable('bar')
				),
				['$foo' => '~0|0.0|\'\'|array()|false|null'],
				['$foo' => '~object|true|nonEmpty'],
			],
			[
				new Expr\Isset_([
					new Variable('stringOrNull'),
					new Variable('barOrNull'),
				]),
				[
					'$stringOrNull' => '~null',
					'$barOrNull' => '~null',
				],
				[
					'isset($stringOrNull, $barOrNull)' => '~object|true|nonEmpty',
				],
			],
			[
				new Expr\BooleanNot(new Expr\Empty_(new Variable('stringOrNull'))),
				[
					'$stringOrNull' => '~false|null',
				],
				[
					'empty($stringOrNull)' => '~0|0.0|\'\'|array()|false|null',
				],
			],
			[
				new Expr\BinaryOp\Identical(
					new Variable('foo'),
					new LNumber(123)
				),
				[
					'$foo' => '123',
					123 => '123',
				],
				['$foo' => '~123'],
			],
			[
				new Expr\Empty_(new Variable('array')),
				[
					'$array' => '~nonEmpty',
				],
				[
					'$array' => 'nonEmpty & ~false|null',
				],
			],
			[
				new BooleanNot(new Expr\Empty_(new Variable('array'))),
				[
					'$array' => 'nonEmpty & ~false|null',
				],
				[
					'$array' => '~nonEmpty',
				],
			],
			[
				new FuncCall(new Name('count'), [
					new Arg(new Variable('array')),
				]),
				[
					'$array' => 'nonEmpty',
				],
				[
					'$array' => '~nonEmpty',
				],
			],
			[
				new BooleanNot(new FuncCall(new Name('count'), [
					new Arg(new Variable('array')),
				])),
				[
					'$array' => '~nonEmpty',
				],
				[
					'$array' => 'nonEmpty',
				],
			],
			[
				new Variable('foo'),
				[
					'$foo' => '~0|0.0|\'\'|array()|false|null',
				],
				[
					'$foo' => '~object|true|nonEmpty',
				],
			],
			[
				new Variable('array'),
				[
					'$array' => '~0|0.0|\'\'|array()|false|null',
				],
				[
					'$array' => '~object|true|nonEmpty',
				],
			],
			[
				new Equal(
					new Expr\Instanceof_(
						new Variable('foo'),
						new Variable('className')
					),
					new LNumber(1)
				),
				['$foo' => 'object'],
				[],
			],
			[
				new Equal(
					new Expr\Instanceof_(
						new Variable('foo'),
						new Variable('className')
					),
					new LNumber(0)
				),
				[],
				[
					'$foo' => 'object',
				],
			],
			[
				new Expr\Isset_(
					[
						new PropertyFetch(new Variable('foo'), new Identifier('bar')),
					]
				),
				[
					'$foo' => 'object&hasProperty(bar) & ~null',
					'$foo->bar' => '~null',
				],
				[
					'isset($foo->bar)' => '~object|true|nonEmpty',
				],
			],
			[
				new Expr\Isset_(
					[
						new Expr\StaticPropertyFetch(new Name('Foo'), new VarLikeIdentifier('bar')),
					]
				),
				[
					'Foo::$bar' => '~null',
				],
				[
					'isset(Foo::$bar)' => '~object|true|nonEmpty',
				],
			],
			[
				new Identical(
					new Variable('barOrNull'),
					new Expr\ConstFetch(new Name('null'))
				),
				[
					'$barOrNull' => 'null',
				],
				[
					'$barOrNull' => '~null',
				],
			],
			[
				new Identical(
					new Expr\Assign(
						new Variable('notNullBar'),
						new Variable('barOrNull')
					),
					new Expr\ConstFetch(new Name('null'))
				),
				[
					'$notNullBar' => 'null',
				],
				[
					'$notNullBar' => '~null',
				],
			],
			[
				new NotIdentical(
					new Variable('barOrNull'),
					new Expr\ConstFetch(new Name('null'))
				),
				[
					'$barOrNull' => '~null',
				],
				[
					'$barOrNull' => 'null',
				],
			],
			[
				new Expr\BinaryOp\Smaller(
					new Variable('n'),
					new LNumber(3)
				),
				[
					'$n' => 'int<min, 2>',
				],
				[
					'$n' => '~int<min, 2>',
				],
			],
			[
				new Expr\BinaryOp\BooleanAnd(
					new Expr\BinaryOp\GreaterOrEqual(
						new Variable('n'),
						new LNumber(3)
					),
					new Expr\BinaryOp\SmallerOrEqual(
						new Variable('n'),
						new LNumber(5)
					)
				),
				[
					'$n' => 'int<3, 5>',
				],
				[
					'$n' => '~int<3, 5>',
				],
			],
			[
				new Expr\BinaryOp\BooleanAnd(
					new Expr\Assign(
						new Variable('foo'),
						new LNumber(1)
					),
					new Expr\BinaryOp\SmallerOrEqual(
						new Variable('n'),
						new LNumber(5)
					)
				),
				[
					'$n' => 'int<min, 5>',
					'$foo' => '~0|0.0|\'\'|array()|false|null',
				],
				[],
			],
			[
				new NotIdentical(
					new Expr\Assign(
						new Variable('notNullBar'),
						new Variable('barOrNull')
					),
					new Expr\ConstFetch(new Name('null'))
				),
				[
					'$notNullBar' => '~null',
				],
				[
					'$notNullBar' => 'null',
				],
			],
			[
				new Identical(
					new Variable('barOrFalse'),
					new Expr\ConstFetch(new Name('false'))
				),
				[
					'$barOrFalse' => 'false & ~object|true|nonEmpty',
				],
				[
					'$barOrFalse' => '~false',
				],
			],
			[
				new Identical(
					new Expr\Assign(
						new Variable('notFalseBar'),
						new Variable('barOrFalse')
					),
					new Expr\ConstFetch(new Name('false'))
				),
				[
					'$notFalseBar' => 'false & ~object|true|nonEmpty',
				],
				[
					'$notFalseBar' => '~false',
				],
			],
			[
				new NotIdentical(
					new Variable('barOrFalse'),
					new Expr\ConstFetch(new Name('false'))
				),
				[
					'$barOrFalse' => '~false',
				],
				[
					'$barOrFalse' => 'false & ~object|true|nonEmpty',
				],
			],
			[
				new NotIdentical(
					new Expr\Assign(
						new Variable('notFalseBar'),
						new Variable('barOrFalse')
					),
					new Expr\ConstFetch(new Name('false'))
				),
				[
					'$notFalseBar' => '~false',
				],
				[
					'$notFalseBar' => 'false & ~object|true|nonEmpty',
				],
			],
			[
				new Expr\Instanceof_(
					new Expr\Assign(
						new Variable('notFalseBar'),
						new Variable('barOrFalse')
					),
					new Name('Bar')
				),
				[
					'$notFalseBar' => 'Bar',
				],
				[
					'$notFalseBar' => '~Bar',
				],
			],
			[
				new Expr\BinaryOp\BooleanOr(
					new FuncCall(new Name('array_key_exists'), [
						new Arg(new String_('foo')),
						new Arg(new Variable('array')),
					]),
					new FuncCall(new Name('array_key_exists'), [
						new Arg(new String_('bar')),
						new Arg(new Variable('array')),
					])
				),
				[
					'$array' => 'array',
				],
				[
					'$array' => '~hasOffset(\'bar\')|hasOffset(\'foo\')',
				],
			],
			[
				new BooleanNot(new Expr\BinaryOp\BooleanOr(
					new FuncCall(new Name('array_key_exists'), [
						new Arg(new String_('foo')),
						new Arg(new Variable('array')),
					]),
					new FuncCall(new Name('array_key_exists'), [
						new Arg(new String_('bar')),
						new Arg(new Variable('array')),
					])
				)),
				[
					'$array' => '~hasOffset(\'bar\')|hasOffset(\'foo\')',
				],
				[
					'$array' => 'array',
				],
			],
			[
				new FuncCall(new Name('array_key_exists'), [
					new Arg(new String_('foo')),
					new Arg(new Variable('array')),
				]),
				[
					'$array' => 'array&hasOffset(\'foo\')',
				],
				[
					'$array' => '~hasOffset(\'foo\')',
				],
			],
			[
				new FuncCall(new Name('is_subclass_of'), [
					new Arg(new Variable('string')),
					new Arg(new Variable('stringOrNull')),
				]),
				[
					'$string' => 'class-string|object',
				],
				[],
			],
			[
				new FuncCall(new Name('is_subclass_of'), [
					new Arg(new Variable('string')),
					new Arg(new Variable('stringOrNull')),
					new Arg(new Expr\ConstFetch(new Name('false'))),
				]),
				[
					'$string' => 'object',
				],
				[],
			],
		];
	}

	private function toReadableResult(SpecifiedTypes $specifiedTypes): array
	{
		$typesDescription = [];

		foreach ($specifiedTypes->getSureTypes() as $exprString => [$exprNode, $exprType]) {
			$typesDescription[$exprString][] = $exprType->describe(VerbosityLevel::precise());
		}

		foreach ($specifiedTypes->getSureNotTypes() as $exprString => [$exprNode, $exprType]) {
			$typesDescription[$exprString][] = '~' . $exprType->describe(VerbosityLevel::precise());
		}

		$descriptions = [];
		foreach ($typesDescription as $exprString => $exprTypes) {
			$descriptions[$exprString] = implode(' & ', $exprTypes);
		}

		return $descriptions;
	}

	private function createInstanceOf(string $className, string $variableName = 'foo'): Expr\Instanceof_
	{
		return new Expr\Instanceof_(new Variable($variableName), new Name($className));
	}

	private function createFunctionCall(string $functionName, string $variableName = 'foo'): FuncCall
	{
		return new FuncCall(new Name($functionName), [new Arg(new Variable($variableName))]);
	}

}
