<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\VerbosityLevel;

class AppendedArrayItemToStringRule implements \PHPStan\Rules\Rule
{
	/** @var \PHPStan\Rules\RuleLevelHelper */
	private $ruleLevelHelper;

	public function __construct(
		RuleLevelHelper $ruleLevelHelper
	)
	{
		$this->ruleLevelHelper = $ruleLevelHelper;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr\ArrayDimFetch::class;
	}

	/**
	 * @param \PhpParser\Node $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
	    if (version_compare(PHP_VERSION, '5.4') < 0) {
	        return [];
        }

        $nodeType = $scope->getType($node->var);
        if ($nodeType instanceof ConstantStringType) {
            return [
                sprintf(
                    'Append array to variable of invalid type %s',
                    $nodeType->describe(VerbosityLevel::typeOnly())
                )
            ];
        }

		return [];
	}
}
