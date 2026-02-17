---
title: "pureMethod.parameterByRef"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Calculator
{
	/**
	 * @phpstan-pure
	 */
	public function increment(int &$value): int // ERROR: Method Calculator::increment() is marked as pure but parameter $value is passed by reference.
	{
		$value++;
		return $value;
	}
}
```

## Why is it reported?

A method marked as `@phpstan-pure` must not have side effects and must always return the same result for the same inputs. A parameter passed by reference (`&$value`) allows the method to modify the caller's variable, which is a side effect. This contradicts the definition of a pure method.

Even if the method does not actually modify the referenced parameter, the mere presence of a by-reference parameter signals that the method's contract allows mutation, which is incompatible with purity.

## How to fix it

Remove the by-reference parameter and return the computed value instead:

```diff-php
 <?php declare(strict_types = 1);

 class Calculator
 {
 	/**
 	 * @phpstan-pure
 	 */
-	public function increment(int &$value): int
+	public function increment(int $value): int
 	{
-		$value++;
-		return $value;
+		return $value + 1;
 	}
 }
```

If the method needs to modify the caller's variable, it is not pure. Remove the `@phpstan-pure` annotation:

```diff-php
 <?php declare(strict_types = 1);

 class Calculator
 {
-	/**
-	 * @phpstan-pure
-	 */
 	public function increment(int &$value): int
 	{
 		$value++;
 		return $value;
 	}
 }
```
