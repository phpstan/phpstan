<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PHPStan\Type\ObjectType;

class TypeSpecifierTest extends \PHPStan\TestCase
{

	/** @var \PhpParser\PrettyPrinter\Standard() */
	private $printer;

	/** @var \PHPStan\Analyser\TypeSpecifier */
	private $typeSpecifier;

	/** @var Scope */
	private $scope;

	protected function setUp()
	{
		$broker = $this->createBroker();
		$this->printer = new \PhpParser\PrettyPrinter\Standard();
		$this->typeSpecifier = new TypeSpecifier($this->printer);
		$this->scope = new Scope($broker, $this->printer, $this->typeSpecifier, '');
		$this->scope = $this->scope->assignVariable('bar', new ObjectType('Bar'));
	}

	/**
	 * @dataProvider dataCondition
	 * @param Expr  $expr
	 * @param array $expectedPositiveResult
	 * @param array $expectedNegatedResult
	 */
	public function testCondition(Expr $expr, array $expectedPositiveResult, array $expectedNegatedResult)
	{
		$specifiedTypes = $this->typeSpecifier->specifyTypesInCondition(new SpecifiedTypes(), $this->scope, $expr);
		$actualResult = $this->toReadableResult($specifiedTypes);
		$this->assertSame($expectedPositiveResult, $actualResult);

		$specifiedTypes = $this->typeSpecifier->specifyTypesInCondition(new SpecifiedTypes(), $this->scope, $expr, true);
		$actualResult = $this->toReadableResult($specifiedTypes);
		$this->assertSame($expectedNegatedResult, $actualResult);
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
				new Variable('foo'),
				['$foo' => '~false|null'],
				[],
			],
			[
				new Expr\BinaryOp\BooleanAnd(
					new Variable('foo'),
					$this->createFunctionCall('random')
				),
				['$foo' => '~false|null'],
				[],
			],
			[
				new Expr\BinaryOp\BooleanOr(
					new Variable('foo'),
					$this->createFunctionCall('random')
				),
				[],
				[],
			],
			[
				new Expr\BooleanNot(new Variable('bar')),
				['$bar' => '~Bar'],
				['$bar' => '~false|null'],
			],
		];
	}

	private function toReadableResult(SpecifiedTypes $specifiedTypes): array
	{
		$typesDescription = [];

		foreach ($specifiedTypes->getSureTypes() as $exprString => list($exprNode, $exprType)) {
			$typesDescription[$exprString] = $exprType->describe();
		}

		foreach ($specifiedTypes->getSureNotTypes() as $exprString => list($exprNode, $exprType)) {
			$typesDescription[$exprString] = '~' . $exprType->describe();
		}

		return $typesDescription;
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
