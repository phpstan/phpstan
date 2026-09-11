---
title: "method.unusedParameterFlow"
shortDescription: "A private method parameter is read, but only to compute values that are themselves never used."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	private function doFoo(int $input): void
	{
		while (rand(0, 1)) {
			$input = $input + 1;
		}
	}
}
```

## Why is it reported?

The private method's parameter `$input` *is* read — but only by `$input = $input + 1`, whose result no code ever observes. The parameter's value feeds a closed computation that produces nothing.

This is different from [`method.unusedParameter`](/error-identifiers/method.unusedParameter), where the parameter is never read at all. Here the value flows through further computation, but that computation is itself dead, so the parameter has no effect on the program.

Only **private** methods are reported. A public or protected method's signature may be dictated by an interface, a parent class, or an override, so its parameters cannot always be removed. Magic methods (whose names start with `__`) are excluded, and the constructor has its own [`constructor.unusedParameterFlow`](/error-identifiers/constructor.unusedParameterFlow) rule. Parameters referenced by a `@phpstan-assert` tag or a conditional return type are also not reported.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Remove the parameter and the dead computation if the result is not needed:

```diff-php
-	private function doFoo(int $input): void
+	private function doFoo(): void
 	{
-		while (rand(0, 1)) {
-			$input = $input + 1;
-		}
 	}
```

Or use the computed value for something observable if that was the intent:

```diff-php
 	private function doFoo(int $input): void
 	{
 		while (rand(0, 1)) {
 			$input = $input + 1;
 		}
+		echo $input;
 	}
```
