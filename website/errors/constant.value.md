---
title: "constant.value"
shortDescription: "Value assigned to a global constant does not match the type configured in dynamicConstantNames."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

const DATABASE_ENGINE = false;
```

PHPStan configuration (`phpstan.neon`):

```yaml
parameters:
	dynamicConstantNames:
		DATABASE_ENGINE: int|string|null
```

## Why is it reported?

When a global constant is listed in [`dynamicConstantNames`](/config-reference#constants) with an explicit type, PHPStan checks that the value assigned via a `const` statement is compatible with the configured type. In the example above, `DATABASE_ENGINE` is configured to accept `int|string|null`, but `false` is being assigned.

This check is enabled at [level 2](/user-guide/rule-levels) and only when the [bleeding edge](/blog/what-is-bleeding-edge) configuration is enabled.

## How to fix it

Change the value to match the configured type:

```diff-php
-const DATABASE_ENGINE = false;
+const DATABASE_ENGINE = null;
```

Or update the configured type to accept the value:

```yaml
parameters:
	dynamicConstantNames:
-		DATABASE_ENGINE: int|string|null
+		DATABASE_ENGINE: int|string|bool|null
```
