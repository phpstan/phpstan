<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreInc;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ConstantScalarType;

class DuplicateKeysInLiteralArraysRule implements \PHPStan\Rules\Rule
{

	/** @var \PhpParser\PrettyPrinter\Standard */
	private $printer;

	public function __construct(
		\PhpParser\PrettyPrinter\Standard $printer
	)
	{
		$this->printer = $printer;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr\Array_::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Array_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return RuleError[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		$values = [];
		$duplicateKeys = [];
		$printedValues = [];
		$valueLines = [];
		foreach ($node->items as $item) {
			if ($item === null) {
				continue;
			}
			if ($item->key === null) {
				continue;
			}

			$key = $item->key;
			// Because post/pre increments guarantee the variable will me mutated on its next appearance we can safely
			// ignore it
			if ($key instanceof PostInc || $key instanceof PreInc) {
				continue;
			}

			$keyType = $scope->getType($key);
			if (
				!$keyType instanceof ConstantScalarType
			) {
				continue;
			}

			$printedValue = $this->printer->prettyPrintExpr($key);
			$value = $keyType->getValue();
			$printedValues[$value][] = $printedValue;

			if (!isset($valueLines[$value])) {
				$valueLines[$value] = $item->getLine();
			}

			$previousCount = count($values);
			$values[$value] = $printedValue;
			if ($previousCount !== count($values)) {
				continue;
			}

			$duplicateKeys[$value] = true;
		}

		$messages = [];
		foreach (array_keys($duplicateKeys) as $value) {
			$messages[] = RuleErrorBuilder::message(sprintf(
				'Array has %d %s with value %s (%s).',
				count($printedValues[$value]),
				count($printedValues[$value]) === 1 ? 'duplicate key' : 'duplicate keys',
				var_export($value, true),
				implode(', ', $printedValues[$value])
			))->line($valueLines[$value])->build();
		}

		return $messages;
	}

}
