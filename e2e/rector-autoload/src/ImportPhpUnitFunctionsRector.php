<?php

declare(strict_types=1);

namespace Luxemate\PhpstanRectorError\Rector\Rule;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ImportPhpUnitFunctionsRector extends AbstractRector
{
	private const PHPUNIT_FUNC_NAMESPACE = 'PHPUnit\\Framework\\';

	public function refactor(Node $node): ?Node
	{
		if (!$node instanceof Node\Name) {
			return null;
		}

		/** @phpstan-ignore classConstant.internal */
		$parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);

		if (!$parentNode instanceof Node\Expr\FuncCall) {
			return null;
		}

		$resolvedName = $node->getAttribute(AttributeKey::RESOLVED_NAME);

		// If node has a resolved name, then it's probably an internal function
		if ($resolvedName instanceof Node\Name\FullyQualified) {
			return null;
		}

		/** @var string $functionName */
		$functionName = $this->getName($node);

		// If function name is FQCN it's already imported or is a member of this file's namespace
		if (str_contains($functionName, '\\')) {
			return null;
		}

		$functionFullyQualifiedName = self::PHPUNIT_FUNC_NAMESPACE.$functionName;

		if (!function_exists($functionFullyQualifiedName)) {
			return null;
		}

		return new Node\Name\FullyQualified($functionFullyQualifiedName);
	}

	public function getRuleDefinition(): RuleDefinition
	{
		return new RuleDefinition(
			'Adds import for non-imported PHPUnit functions.',
			[]
		);
	}

	public function getNodeTypes(): array
	{
		return [Node\Name::class];
	}
}
