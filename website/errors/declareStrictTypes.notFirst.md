---
title: "declareStrictTypes.notFirst"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

use stdClass;

declare(strict_types=1);
```

## Why is it reported?

PHP requires `declare(strict_types=1)` to be the very first statement in a file (after the opening `<?php` tag). If any other statement appears before it (including `use` imports, namespace declarations, or other code), the `declare` statement has no effect and PHP may emit an error. This is a fundamental language requirement, not just a convention.

## How to fix it

Move the `declare(strict_types=1)` statement to be the first statement in the file, immediately after the opening `<?php` tag:

```diff-php
-<?php declare(strict_types = 1);
-
-use stdClass;
-
-declare(strict_types=1);
+<?php declare(strict_types = 1);
+
+use stdClass;
```

If the file contains inline HTML before the PHP block, the `declare` statement must still be the first PHP statement:

```diff-php
-<html>
 <?php declare(strict_types = 1);
```
