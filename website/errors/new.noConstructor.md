---
title: "new.noConstructor"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
}

new Foo(1, 2, 3); // ERROR: Class Foo does not have a constructor and must be instantiated without any parameters.
```

## Why is it reported?

A class that does not define a `__construct()` method is being instantiated with arguments. Since the class has no constructor, it does not accept any parameters during instantiation. Passing arguments to `new` for such a class is a runtime error in PHP.

## How to fix it

Remove the arguments from the instantiation:

```diff-php
 <?php declare(strict_types = 1);

-new Foo(1, 2, 3);
+new Foo();
```

Or add a constructor to the class if the parameters are needed:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
+	public function __construct(
+		private int $a,
+		private int $b,
+		private int $c,
+	) {
+	}
 }

 new Foo(1, 2, 3);
```
