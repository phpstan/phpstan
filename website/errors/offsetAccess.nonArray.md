---
title: "offsetAccess.nonArray"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(stdClass $obj): void
{
	[$a, $b] = $obj; // error: Cannot use array destructuring on stdClass.
}
```

## Why is it reported?

Array destructuring (`[$a, $b] = ...`) requires the right-hand side to be an array or an object implementing `ArrayAccess`. When the expression being destructured is neither of those types, PHP cannot access its elements by offset, which will result in an error at runtime.

## How to fix it

Ensure the value being destructured is an array.

```diff-php
-function doFoo(stdClass $obj): void
+function doFoo(array $data): void
 {
-	[$a, $b] = $obj;
+	[$a, $b] = $data;
 }
```

If the value can sometimes be `null`, check for it before destructuring.

```diff-php
 function doFoo(?array $data): void
 {
-	[$a, $b] = $data;
+	if ($data !== null) {
+		[$a, $b] = $data;
+	}
 }
```
