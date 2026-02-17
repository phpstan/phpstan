---
title: "possiblyImpure.include"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Config
{

	/** @phpstan-pure */
	public function load(string $file): array
	{
		return include $file;
	}

}
```

## Why is it reported?

The function or method is marked as pure (via `@phpstan-pure`), but it uses `include` or `require` inside its body. Including or requiring files is a side effect because it executes arbitrary code from the filesystem, which may modify global state, define functions or classes, or produce output. This makes the function possibly impure.

A pure function must have no side effects and must depend only on its arguments.

## How to fix it

Remove the `include`/`require` statement and pass the data as a parameter instead:

```diff-php
 <?php declare(strict_types = 1);

 class Config
 {

-	/** @phpstan-pure */
-	public function load(string $file): array
+	/** @phpstan-pure */
+	public function load(array $data): array
 	{
-		return include $file;
+		return $data;
 	}

 }
```

Alternatively, if the function genuinely needs to include files, remove the `@phpstan-pure` annotation:

```diff-php
 <?php declare(strict_types = 1);

 class Config
 {

-	/** @phpstan-pure */
 	public function load(string $file): array
 	{
 		return include $file;
 	}

 }
```
