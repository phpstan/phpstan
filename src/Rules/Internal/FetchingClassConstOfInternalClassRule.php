<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\InternableReflection;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;

class FetchingClassConstOfInternalClassRule implements \PHPStan\Rules\Rule
{

	/** @var Broker */
	private $broker;

	/** @var RuleLevelHelper */
	private $ruleLevelHelper;

	/** @var InternalScopeHelper */
	private $internalScopeHelper;

	public function __construct(
		Broker $broker,
		RuleLevelHelper $ruleLevelHelper,
		InternalScopeHelper $internalScopeHelper
	)
	{
		$this->broker = $broker;
		$this->ruleLevelHelper = $ruleLevelHelper;
		$this->internalScopeHelper = $internalScopeHelper;
	}

	public function getNodeType(): string
	{
		return ClassConstFetch::class;
	}

	/**
	 * @param ClassConstFetch $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->name instanceof Identifier) {
			return [];
		}

		$constantName = $node->name->name;
		$referencedClasses = [];

		if ($node->class instanceof Name) {
			$referencedClasses[] = $scope->resolveName($node->class);
		} else {
			$classTypeResult = $this->ruleLevelHelper->findTypeToCheck(
				$scope,
				$node->class,
				'', // We don't care about the error message
				function (Type $type) use ($constantName) {
					return $type->canAccessConstants()->yes() && $type->hasConstant($constantName);
				}
			);

			if ($classTypeResult->getType() instanceof ErrorType) {
				return [];
			}

			$referencedClasses = $classTypeResult->getReferencedClasses();
		}

		$errors = [];

		foreach ($referencedClasses as $referencedClass) {
			try {
				$class = $this->broker->getClass($referencedClass);
			} catch (\PHPStan\Broker\ClassNotFoundException $e) {
				continue;
			}

			$classConstFile = $class->getFileName();
			if ($classConstFile === false) {
				continue;
			}

			if ($this->internalScopeHelper->isFileInInternalPaths($classConstFile)) {
				continue;
			}

			if ($class->isInternal()) {
				$errors[] = sprintf(
					'Fetching class constant %s of internal class %s.',
					$constantName,
					$referencedClass
				);
			}

			if (!$class->hasConstant($constantName)) {
				continue;
			}

			$constantReflection = $class->getConstant($constantName);

			if (!$constantReflection instanceof InternableReflection || !$constantReflection->isInternal()) {
				continue;
			}

			$errors[] = sprintf(
				'Fetching internal class constant %s of class %s.',
				$constantName,
				$referencedClass
			);
		}

		return $errors;
	}

}
