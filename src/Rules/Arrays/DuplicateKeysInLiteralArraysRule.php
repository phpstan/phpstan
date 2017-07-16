<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class DuplicateKeysInLiteralArraysRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PhpParser\PrettyPrinter\Standard */
	private $printer;

	public function __construct(
		Broker $broker,
		\PhpParser\PrettyPrinter\Standard $printer
	)
	{
		$this->broker = $broker;
		$this->printer = $printer;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr\Array_::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Array_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		$values = [];
		$duplicateKeys = [];
		foreach ($node->items as $item) {
			if ($item === null) {
				continue;
			}
			if ($item->key === null) {
				continue;
			}

			$key = $item->key;
			if (
				!$key instanceof \PhpParser\Node\Scalar\String_
				&& !$key instanceof \PhpParser\Node\Scalar\LNumber
				&& !$key instanceof \PhpParser\Node\Scalar\DNumber
				&& !$key instanceof \PhpParser\Node\Expr\ConstFetch
			) {
				continue;
			}

			if ($key instanceof \PhpParser\Node\Expr\ConstFetch) {
				$printedValue = (string) $key->name;
				$constName = strtolower($printedValue);
				if ($constName === 'true') {
					$value = true;
				} elseif ($constName === 'false') {
					$value = false;
				} elseif ($constName === 'null') {
					$value = null;
				} elseif ($this->broker->hasConstant($key->name, $scope)) {
					$value = constant($this->broker->resolveConstantName($key->name, $scope));
				} else {
					continue;
				}
			} else {
				$printedValue = $this->printer->prettyPrintExpr($key);
				$value = eval(sprintf('return %s;', $printedValue));
			}

			$previousCount = count($values);
			$values[$value] = $printedValue;
			if ($previousCount === count($values)) {
				if (!isset($duplicateKeys[$value])) {
					$duplicateKeys[$value] = [$values[$value]];
				}

				$duplicateKeys[$value][] = $printedValue;
			}
		}

		$messages = [];
		foreach ($duplicateKeys as $key => $values) {
			$messages[] = sprintf(
				'Array has %d %s with value %s (%s).',
				count($values),
				count($values) === 1 ? 'duplicate key' : 'duplicate keys',
				var_export($key, true),
				implode(', ', $values)
			);
		}

		return $messages;
	}

}
