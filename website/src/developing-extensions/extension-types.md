---
title: Extension Types
---

PHPStan's behavior can be customized in various ways.

Core concepts
-------------------

Many basic concepts about static analysis are shared among all the extension types.

[Learn about core concepts »](/developing-extensions/core-concepts)

Custom rules
-------------------

PHPStan allows writing custom rules to check for specific situations in your own codebase.

[Learn more »](/developing-extensions/rules)

Error formatters
------------------

PHPStan outputs errors via so-called error formatters. You can implement your own format.

[Learn more »](/developing-extensions/error-formatters)

Class reflection extensions
------------------

Classes in PHP can expose "magic" properties and methods decided in run-time using class methods like `__get`, `__set`, and `__call`. Because PHPStan is all about static analysis (testing code for errors without running it), it has to know about those properties and methods beforehand.

[Learn more »](/developing-extensions/class-reflection-extensions)

Dynamic return type extensions
-------------------

If the return type of a method is not always the same, but depends on an argument passed to the method, you can specify the return type by writing and registering an extension.

[Learn more »](/developing-extensions/dynamic-return-type-extensions)

Dynamic throw type extensions
-------------------

To support [precise try-catch-finally analysis](/blog/precise-try-catch-finally-analysis), you can write a dynamic throw type extension to describe functions and methods that might throw an exception only when specific types of arguments are passed during a call.

[Learn more »](/developing-extensions/dynamic-throw-type-extensions)

Type-specifying extensions
-------------------

These extensions allow you to specify types of expressions based on certain type-checking function and method calls, like `is_int()` or `self::assertNotNull()`.

[Learn more »](/developing-extensions/type-specifying-extensions)

Custom PHPDoc Types
-------------------

PHPStan lets you override how it converts PHPDoc AST coming from its phpdoc-parser library into its typesystem representation. This can be used to introduce custom utility types.

[Learn more »](/developing-extensions/custom-phpdoc-types)

Always-read and written properties
-------------------

This extension allows you to mark private properties as always-read and written even if the surrounding code doesn't look like that.

[Learn more »](/developing-extensions/always-read-written-properties)

Always-used class constants
-------------------

This extension allows you to mark private class constants as always-used even if the surrounding code doesn't look like that.

[Learn more »](/developing-extensions/always-used-class-constants)

Allowed subtypes
-------------------

PHP language doesn't have a concept of sealed classes - a way to restrict class hierarchies and provide more control over inheritance. So any interface or non-final class can have an infinite number of child classes. But PHPStan provides an extension type to tell the analyzer the complete list of allowed child classes.

[Learn more »](/developing-extensions/allowed-subtypes)
