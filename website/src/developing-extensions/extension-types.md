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

Restricted usage extensions
-------------------

These extensions allow you to restrict where methods, properties, functions etc. can be accessed from without implementing full-fledged custom rules.

[Learn more »](/developing-extensions/restricted-usage-extensions)

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

Closure extensions
-------------------

These extensions allow you to override the closure type passed to function and method calls, or the `$this` variable of a closure depending on the surrounding context.

[Learn more »](/developing-extensions/closure-extensions)

Parameter out type extensions
-------------------

If a function or method modifies a by-reference parameter and the output type depends on the arguments passed, you can write a parameter out type extension to resolve it dynamically.

[Learn more »](/developing-extensions/parameter-out-type-extensions)

Expression type resolver extensions
-------------------

A catch-all extension point that lets you override the inferred type of any expression — more general than dynamic return type extensions which only work for specific method/function calls.

[Learn more »](/developing-extensions/expression-type-resolver-extensions)

Operator type specifying extensions
-------------------

If you're working with PHP extensions that overload arithmetic operators (like GMP or BCMath), you can describe how operators should infer types for your custom objects.

[Learn more »](/developing-extensions/operator-type-specifying-extensions)

Exception type resolver
-------------------

Lets you write custom logic to dynamically decide whether an exception is checked or unchecked, with access to the scope where the exception is thrown.

[Learn more »](/developing-extensions/exception-type-resolver)

Forbidden class name extensions
-------------------

If your project has classes that should never be referenced directly (like generated proxy classes), you can dynamically report their usage.

[Learn more »](/developing-extensions/forbidden-class-name-extensions)

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

Additional constructors
-------------------

If properties are initialized in methods other than the constructor (like `setUp()` in PHPUnit), you can dynamically mark those methods as constructors so they're not reported as uninitialized.

[Learn more »](/developing-extensions/additional-constructors-extensions)

Stub files extensions
-------------------

If you need to load stub files conditionally based on custom logic (like the PHP version or installed extensions), you can write a stub files extension.

[Learn more »](/developing-extensions/stub-files-extensions)

Diagnose extensions
-------------------

Custom extensions can output diagnostic information visible when running the `analyse` command with `-vvv` or the `diagnose` command.

[Learn more »](/developing-extensions/diagnose-extensions)

Deprecations
-------------------

PHPStan lets you provide custom deprecation information based on e.g. native PHP attributes. Such information is then used in e.g. `ClassReflection::isDeprecated()`

[Learn more »](/developing-extensions/custom-deprecations)
