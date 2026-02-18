---
title: "cast.voidNotSupported"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

(void) doSomething();
```

## Why is it reported?

The `(void)` cast is only available in PHP 8.5 and later. The analysed code uses a `(void)` cast but the configured PHP version is older than 8.5, which means this code will cause a syntax error at runtime.

## How to fix it

Upgrade to PHP 8.5 or later, or configure PHPStan to analyse against PHP 8.5+ using the [`phpVersion`](/config-reference#phpversion) configuration option.

Alternatively, remove the `(void)` cast if targeting an older PHP version:

```diff-php
 <?php declare(strict_types = 1);

-(void) doSomething();
+doSomething();
```
