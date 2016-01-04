# PHPStan - PHP Static Analysis Tool

PHPStan is currently in development. First stable version together with documentation will be released in Q1 2016.

Once it's released, it will perform the following checks on your codebase:

* Existence of classes used in `instanceof`, `catch`, typehints and other language constructs. PHP does not do this and just stays silent instead.
* Existence and visibility of called methods and functions.
* Existence and visibility of accessed properties.
* Correct number and types of parameters passed to constructors, methods and functions.
* Correct number of parameters passed to `sprintf`/`printf` calls based on format strings.
* Existence of variables while respecting scopes of branches and loops.
* Lossless type casts

