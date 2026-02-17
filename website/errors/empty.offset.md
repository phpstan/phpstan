---
title: "empty.offset"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @param array{name: string} $data */
function check(array $data): void
{
	if (empty($data['age'])) {
		echo 'No age';
	}
}
```

## Why is it reported?

The offset used inside `empty()` does not exist on the given type. The `empty()` construct checks whether an array offset exists and has a non-empty value. When PHPStan can determine that the offset never exists on the array type, using `empty()` on it always evaluates to `true`, which indicates a logic error such as a typo in the key name or a wrong variable.

In the example above, the array shape `array{name: string}` does not have an `'age'` key, so `empty($data['age'])` is always `true`.

## How to fix it

Use the correct offset name that exists in the array type:

```diff-php
 <?php declare(strict_types = 1);

 /** @param array{name: string} $data */
 function check(array $data): void
 {
-	if (empty($data['age'])) {
-		echo 'No age';
+	if ($data['name'] === '') {
+		echo 'No name';
 	}
 }
```

Or update the type to include the expected offset:

```diff-php
 <?php declare(strict_types = 1);

-/** @param array{name: string} $data */
+/** @param array{name: string, age?: int} $data */
 function check(array $data): void
 {
 	if (empty($data['age'])) {
 		echo 'No age';
 	}
 }
```
