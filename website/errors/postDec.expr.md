---
title: "postDec.expr"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

date('j. n. Y')--;
```

## Why is it reported?

The post-decrement operator (`--`) can only be applied to variables, properties, array offsets, or static properties. Applying it to a non-variable expression such as a function call result is not valid because PHP cannot assign the decremented value back to anything.

In the example above, `date('j. n. Y')` returns a temporary string value that has no storage location, so the `--` operator cannot decrement it.

## How to fix it

Store the value in a variable first, then apply the decrement operator to that variable:

```diff-php
 <?php declare(strict_types = 1);

-date('j. n. Y')--;
+$date = date('j. n. Y');
+$date--;
```
