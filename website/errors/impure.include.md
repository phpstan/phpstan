---
title: "impure.include"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

#[\Pure]
function loadTranslations(string $locale): array
{
	return include __DIR__ . '/translations/' . $locale . '.php';
}
```

## Why is it reported?

A function or method marked as pure must not have side effects and must depend only on its parameters. Using `include` or `include_once` inside a pure function is an impure operation because it reads a file from disk and executes its contents. File system access is inherently impure since the result can vary depending on external state such as file contents, file existence, or file permissions.

## How to fix it

Remove the `#[\Pure]` attribute if the function needs to use `include`:

```diff-php
 <?php declare(strict_types = 1);

-#[\Pure]
 function loadTranslations(string $locale): array
 {
 	return include __DIR__ . '/translations/' . $locale . '.php';
 }
```

Alternatively, restructure the code so that the file loading happens outside the pure function, and pass the data as a parameter:

```diff-php
 <?php declare(strict_types = 1);

-#[\Pure]
-function loadTranslations(string $locale): array
-{
-	return include __DIR__ . '/translations/' . $locale . '.php';
-}
+#[\Pure]
+function filterTranslations(array $translations, string $key): string
+{
+	return $translations[$key] ?? $key;
+}
```
