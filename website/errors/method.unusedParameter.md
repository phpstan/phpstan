---
title: "method.unusedParameter"
shortDescription: "A private method parameter is never used in the method body."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	private function doFoo(int $value, string $unused): void
	{
		echo $value;
	}
}
```

## Why is it reported?

The private method declares the parameter `$unused` but never reads it in its body. A parameter that no code path uses is dead — it either signals a leftover from refactoring or a mistake where the parameter was meant to be used.

Only **private** methods are reported. A public or protected method's signature may be dictated by an interface, a parent class, or an override, so its parameters cannot always be removed. A private method has no callers outside the class, so its signature is not a contract. Magic methods (whose names start with `__`) are excluded because the engine dictates their signature, and the constructor has its own [`constructor.unusedParameter`](/error-identifiers/constructor.unusedParameter) rule. Parameters referenced by a `@phpstan-assert` tag or a conditional return type are also not reported.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Remove the unused parameter:

```diff-php
-	private function doFoo(int $value, string $unused): void
+	private function doFoo(int $value): void
 	{
 		echo $value;
 	}
```

Or use the parameter in the method body if it was intended to be read:

```diff-php
 	private function doFoo(int $value, string $unused): void
 	{
 		echo $value;
+		echo $unused;
 	}
```
