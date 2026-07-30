---
title: "switch.duplicateCase"
shortDescription: "A switch case repeats a value already matched by an earlier case."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	switch ($i) {
		case 1:
			break;
		case 2:
			break;
		case 1:
			break;
	}
}
```

## Why is it reported?

A `switch` matches its cases from top to bottom and executes the first one that matches. When two cases have the same value, the later one can never be reached — the earlier case always wins. This is almost always a copy-paste mistake or a leftover from refactoring.

Because `switch` compares with loose `==`, values that are only loosely equal count as duplicates too. For example `1`, `'1'`, `1.0` and `true` all match the same subject value and cannot be told apart by a `switch`:

```php
<?php declare(strict_types = 1);

function doFoo(mixed $m): void
{
	switch ($m) {
		case 1:
			break;
		case '1': // duplicate of case 1
			break;
	}
}
```

## How to fix it

Remove the duplicate case if it is redundant:

```diff-php
 switch ($i) {
 	case 1:
 		break;
 	case 2:
 		break;
-	case 1:
-		break;
 }
```

If the later case was meant to match a different value, correct it:

```diff-php
 switch ($i) {
 	case 1:
 		break;
 	case 2:
 		break;
-	case 1:
+	case 3:
 		break;
 }
```
