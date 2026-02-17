---
title: "constant.attributesNotSupported"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

// Analysed with PHP version set to < 8.5

#[\Attribute(\Attribute::TARGET_CONSTANT)]
class MyAttr {}

#[MyAttr]
const FOO = 1; // error
```

## Why is it reported?

Attributes on global constants are a feature introduced in PHP 8.5. When the configured PHP version for analysis is lower than 8.5, placing attributes on global constants is not supported and will result in a runtime error.

## How to fix it

If you need attributes on global constants, set the [`phpVersion`](/config-reference#phpversion) parameter in your PHPStan configuration to 8.5 or later:

```yaml
parameters:
    phpVersion: 80500
```

Alternatively, if you cannot upgrade, avoid using attributes on global constants and consider using class constants instead, which support attributes since PHP 8.0:

```diff-php
 <?php declare(strict_types = 1);

-#[MyAttr]
-const FOO = 1;
+class Config
+{
+    #[MyAttr]
+    const FOO = 1;
+}
```
