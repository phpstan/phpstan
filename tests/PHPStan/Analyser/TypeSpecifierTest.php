<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PHPStan\TrinaryLogic;
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
		$this->scope = $this->scope->assignVariable('bar', new ObjectType('Bar'), TrinaryLogic::createYes());
		$this->scope = $this->scope->assignVariable('stringOrNull', new UnionType([new StringType(), new NullType()]), TrinaryLogic::createYes());
		$this->scope = $this->scope->assignVariable('barOrNull', new UnionType([new ObjectType('Bar'), new NullType()]), TrinaryLogic::createYes());
		$this->scope = $this->scope->assignVariable('stringOrFalse', new UnionType([new StringType(), new ConstantBooleanType(false)]), TrinaryLogic::createYes());
		$this->scope = $this->scope->assignVariable('array', new ArrayType(new MixedType(), new MixedType()), TrinaryLogic::createYes());
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
				new Variable('foo'),
				['$foo' => '~0|0.0|\'\'|array()|false|null'],
				['$foo' => '~object'],
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
				['$foo' => '~object'],
			],
			[
				new Expr\BooleanNot(new Variable('bar')),
				['$bar' => '~object'],
				['$bar' => '~0|0.0|\'\'|array()|false|null'],
			],

			[
				new PropertyFetch(new Variable('this'), 'foo'),
				['$this->foo' => '~0|0.0|\'\'|array()|false|null'],
				['$this->foo' => '~object'],
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
				['$this->foo' => '~object'],
			],
			[
				new Expr\BooleanNot(new PropertyFetch(new Variable('this'), 'foo')),
				['$this->foo' => '~object'],
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
				['$foo' => 'false & ~object'],
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
				['$foo' => '~object'],
				['$foo' => '~0|0.0|\'\'|array()|false|null'],
			],
			[
				new Equal(
					new Variable('foo'),
					new Expr\ConstFetch(new Name('null'))
				),
				['$foo' => '~object'],
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
				['$foo' => '~object'],
			],
			[
				new Expr\Assign(
					new Variable('foo'),
					new Variable('stringOrFalse')
				),
				['$foo' => '~0|0.0|\'\'|array()|false|null'],
				['$foo' => '~object'],
			],
			[
				new Expr\Assign(
					new Variable('foo'),
					new Variable('bar')
				),
				['$foo' => '~0|0.0|\'\'|array()|false|null'],
				['$foo' => '~object'],
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
					'isset($stringOrNull, $barOrNull)' => '~object',
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
					'$array' => '~nonEmpty',
				],
				[
					'$array' => 'nonEmpty',
				],
			],
			[
				new BooleanNot(new FuncCall(new Name('count'), [
					new Arg(new Variable('array')),
				])),
				[
					'$array' => 'nonEmpty',
				],
				[
					'$array' => '~nonEmpty',
				],
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
