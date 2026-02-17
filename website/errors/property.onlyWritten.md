---
title: "property.onlyWritten"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class UserProcessor
{

	private string $lastProcessed;

	public function process(string $name): void
	{
		$this->lastProcessed = $name;
	}

}
```

## Why is it reported?

A private property is being written to but is never read anywhere in the class. This indicates dead code -- the property stores a value that is never used. Writing to a property without ever reading it has no observable effect and is likely a leftover from a refactoring or a mistake.

## How to fix it

If the property is not needed, remove it along with its assignments:

```diff-php
 <?php declare(strict_types = 1);

 class UserProcessor
 {

-	private string $lastProcessed;
-
 	public function process(string $name): void
 	{
-		$this->lastProcessed = $name;
+		// process the name
 	}

 }
```

If the property is meant to be read, add the code that reads it:

```diff-php
 <?php declare(strict_types = 1);

 class UserProcessor
 {

 	private string $lastProcessed;

 	public function process(string $name): void
 	{
 		$this->lastProcessed = $name;
 	}

+	public function getLastProcessed(): string
+	{
+		return $this->lastProcessed;
+	}

 }
```

If the property is read by a framework or library through reflection, you can use the `@phpstan-use` annotation or configure PHPStan to [always mark certain properties as read](https://phpstan.org/developing-extensions/always-read-written-properties).
