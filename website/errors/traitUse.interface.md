---
title: "traitUse.interface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface Printable
{
	public function print(): void;
}

class Document
{
	use Printable;
}
```

## Why is it reported?

The `use` statement inside a class body is attempting to use an interface as if it were a trait. The `use` keyword in a class body is reserved for traits. Interfaces must be implemented using the `implements` keyword in the class declaration. This code will result in a fatal error at runtime.

## How to fix it

Replace the `use` statement with `implements` in the class declaration.

```diff-php
 <?php declare(strict_types = 1);

 interface Printable
 {
 	public function print(): void;
 }

-class Document
+class Document implements Printable
 {
-	use Printable;
+	public function print(): void
+	{
+		echo 'Printing document';
+	}
 }
```
