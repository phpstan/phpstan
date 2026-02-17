---
title: "enum.duplicateMethod"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Suit: string
{
	case Hearts = 'hearts';

	public function label(): string
	{
		return $this->value;
	}

	public function label(): string
	{
		return strtoupper($this->value);
	}
}
```

## Why is it reported?

An enum declares the same method more than once. PHP does not allow two methods with the same name in a single enum. This is a fatal error.

In the example above, the method `label()` is declared twice in the enum `Suit`.

## How to fix it

Remove the duplicate method declaration, keeping only one:

```diff-php
 <?php declare(strict_types = 1);

 enum Suit: string
 {
 	case Hearts = 'hearts';

 	public function label(): string
 	{
-		return $this->value;
-	}
-
-	public function label(): string
-	{
 		return strtoupper($this->value);
 	}
 }
```

If the duplicate methods were intended to have different behaviour, rename one of them:

```diff-php
 <?php declare(strict_types = 1);

 enum Suit: string
 {
 	case Hearts = 'hearts';

 	public function label(): string
 	{
 		return $this->value;
 	}

-	public function label(): string
+	public function upperLabel(): string
 	{
 		return strtoupper($this->value);
 	}
 }
```
