---
title: "varTag.noVariable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(array $list): void
{
    /** @var int */
    foreach ($list as $key => $val) {
        // ambiguous: which variable does @var apply to?
    }
}
```

## Why is it reported?

The `@var` PHPDoc tag does not specify a variable name, and the context has multiple variables that it could apply to. When there is ambiguity about which variable the `@var` tag refers to, PHPStan requires the variable name to be specified explicitly.

This is reported in the following situations:

- A `@var` tag without a variable name above a `foreach` loop with multiple variables (key and value).
- A `@var` tag without a variable name above a `static` declaration with multiple variables.
- A `@var` tag without a variable name above a `global` declaration with multiple variables.
- A `@var` tag without a variable name above a statement that is not a variable assignment.

```php
<?php declare(strict_types = 1);

function doBar(): void
{
    /** @var int */
    static $a, $b;
    // ambiguous: does @var apply to $a or $b?
}
```

## How to fix it

Add the variable name to the `@var` tag:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(array $list): void
 {
-    /** @var int */
+    /** @var int $val */
     foreach ($list as $key => $val) {
     }
 }
```

For multiple variables, use multiple `@var` tags with explicit names:

```diff-php
 <?php declare(strict_types = 1);

 function doBar(): void
 {
-    /** @var int */
-    static $a, $b;
+    /**
+     * @var int $a
+     * @var string $b
+     */
+    static $a, $b;
 }
```
