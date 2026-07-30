---
title: "switch.alwaysFalse"
shortDescription: "A switch case can never match the switch subject because their types do not overlap."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	switch ($i) {
		case 'foo':
			break;
	}
}
```

## Why is it reported?

The comparison between the `switch` subject and this case value is always `false`, so the case body can never execute. This happens when the case value can never be loosely equal (`==`) to any possible value of the subject — for example comparing an `int` subject against the string `'foo'`, or listing a case whose value falls outside the subject's known set of values:

```php
<?php declare(strict_types = 1);

/** @param 1|2|3 $i */
function doFoo(int $i): void
{
	switch ($i) {
		case 4: // int is never 4, always false
			break;
		case 1:
			break;
	}
}
```

It can also appear when previous cases have already exhausted every possible value of the subject, leaving a later case impossible to reach.

## How to fix it

Fix the case value so it can actually match the subject:

```diff-php
 switch ($i) {
-	case 'foo':
+	case 1:
 		break;
 }
```

Remove the case entirely if it is dead code:

```diff-php
 switch ($i) {
-	case 'foo':
-		break;
 }
```

If the subject can legitimately hold more types or values than PHPStan infers, widen its declared type so the case becomes reachable. This rule respects the [`treatPhpDocTypesAsCertain`](/config-reference#treatphpdoctypesascertain) configuration parameter — when the type mismatch comes only from PHPDoc types, setting it to `false` relaxes the check.
