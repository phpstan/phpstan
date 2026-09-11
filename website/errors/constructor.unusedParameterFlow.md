---
title: "constructor.unusedParameterFlow"
shortDescription: "A constructor parameter is read, but only to compute values that are themselves never used."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function __construct(int $input)
	{
		while (rand(0, 1)) {
			$input = $input + 1;
		}
	}
}
```

## Why is it reported?

The constructor's parameter `$input` *is* read — but only by `$input = $input + 1`, whose result no code ever observes. The parameter's value feeds a closed computation that produces nothing.

This is different from [`constructor.unusedParameter`](/error-identifiers/constructor.unusedParameter), where the parameter is never read at all. Here the value flows through further computation, but that computation is itself dead, so the parameter has no effect on the object.

This only applies to non-promoted parameters. Constructor-promoted parameters (those with visibility keywords like `public`, `protected`, or `private`) are excluded because they initialize properties. Parameters referenced by a `@phpstan-assert` tag or a conditional return type are also not reported.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Remove the parameter and the dead computation if the result is not needed:

```diff-php
-	public function __construct(int $input)
+	public function __construct()
 	{
-		while (rand(0, 1)) {
-			$input = $input + 1;
-		}
 	}
```

Or promote the parameter to a property if the value should be kept:

```diff-php
-	public function __construct(int $input)
+	public function __construct(private int $input)
 	{
-		while (rand(0, 1)) {
-			$input = $input + 1;
-		}
 	}
```
