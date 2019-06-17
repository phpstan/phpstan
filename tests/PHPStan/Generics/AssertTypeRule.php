<?php declare(strict_types = 1);

namespace PHPStan\Generics;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\VerbosityLevel;

class AssertTypeRule implements Rule
{

	private const ASSERT_TYPE_FUNCTION = 'PHPStan\\Generics\\assertType';

	public function getNodeType(): string
	{
		return FuncCall::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		assert($node instanceof FuncCall);

		$nameNode = $node->name;

		if (!$nameNode instanceof Name) {
			return [];
		}

		if ((string) $nameNode !== self::ASSERT_TYPE_FUNCTION) {
			return [];
		}

		if (count($node->args) !== 2) {
			return [];
		}

		$typeType = $scope->getType($node->args[0]->value);
		$valueType = $scope->getType($node->args[1]->value);

		$constants = TypeUtils::getConstantStrings($typeType);
		foreach ($constants as $constant) {
			$typeString = $valueType->describe(VerbosityLevel::precise());
			if ($constant->getValue() !== $typeString) {
				return [sprintf(
					'Expected type %s, got %s',
					$constant->getValue(),
					$typeString
				)];
			}
		}

		return [];
	}

}
