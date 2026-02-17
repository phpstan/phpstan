---
title: "return.nestedUnusedType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @return array<int|string>
 */
function doFoo(): array
{
	return [1, 2, 3];
}
```

## Why is it reported?

The declared return type contains a nested type that is wider than necessary based on the actual return values. In the example above, the function is declared to return `array<int|string>`, but it only ever returns arrays containing `int` values. The `string` part of the nested union type is unused and can be narrowed.

## How to fix it

Narrow the return type to match the actual return values:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @return array<int|string>
+ * @return array<int>
  */
 function doFoo(): array
 {
 	return [1, 2, 3];
 }
```
