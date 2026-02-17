---
title: "whitespace.bom"
ignorable: true
---

## Code example

A PHP file that starts with a UTF-8 Byte Order Mark (BOM) character (`EF BB BF` in hex):

```php
<?php declare(strict_types = 1);

echo 'Hello';
```

The BOM is an invisible character at the very beginning of the file, before the `<?php` opening tag.

## Why is it reported?

The file begins with a UTF-8 BOM (Byte Order Mark) character. While the BOM is valid UTF-8, it causes problems in PHP files:

- When PHP outputs content to the browser, the BOM is sent before any headers, which can cause "headers already sent" errors.
- It can interfere with `header()`, `session_start()`, and other functions that must be called before any output.
- It can produce unexpected whitespace in HTML output.

## How to fix it

Remove the BOM character from the beginning of the file. Most code editors have an option to save files without BOM:

- In **VS Code**: Change the encoding in the status bar from "UTF-8 with BOM" to "UTF-8".
- In **PhpStorm**: Go to File > File Properties > Remove BOM.
- In **Sublime Text**: Go to File > Save with Encoding > UTF-8.

Alternatively, use a command-line tool to strip the BOM:

```bash
sed -i '1s/^\xEF\xBB\xBF//' file.php
```
