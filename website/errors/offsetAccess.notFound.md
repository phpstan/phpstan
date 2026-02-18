---
title: "offsetAccess.notFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$array = ['name' => 'John', 'age' => 30];
	echo $array['email'];
}
```

## Why is it reported?

The code accesses an array or object offset that does not exist on the given type. This access would result in an undefined offset warning (or return `null` for `ArrayAccess` implementations) at runtime.

In the example above, the array has keys `name` and `age`, but the code tries to access the key `email` which does not exist.

## How to fix it

Use an offset that exists on the type:

```diff-php
 $array = ['name' => 'John', 'age' => 30];
-echo $array['email'];
+echo $array['name'];
```

If the offset might not exist, check for its presence first:

```diff-php
 $array = ['name' => 'John', 'age' => 30];
-echo $array['email'];
+if (isset($array['email'])) {
+	echo $array['email'];
+}
```

Or add the missing key to the array:

```diff-php
-$array = ['name' => 'John', 'age' => 30];
+$array = ['name' => 'John', 'age' => 30, 'email' => 'john@example.com'];
 echo $array['email'];
```
