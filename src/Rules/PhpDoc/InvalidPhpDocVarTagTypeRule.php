<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
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

	public function __construct(FileTypeMapper $fileTypeMapper)
	{
		$this->fileTypeMapper = $fileTypeMapper;
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
			if (
				!$varTagType instanceof ErrorType
				&& !$varTagType instanceof NeverType
			) {
				continue;
			}

			$identifier = 'PHPDoc tag @var';
			if (is_string($name)) {
				$identifier .= sprintf(' for variable $%s', $name);
			}

			$errors[] = RuleErrorBuilder::message(sprintf('%s contains unresolvable type.', $identifier))->line($docComment->getLine())->build();
		}

		return $errors;
	}

}
