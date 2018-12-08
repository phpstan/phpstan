<?php declare(strict_types = 1);

namespace PHPStan\Rules\Regexp;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\TypeUtils;

class RegularExpressionPatternRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return FuncCall::class;
	}

	/**
	 * @param FuncCall $node
	 * @param Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$patterns = $this->extractPatterns($node, $scope);

		$errors = [];
		foreach ($patterns as $pattern) {
			$errorMessage = $this->validatePattern($pattern);
			if ($errorMessage === null) {
				continue;
			}

			$errors[] = sprintf('Regex pattern is invalid: %s', $errorMessage);
		}

		return $errors;
	}

	/**
	 * @param FuncCall $functionCall
	 * @param Scope $scope
	 * @return string[]
	 */
	private function extractPatterns(FuncCall $functionCall, Scope $scope): array
	{
		if (!$functionCall->name instanceof Node\Name) {
			return [];
		}
		$functionName = strtolower((string) $functionCall->name);
		if (!\Nette\Utils\Strings::startsWith($functionName, 'preg_')) {
			return [];
		}

		if (!isset($functionCall->args[0])) {
			return [];
		}
		$patternNode = $functionCall->args[0]->value;
		$patternType = $scope->getType($patternNode);

		$patternStrings = [];

		foreach (TypeUtils::getConstantStrings($patternType) as $constantStringType) {
			if (
				!in_array($functionName, [
					'preg_match',
					'preg_match_all',
					'preg_split',
					'preg_grep',
					'preg_replace',
					'preg_replace_callback',
					'preg_filter',
				], true)
			) {
				continue;
			}

			$patternStrings[] = $constantStringType->getValue();
		}

		foreach (TypeUtils::getConstantArrays($patternType) as $constantArrayType) {
			if (
				in_array($functionName, [
					'preg_replace',
					'preg_replace_callback',
					'preg_filter',
				], true)
			) {
				foreach ($constantArrayType->getValueTypes() as $arrayKeyType) {
					if (!$arrayKeyType instanceof ConstantStringType) {
						continue;
					}

					$patternStrings[] = $arrayKeyType->getValue();
				}
			}

			if ($functionName !== 'preg_replace_callback_array') {
				continue;
			}

			foreach ($constantArrayType->getKeyTypes() as $arrayKeyType) {
				if (!$arrayKeyType instanceof ConstantStringType) {
					continue;
				}

				$patternStrings[] = $arrayKeyType->getValue();
			}
		}

		return $patternStrings;
	}

	private function validatePattern(string $pattern): ?string
	{
		try {
			\Nette\Utils\Strings::match('', $pattern);
		} catch (\Nette\Utils\RegexpException $e) {
			return $e->getMessage();
		}

		return null;
	}

}
