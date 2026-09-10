---
title: "function.unusedParameter"
shortDescription: "A function parameter is never used in the function body."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $value, string $unused): void
{
	echo $value;
}
```

## Why is it reported?

The function declares the parameter `$unused` but never reads it in its body. A parameter that no code path uses is dead — it either signals a leftover from refactoring or a mistake where the parameter was meant to be used.

Unlike methods, a plain function has no interface, parent, or override that could dictate its signature, so an unused parameter is always safe to remove. Parameters referenced by a `@phpstan-assert` tag or a conditional return type are not reported, since they serve the call site.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Remove the unused parameter:

```diff-php
-function doFoo(int $value, string $unused): void
+function doFoo(int $value): void
 {
 	echo $value;
 }
```

Or use the parameter in the function body if it was intended to be read:

```diff-php
 function doFoo(int $value, string $unused): void
 {
 	echo $value;
+	echo $unused;
 }
```
