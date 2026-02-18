---
title: "phpParser.nodeConnectingAttribute"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

/** @implements Rule<Node\Stmt\Echo_> */
class MyRule implements Rule
{
	public function getNodeType(): string
	{
		return Node\Stmt\Echo_::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$parent = $node->getAttribute('parent');
		return [];
	}
}
```

## Why is it reported?

The code accesses a PHP-Parser node attribute (`parent`, `previous`, or `next`) that was previously provided by the `NodeConnectingVisitor` but is no longer available. PHPStan no longer uses this visitor, so these attributes are not set on AST nodes.

Learn more: [Preprocessing AST for Custom Rules](/blog/preprocessing-ast-for-custom-rules)

## How to fix it

Use PHPStan's built-in node types that provide parent/sibling information instead of relying on node attributes:

```diff-php
-$parent = $node->getAttribute('parent');
+// Use PHPStan's custom node types that provide structural context
```

See the blog post [Preprocessing AST for Custom Rules](/blog/preprocessing-ast-for-custom-rules) for the recommended approach to accessing parent and sibling nodes in PHPStan rules.
