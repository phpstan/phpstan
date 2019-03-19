<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class InvalidPartOfEncapsedStringRule implements \PHPStan\Rules\Rule
{

	/** @var \PhpParser\PrettyPrinter\Standard */
	private $printer;

	/** @var \PHPStan\Rules\RuleLevelHelper */
	private $ruleLevelHelper;

	public function __construct(
		\PhpParser\PrettyPrinter\Standard $printer,
		RuleLevelHelper $ruleLevelHelper
	)
	{
		$this->printer = $printer;
		$this->ruleLevelHelper = $ruleLevelHelper;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\Scalar\Encapsed::class;
	}

	/**
	 * @param \PhpParser\Node\Scalar\Encapsed $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$messages = [];
		foreach ($node->parts as $part) {
			if ($part instanceof Node\Scalar\EncapsedStringPart) {
				continue;
			}

			$typeResult = $this->ruleLevelHelper->findTypeToCheck(
				$scope,
				$part,
				'',
				static function (Type $type): bool {
					return !$type->toString() instanceof ErrorType;
				}
			);
			$partType = $typeResult->getType();
			if ($partType instanceof ErrorType) {
				continue;
			}

			$stringPartType = $partType->toString();
			if (!$stringPartType instanceof ErrorType) {
				continue;
			}

			$messages[] = sprintf(
				'Part %s (%s) of encapsed string cannot be cast to string.',
				$this->printer->prettyPrintExpr($part),
				$partType->describe(VerbosityLevel::value())
			);
		}

		return $messages;
	}

}
