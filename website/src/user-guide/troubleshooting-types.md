---
title: Troubleshooting Types
---

PHPStan can sometimes resolve the type of an expression to something that you didn't expect. This guide is here to help you troubleshoot where the unexpected type might be coming from. Here's the list of all the places PHPStan is getting its type information from.

Native types
---------

PHPStan uses native parameter types, return types, and property types to inform the analysis about types.

```php
private int $prop;

function foo(Foo $foo): Bar
{
    // ...
}
```

PHPDoc types
---------

 PHPDoc tags `@param` and `@return` [among others](/writing-php-code/phpdocs-basics) are used to convey the same types that can be expressed natively and are checked in PHP runtime (like `int` or `object` for example), but also [additional types](/writing-php-code/phpdoc-types) that are meaningful only when checked by the static analyser.

 These types are interpreted alongside native types and combined together when both are present:

 ```php
 /** @param array<int, Item> $items */
 function foo(array $items)
 {

 }
 ```

Stub files
---------

Besides the original code that's analysed, PHPStan also takes advantage of so-called [stub files](/user-guide/stub-files) that are used for overriding wrong 3rd party PHPDocs. These stub files can come from [PHPStan extensions](/user-guide/extension-library), but you can also write your own directly in the project.

Internal stubs
---------

PHPStan needs to know precise parameter and return types of built-in PHP functions and those coming from loaded PHP extensions. There are three separate sources for that:

* PHPStan-maintained [`functionMap.php`](https://github.com/phpstan/phpstan-src/blob/1.12.x/resources/functionMap.php), and [deltas](https://github.com/phpstan/phpstan-src/tree/1.12.x/resources) for different PHP versions
* [jetbrains/phpstorm-stubs](https://github.com/jetbrains/phpstorm-stubs)
* [Official stubs](https://github.com/phpstan/php-8-stubs) extracted from [php-src](https://github.com/php/php-src) if you're on PHP 8 and later
* For custom PHP extensions that are not present in `jetbrains/phpstorm-stubs`, their [PHP reflection data](https://www.php.net/manual/en/book.reflection.php) is used.

The way these are combined together to offer the full picture is a very complex logic and subject to frequent change as bugs are getting fixed and improvements made.

Local type narrowing
---------

There are many ways how to narrow a type locally in a function body. [This guide](/writing-php-code/narrowing-types) talks about that.

This includes custom [type-specifying extensions](/developing-extensions/type-specifying-extensions), `@phpstan-assert` tags, and [inline PHPDoc `@var` tags](/writing-php-code/phpdocs-basics#inline-%40var).

Dynamic return type extensions
---------

There are many [dynamic return type extensions](/developing-extensions/dynamic-return-type-extensions) registered for built-in PHP functions. They might come from [PHPStan extensions](/user-guide/extension-library) and from your own project configuration too.

Debugging
---------

You can use `PHPStan\dumpType()` function in your code to see what type PHPStan resolves an expression to:

```php
\PHPStan\dumpType(1 + 1); // Reports: Dumped type: 2
```

Re-run the PHPStan analysis on the code that has this function call to see the result.

Of course this function should never become part of code running in production, so don't forget to delete it!

What PHPStan doesn't do
---------

PHPStan doesn't descend into a called function's body to see what's going on there. The native types, the PHPDocs, and the registered [dynamic return type](/developing-extensions/dynamic-return-type-extensions) and [type-specifying extensions](/developing-extensions/type-specifying-extensions) are everything that's used to figure out how the types are going to impacted.
