<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;

class PrintfParametersRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return FuncCall::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\FuncCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!($node->name instanceof \PhpParser\Node\Name)) {
			return [];
		}

		$functionsArgumentPositions = [
			'printf' => 0,
			'sprintf' => 0,
			'sscanf' => 1,
			'fscanf' => 1,
		];
		$minimumNumberOfArguments = [
			'printf' => 1,
			'sprintf' => 1,
			'sscanf' => 3,
			'fscanf' => 3,
		];

		$name = strtolower((string) $node->name);
		if (!isset($functionsArgumentPositions[$name])) {
			return [];
		}

		$formatArgumentPosition = $functionsArgumentPositions[$name];

		$args = $node->args;
		$argsCount = count($args);
		if ($argsCount < $minimumNumberOfArguments[$name]) {
			return []; // caught by CallToFunctionParametersRule
		}

		$formatArg = $args[$formatArgumentPosition]->value;
		if (!($formatArg instanceof String_)) {
			return []; // inspect only literal string format
		}

		foreach ($node->args as $arg) {
			if ($arg->unpack) {
				return [];
			}
		}

		$format = $formatArg->value;
		$placeHoldersCount = $this->getPlaceholdersCount($name, $format);
		$argsCount -= $formatArgumentPosition;

		if ($argsCount !== $placeHoldersCount + 1) {
			return [
				sprintf(
					sprintf(
						'%s, %s.',
						$placeHoldersCount === 1 ? 'Call to %s contains %d placeholder' : 'Call to %s contains %d placeholders',
						$argsCount - 1 === 1 ? '%d value given' : '%d values given'
					),
					$name,
					$placeHoldersCount,
					$argsCount - 1
				),
			];
		}

		return [];
	}

	private function getPlaceholdersCount(string $functionName, string $format): int
	{
		$specifiers = in_array($functionName, ['sprintf', 'printf'], true) ? '[bcdeEfFgGosuxX]' : '(?:[cdDeEfinosuxX]|\[[^\]]+\])';
		$pattern = '~(?<before>%*)%(?:(?<position>\d+)\$)?[-+]?(?:[ 0]|(?:\'[^%]))?-?\d*(?:\.\d*)?' . $specifiers . '~';

		if (!preg_match_all($pattern, $format, $matches, PREG_SET_ORDER)) {
			return 0;
		}

		$placeholders = array_filter($matches, function (array $match): bool {
			return strlen($match['before']) % 2 === 0;
		});

		if (count($placeholders) === 0) {
			return 0;
		}

		$maxPositionedNumber = 0;
		$maxOrdinaryNumber = 0;
		foreach ($placeholders as $placeholder) {
			if (isset($placeholder['position']) && $placeholder['position'] !== '') {
				$maxPositionedNumber = max((int) $placeholder['position'], $maxPositionedNumber);
			} else {
				$maxOrdinaryNumber++;
			}
		}

		return max($maxPositionedNumber, $maxOrdinaryNumber);
	}

}
