<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Analyser\Scope;
use PHPStan\Node\LiteralArrayNode;
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
		return LiteralArrayNode::class;
	}

	/**
	 * @param LiteralArrayNode $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return RuleError[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		$values = [];
		$duplicateKeys = [];
		$printedValues = [];
		$valueLines = [];
		foreach ($node->getItemNodes() as $itemNode) {
			$item = $itemNode->getArrayItem();
			if ($item->key === null) {
				continue;
			}

			$key = $item->key;
			$keyType = $itemNode->getScope()->getType($key);
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
