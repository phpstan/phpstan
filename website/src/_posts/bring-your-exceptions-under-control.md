---
title: "Bring your exceptions under control with @throws"
date: 2021-05-12
tags: guides
---

The most common detected bugs [^telemetry] by PHPStan are:

[^telemetry]: This is just from my personal experience and anecdotal evidence - PHPStan doesn't perform any telemetry on user's code.

* Calling unknown method on an object
* Accessing unknown property on an object
* Passing wrong types of arguments to methods and functions

Static analysers put `@param` and `@return` PHPDoc validation on the map, people have been benefiting from those errors being reported for the past 4,5 years, and fixed a lot of typehints in their own and third party codebases on the way.

But one aspect of the PHP language had been flying under the radar this whole time. Exceptions can serve you well, but they can also wreak havoc in your codebase. Similarly to the PHP landscape before static analysis came to it, the flow of exceptions throughout applications is still largely unchecked. Developers forget to handle error states, catch expected exceptions, and that leads to applications crashing in production.

PHPStan can now be used to bring exceptions under control, and in this article I'm gonna describe how. You can take advantage of these features with [PHPStan 0.12.87](https://github.com/phpstan/phpstan/releases/tag/0.12.87) or later.

Checked vs. unchecked exceptions
------------------------

PHPStan draws its inspiration from Java. It divides exceptions into two categories. **Checked exceptions** are expected to be handled by the code at the call site of a function that throws a checked exception. Examples of such exceptions are:

* Database record has not been found (there's a nonexistent ID in the URL) - we're expected to catch such exception and show a 404 Not Found page
* Customer cannot buy this product because it's been sold out - an expected situation, the customer should see an error message while adding the product to their cart

```php
/** @throws ProductVariantNotAvailableForUserException */
public function addVariantToCart(int $userId, int $variantId): void
{
	// ...
}
```

On the other hand, **unchecked exceptions** are not welcome when they're thrown, and they're not supposed to be caught and handled because they present an unrecoverable state of the application. We don't know how we should continue the execution if an unchecked exception is thrown:

* A file that's supposed to always exist was not found
* Database connection failed
* TypeError has been thrown by PHP - function expected `string` but an `int` was passed into it

It's better to let them bubble up to the topmost layer of the code, log them, alert the developers, show the user a 500 Internal Server Error, and fix the cause as soon a possible - whether it's a programming error, or an external service problem.

Unlike Java, PHPStan lets the user decide which exceptions are going to be checked and unchecked. These criteria will be different for a database abstraction library, for a web application, and for the static analyser itself.

All exceptions are checked by default. PHPStan's [configuration](/config-reference) allows marking exception classes as unchecked in two different ways:

* Mark a class and its subclasses as unchecked with `exceptions.uncheckedExceptionClasses`
* Mark a specific class name (without the subclasses) using regular exceptions with `exceptions.uncheckedExceptionRegexes`

The configuration in practice can look like this:

```neon
parameters:
	exceptions:
		uncheckedExceptionRegexes:
			- '#^Exception$#' # Mark general Exception as unchecked, subclasses are still checked
			- '#^Foo\\Bar\\#' # Mark exceptions from Foo\Bar namespace as unchecked
		uncheckedExceptionClasses:
			- 'LogicException' # Mark LogicException and child classes as unchecked
```

Since version 0.12.88 PHPStan also supports marking all exceptions as unchecked by default, and list the criteria for the only checked exception classes:

```neon
parameters:
	exceptions:
		checkedExceptionRegexes:
			- '#^Foo\\Bar\\#' # Mark exceptions from Foo\Bar namespace as checked
		checkedExceptionClasses:
			- 'RuntimeException' # Mark RuntimeException and child classes as checked
```

Enforce declaring thrown checked exceptions in `@throws`
------------------------

The couple of new rules related to checked exceptions are opt-in because every developer might have different needs.

To have a missing `@throws` with a checked exception above a function reported, turn it on in your configuration:

```neon
parameters:
	exceptions:
		check:
			missingCheckedExceptionInThrows: true
```

Report extra exceptions in `@throws` that aren't actually thrown
------------------------

When `@throws` contains an exception that isn't thrown in the function body, PHPStan can report it with the following setting:

```neon
parameters:
	exceptions:
		check:
			tooWideThrowType: true
```

Dead catch reporting
------------------------

Error will be reported when there's a `catch` block for an exception that isn't thrown in the `try` block. It works even for multi-catch statements if you enable [bleedingEdge](/blog/what-is-bleeding-edge) (since [1.10.16](https://github.com/phpstan/phpstan/releases/tag/1.10.16)). This will be enabled by default in next major version as I believe it's useful regardless of anyone's view of how exceptions should be categorized and handled.

```neon
includes:
	- vendor/phpstan/phpstan/conf/bleedingEdge.neon
```

Since version 1.10.36 PHPStan allows to disable the dead catch reporting for unchecked exception with the following setting:

```neon
parameters:
	exceptions:
		reportUncheckedExceptionDeadCatch: false
```

This option will be useful if you don't plan to annotate `@throws` tag for unchecked exceptions but still occasionally catch them.

What does absent `@throws` above a function mean?
------------------------

PHPStan needs to be careful with interpreting of the existing code. No tool until now has forced developers to dutifully document their exceptions so absent `@throws` tag can mean two different things:

* Function throws some undocumented exceptions
* Function doesn't throw any exception

The first option is safer so that's what PHPStan does by default. In this mode you can write `@throws void` to mark a function that definitely doesn't throw an exception.

However, if another function call in the same scope declares `@throws` with an exception class, PHPStan will instead interpret the absent `@throws` as no exception being thrown. To flip the switch and always enable this mode, turn `exceptions.implicitThrows` off in your configuration:

```neon
parameters:
	exceptions:
		implicitThrows: false
```

Inline `@throws`
------------------------

Similar to [an inline `@var` tag](/writing-php-code/phpdocs-basics#inline-%40var) to override what PHPStan thinks about the type of the assigned variable, you can use an inline `@throws` tag to persuade PHPStan about what's being thrown and not thrown in a function body:

```php
/** @throws FooException */
$a = $this->doSomething();
```

[Stub files](/user-guide/stub-files) can also be used to fix `@throws` PHPDocs in 3rd party code.

Future scope
------------------------

There might be more opportunities for more rules to explore in the future. Some ideas that come to mind are:

* Disallow unchecked exceptions in `catch` blocks. This might sound useful, but sometimes we want to check an unchecked exception and convert it to a checked exception before re-throwing.
* Disallow unchecked exceptions in `@throws` - this is a hard sell, we might want to write them there so that we can get more [precise try-catch-finally analysis](/blog/precise-try-catch-finally-analysis).
* Disallow empty catch-all like `catch (\Throwable $e) { }` - this construct can silence all errors which is rarely desired

-----------------

I can't wait for everyone to try out these features and send back some feedback to improve them!

---

Do you like PHPStan and use it every day? [**Consider supporting further development of PHPStan on GitHub Sponsors**](https://github.com/sponsors/ondrejmirtes/). I’d really appreciate it!
