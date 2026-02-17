---
title: "whitespace.fileEnd"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

echo 'foo';

?>

```

The file above ends with a closing `?>` tag followed by trailing whitespace.

## Why is it reported?

When a PHP file ends with a closing `?>` tag followed by whitespace (spaces, tabs, or newlines), that whitespace is treated as output by the web server. This can cause "headers already sent" errors when the code tries to set HTTP headers, start sessions, or perform redirects. It can also cause unexpected output in included files.

## How to fix it

The recommended approach is to remove the closing `?>` tag entirely. The PHP manual recommends omitting the closing tag in files that contain only PHP code:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

 echo 'foo';
-
-?>
-
```

If the closing `?>` tag must be kept, remove any whitespace after it so the file ends immediately after the `?>`:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

 echo 'foo';

 ?>
-
```
