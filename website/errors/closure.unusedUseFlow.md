---
title: "closure.unusedUseFlow"
shortDescription: "A variable imported into a closure's use clause is read, but only to compute values that are themselves never used."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $input): \Closure
{
	return function () use ($input): void {
		while (rand(0, 1)) {
			$input = $input + 1;
		}
	};
}
```

## Why is it reported?

The closure imports `$input` by value through the `use` clause, and that value *is* read — but only by `$input = $input + 1`, whose result no code ever observes. The imported value feeds a closed computation that produces nothing.

This is different from [`closure.unusedUse`](/error-identifiers/closure.unusedUse), where the imported variable is never read at all. Here the value flows through further computation, but that computation is itself dead, so importing the variable has no effect. This usually points to a logic error where the imported value was meant to be used.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 1 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Use the imported value for something observable if that was the intent:

```diff-php
 	return function () use ($input): void {
 		while (rand(0, 1)) {
 			$input = $input + 1;
 		}
+		echo $input;
 	};
```

Or remove the variable from the `use` clause and the dead computation:

```diff-php
-	return function () use ($input): void {
-		while (rand(0, 1)) {
-			$input = $input + 1;
-		}
+	return function (): void {
 	};
```
