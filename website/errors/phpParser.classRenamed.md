---
title: "phpParser.classRenamed"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

/** @implements Rule<Node> */
class MyRule implements Rule
{
	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr\ArrayItem::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		return [];
	}
}
```

## Why is it reported?

The code references a PHP-Parser class name that was renamed in PHP-Parser v5. PHPStan uses PHP-Parser v5, where several node classes were moved to new locations. Using the old class names will cause errors because these classes no longer exist under their original names.

The renamed classes include:

- `PhpParser\Node\Scalar\LNumber` -> `PhpParser\Node\Scalar\Int_`
- `PhpParser\Node\Scalar\DNumber` -> `PhpParser\Node\Scalar\Float_`
- `PhpParser\Node\Scalar\Encapsed` -> `PhpParser\Node\Scalar\InterpolatedString`
- `PhpParser\Node\Scalar\EncapsedStringPart` -> `PhpParser\Node\InterpolatedStringPart`
- `PhpParser\Node\Expr\ArrayItem` -> `PhpParser\Node\ArrayItem`
- `PhpParser\Node\Expr\ClosureUse` -> `PhpParser\Node\ClosureUse`
- `PhpParser\Node\Stmt\DeclareDeclare` -> `PhpParser\Node\DeclareItem`
- `PhpParser\Node\Stmt\PropertyProperty` -> `PhpParser\Node\PropertyItem`
- `PhpParser\Node\Stmt\StaticVar` -> `PhpParser\Node\StaticVar`
- `PhpParser\Node\Stmt\UseUse` -> `PhpParser\Node\UseItem`

## How to fix it

Replace the old class name with the new one from PHP-Parser v5:

```diff-php
 /** @implements Rule<Node> */
 class MyRule implements Rule
 {
 	public function getNodeType(): string
 	{
-		return \PhpParser\Node\Expr\ArrayItem::class;
+		return \PhpParser\Node\ArrayItem::class;
 	}

 	public function processNode(Node $node, Scope $scope): array
 	{
 		return [];
 	}
 }
```

See the [PHP-Parser v5 upgrade guide](https://github.com/nikic/PHP-Parser/blob/master/UPGRADE-5.0.md#renamed-nodes) for the full list of renamed classes.
