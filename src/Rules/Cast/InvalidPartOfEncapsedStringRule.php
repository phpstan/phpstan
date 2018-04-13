<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ErrorType;
use PHPStan\Type\VerbosityLevel;

class InvalidPartOfEncapsedStringRule implements \PHPStan\Rules\Rule
{

	/** @var \PhpParser\PrettyPrinter\Standard */
	private $printer;

	public function __construct(\PhpParser\PrettyPrinter\Standard $printer)
	{
		$this->printer = $printer;
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

			$partType = $scope->getType($part);
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
