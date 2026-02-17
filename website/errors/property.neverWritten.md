---
title: "property.neverWritten"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Queue
{
	private(set) int $count = 0;

	public function getCount(): int
	{
		return $this->count;
	}
}
```

## Why is it reported?

The property is declared as writable-only (using asymmetric visibility with `private(set)` or similar), but it is never actually written to anywhere in the code. This suggests the property is unused or the write operations are missing. A writable property that is never written serves no purpose.

PHPStan reports this as part of its dead code detection. Unused properties add unnecessary complexity and can indicate incomplete implementations.

## How to fix it

If the property should be written, add the missing write operations:

```diff-php
 <?php declare(strict_types = 1);

 class Queue
 {
 	private(set) int $count = 0;

+	public function increment(): void
+	{
+		$this->count++;
+	}
+
 	public function getCount(): int
 	{
 		return $this->count;
 	}
 }
```

If the property is no longer needed, remove it:

```diff-php
 <?php declare(strict_types = 1);

 class Queue
 {
-	private(set) int $count = 0;
-
-	public function getCount(): int
-	{
-		return $this->count;
-	}
 }
```
