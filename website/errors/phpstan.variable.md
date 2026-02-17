---
title: "phpstan.variable"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

use function PHPStan\Testing\assertVariableCertainty;
use PHPStan\TrinaryLogic;

function doFoo(): void
{
	if (rand(0, 1)) {
		$a = 1;
	}

	assertVariableCertainty(TrinaryLogic::createYes(), $a);
}
```

## Why is it reported?

This is an internal testing function used in PHPStan's test suite. The `assertVariableCertainty()` call expects a specific certainty level for a variable (Yes, No, or Maybe), but the actual certainty level is different. In the example above, the variable `$a` might or might not exist (certainty: Maybe), but the assertion expects it to always exist (certainty: Yes).

## How to fix it

Fix the expected certainty to match the actual state:

```diff-php
 <?php declare(strict_types = 1);

-assertVariableCertainty(TrinaryLogic::createYes(), $a);
+assertVariableCertainty(TrinaryLogic::createMaybe(), $a);
```
