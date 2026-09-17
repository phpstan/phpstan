---
title: "purePropertyHook.parameterByRef"
shortDescription: "Pure property hook has a by-reference parameter, which allows side effects."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

final class Foo
{
	private int $backing = 1;

	public int $value {
		/** @phpstan-pure */
		set(int &$value) {
			$this->backing = $value;
		}
	}
}
```

## Why is it reported?

A property hook (PHP 8.4+) marked as `@phpstan-pure` must not have side effects and must always behave the same for the same inputs. A parameter passed by reference (`&$value`) lets the hook modify the caller's variable, which is a side effect and contradicts purity.

Note that PHP itself does not allow by-reference parameters in property hooks — declaring one produces a fatal error (`Parameter $value of set hook must not be pass-by-reference`). This identifier exists for consistency with [`pureMethod.parameterByRef`](/error-identifiers/pureMethod.parameterByRef) and [`pureFunction.parameterByRef`](/error-identifiers/pureFunction.parameterByRef), which apply to regular methods and functions where by-reference parameters are permitted.

## How to fix it

Do not pass the hook parameter by reference. A `set` hook receives the assigned value directly:

```diff-php
 public int $value {
 	/** @phpstan-pure */
-	set(int &$value) {
+	set {
 		$this->backing = $value;
 	}
 }
```

Because a `set` hook mutates state, it is inherently impure; remove the `@phpstan-pure` annotation as well:

```diff-php
 public int $value {
-	/** @phpstan-pure */
 	set {
 		$this->backing = $value;
 	}
 }
```
