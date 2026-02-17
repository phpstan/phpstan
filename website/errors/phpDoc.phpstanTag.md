---
title: "phpDoc.phpstanTag"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/**
	 * @phpstan-pararm string $name
	 */
	public function setName(string $name): void
	{
	}
}
```

## Why is it reported?

The PHPDoc comment contains a tag starting with `@phpstan-` that is not recognized by PHPStan. This is most likely a typo in the tag name. In the example above, `@phpstan-pararm` should be `@phpstan-param`.

## How to fix it

Correct the tag name to a valid `@phpstan-` tag:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	/**
-	 * @phpstan-pararm string $name
+	 * @phpstan-param string $name
 	 */
 	public function setName(string $name): void
 	{
 	}
 }
```

Common valid `@phpstan-` tags include `@phpstan-param`, `@phpstan-return`, `@phpstan-var`, `@phpstan-template`, `@phpstan-extends`, `@phpstan-implements`, `@phpstan-type`, `@phpstan-import-type`, `@phpstan-assert`, `@phpstan-pure`, `@phpstan-impure`, `@phpstan-ignore`, and others.
