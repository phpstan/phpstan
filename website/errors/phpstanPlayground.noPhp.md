---
title: "phpstanPlayground.noPhp"
ignorable: false
---

## Code example

```php
This is not PHP code.
It's just plain text without the opening PHP tag.
```

## Why is it reported?

The submitted code in the [PHPStan Playground](https://phpstan.org/try) does not contain any PHP code. The file consists entirely of inline HTML without any `<?php` opening tag, so the PHP parser treats the entire content as non-PHP markup.

This error is not ignorable because there is no PHP code for PHPStan to analyse.

## How to fix it

Add the `<?php` opening tag at the beginning of the code:

```diff-php
+<?php declare(strict_types = 1);
+
+function greet(string $name): string
+{
+	return 'Hello, ' . $name;
+}
```
