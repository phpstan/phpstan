---
title: Extension Types
---

<div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">

Please note that this website section is under development as of December 2020. The plan is to write about development of custom extensions in much more detail. Thanks for understanding.

</div>

PHPStan's behavior can be customized in various ways.

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

Type-specifying extensions
-------------------

These extensions allow you to specify types of expressions based on certain type-checking function and method calls, like `is_int()` or `self::assertNotNull()`.

[Learn more »](/developing-extensions/type-specifying-extensions)
