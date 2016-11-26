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

		$name = (string) $node->name;
		if (!in_array($name, ['printf', 'sprintf'], true)) {
			return [];
		}

		$args = $node->args;
		$argsCount = count($args);
		if ($argsCount < 1) {
			return []; // caught by CallToFunctionParametersRule
		}

		$formatArg = $args[0]->value;
		if (!($formatArg instanceof String_)) {
			return []; // inspect only literal string format
		}

		$format = $formatArg->value;
		$placeHoldersCount = $this->getPlaceholdersCount($format);

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

	private function getPlaceholdersCount(string $format): int
	{
		$format = str_replace('%%', '', $format);
		$characterGroups = '(?:[\.\-0-9\'])*[a-zA-Z]';
		$options = [
			$characterGroups,
			'[0-9]+\$' . $characterGroups,
		];
		preg_match_all(sprintf('~%%((?:%s))~', implode(')|(?:', $options)), $format, $matches);
		$maxPositionedNumber = 0;
		$maxOrdinaryNumber = 0;
		foreach ($matches[1] as $match) {
			if ((int) $match !== 0 && strpos($match, '$') !== false) {
				$maxPositionedNumber = max((int) $match, $maxPositionedNumber);
			} else {
				$maxOrdinaryNumber++;
			}
		}

		return max($maxPositionedNumber, $maxOrdinaryNumber);
	}

}
