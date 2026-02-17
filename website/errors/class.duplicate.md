---
title: "class.duplicate"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// file1.php
class UserService
{
	public function find(): void
	{
	}
}

// file2.php
class UserService
{
	public function find(): void
	{
	}
}
```

## Why is it reported?

The same class name is declared in multiple files within the analysed codebase. PHP does not allow two classes with the same fully-qualified name. If both files are loaded at runtime, a fatal error will occur. Even if only one is loaded at a time (e.g., via autoloading), having duplicate declarations is confusing and error-prone.

## How to fix it

Remove the duplicate declaration, or rename one of the classes:

```diff-php
 <?php declare(strict_types = 1);

 // file2.php
-class UserService
+class AdminUserService
 {
 	public function find(): void
 	{
 	}
 }
```

Or place the classes in different namespaces:

```diff-php
 <?php declare(strict_types = 1);

-// file2.php
-class UserService
+// file2.php
+namespace Admin;
+
+class UserService
 {
 	public function find(): void
 	{
 	}
 }
```
