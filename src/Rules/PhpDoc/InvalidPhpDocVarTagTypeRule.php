<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\ClassNameNodePair;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ErrorType;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\NeverType;
use function sprintf;

class InvalidPhpDocVarTagTypeRule implements Rule
{

	/** @var FileTypeMapper */
	private $fileTypeMapper;

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Rules\ClassCaseSensitivityCheck */
	private $classCaseSensitivityCheck;

	/** @var bool */
	private $checkClassCaseSensitivity;

	public function __construct(
		FileTypeMapper $fileTypeMapper,
		Broker $broker,
		ClassCaseSensitivityCheck $classCaseSensitivityCheck,
		bool $checkClassCaseSensitivity
	)
	{
		$this->fileTypeMapper = $fileTypeMapper;
		$this->broker = $broker;
		$this->classCaseSensitivityCheck = $classCaseSensitivityCheck;
		$this->checkClassCaseSensitivity = $checkClassCaseSensitivity;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node::class;
	}

	/**
	 * @param \PhpParser\Node $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return \PHPStan\Rules\RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (
			!$node instanceof Node\Stmt\Foreach_
			&& !$node instanceof Node\Expr\Assign
			&& !$node instanceof Node\Expr\AssignRef
			&& !$node instanceof Node\Stmt\Static_
		) {
			return [];
		}

		$docComment = $node->getDocComment();
		if ($docComment === null) {
			return [];
		}

		$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
			$scope->getFile(),
			$scope->isInClass() ? $scope->getClassReflection()->getName() : null,
			$scope->isInTrait() ? $scope->getTraitReflection()->getName() : null,
			$docComment->getText()
		);

		$errors = [];
		foreach ($resolvedPhpDoc->getVarTags() as $name => $varTag) {
			$varTagType = $varTag->getType();
			$identifier = 'PHPDoc tag @var';
			if (is_string($name)) {
				$identifier .= sprintf(' for variable $%s', $name);
			}
			if (
				$varTagType instanceof ErrorType
				|| ($varTagType instanceof NeverType && !$varTagType->isExplicit())
			) {
				$errors[] = RuleErrorBuilder::message(sprintf('%s contains unresolvable type.', $identifier))->line($docComment->getLine())->build();
				continue;
			}

			$referencedClasses = $varTagType->getReferencedClasses();
			foreach ($referencedClasses as $referencedClass) {
				if ($this->broker->hasClass($referencedClass)) {
					if ($this->broker->getClass($referencedClass)->isTrait()) {
						$errors[] = RuleErrorBuilder::message(sprintf(
							sprintf('%s has invalid type %%s.', $identifier),
							$referencedClass
						))->build();
					}
					continue;
				}

				$errors[] = RuleErrorBuilder::message(sprintf(
					sprintf('%s contains unknown class %%s.', $identifier),
					$referencedClass
				))->build();
			}

			if (!$this->checkClassCaseSensitivity) {
				continue;
			}

			$errors = array_merge(
				$errors,
				$this->classCaseSensitivityCheck->checkClassNames(array_map(static function (string $class) use ($node): ClassNameNodePair {
					return new ClassNameNodePair($class, $node);
				}, $referencedClasses))
			);
		}

		return $errors;
	}

}
