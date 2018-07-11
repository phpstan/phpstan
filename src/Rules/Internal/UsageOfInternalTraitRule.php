<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

use PhpParser\Node;
use PhpParser\Node\Stmt\TraitUse;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class UsageOfInternalTraitRule implements \PHPStan\Rules\Rule
{

	/** @var Broker */
	private $broker;

	/** @var InternalScopeHelper */
	private $internalScopeHelper;

	public function __construct(Broker $broker, InternalScopeHelper $internalScopeHelper)
	{
		$this->broker = $broker;
		$this->internalScopeHelper = $internalScopeHelper;
	}

	public function getNodeType(): string
	{
		return TraitUse::class;
	}

	/**
	 * @param TraitUse $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$classReflection = $scope->getClassReflection();
		if ($classReflection === null) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$errors = [];
		$className = $classReflection->getName();

		foreach ($node->traits as $traitNameNode) {
			$traitName = (string) $traitNameNode;

			try {
				$trait = $this->broker->getClass($traitName);

				if ($trait->isInternal()) {
					continue;
				}

				$traitFile = $trait->getFileName();
				if ($traitFile === false) {
					continue;
				}

				if ($this->internalScopeHelper->isFileInInternalPaths($traitFile)) {
					continue;
				}

				$errors[] = sprintf(
					'Usage of internal trait %s in class %s.',
					$traitName,
					$className
				);
			} catch (\PHPStan\Broker\ClassNotFoundException $e) {
				continue;
			}
		}

		return $errors;
	}

}
