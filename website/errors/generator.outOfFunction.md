---
title: "generator.outOfFunction"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

yield 1;
yield from doFoo();
```

## Why is it reported?

The `yield` and `yield from` expressions can only be used inside a function or method body. Using them at the top level of a script or in a non-function context is not valid PHP. Generators require a function scope because PHP needs to create a `Generator` object from the function's execution context.

## How to fix it

Wrap the `yield` expression inside a function or method:

```diff-php
-yield 1;
-yield from doFoo();
+function generateValues(): \Generator
+{
+	yield 1;
+	yield from doFoo();
+}
```
