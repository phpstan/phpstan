---
title: Config Reference
---

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">

**Getting started?**

This page is about all the configuration options PHPStan has. If you want to learn how to start using PHPStan, head to [Getting Started](/user-guide/getting-started) instead.

</div>

NEON format
----------------------

PHPStan uses configuration format called [NEON](https://ne-on.org/). It's very similar to YAML so if you're familiar with it, you can also write NEON.

This is how a possible example of a config file can look like:

```yaml
parameters:
	level: 6
	paths:
		- src
		- tests
```

Config file
---------------------


A config file can be passed to the `phpstan` executable using the `-c|--configuration` option:

```bash
vendor/bin/phpstan analyse -c phpstan.neon
```

When using a config file, you have to pass the `--level|-l` option to `analyse` command (default value `0` does not apply here), or provide it as a `level` parameter in the config file itself. [Learn more about other command line options »](/user-guide/command-line-usage)

If you do not provide a config file explicitly, PHPStan will look for files named `phpstan.neon`, `phpstan.neon.dist`, or `phpstan.dist.neon` in the current directory.

The resolution priority is as such:

1. If config file is provided as a command line option, it will be used.
2. Otherwise, if `phpstan.neon` exists in the current working directory, it will be used.
3. Otherwise, if `phpstan.neon.dist` exists in the current working directory, it will be used.
4. Otherwise, if `phpstan.dist.neon` exists in the current working directory, it will be used.
5. If none of the above is true, no config will be used.

The usual practice is to have `phpstan.neon.dist` or `phpstan.dist.neon` under version control, and allow the user to override certain settings in their environment (on their own computer or on a continuous integration server) by creating `phpstan.neon` that's present in `.gitignore` file. See [Multiple files](#multiple-files) for more details:

Multiple files
---------------------

The NEON format supports an `includes` section to compose the final configuration from multiple files. Let's say the `phpstan.neon.dist` project config file looks like this:

```yaml
parameters:
	level: 6
	paths:
		- src
		- tests
```

If the user doesn't have certain PHP extensions, wants to ignore some reported errors from the analysis, and to disable parallel CPU processing, they can create `phpstan.neon` file with this content:

```yaml
includes:
	- phpstan.neon.dist

parameters:
	ignoreErrors:
		- '#Function pcntl_open not found\.#'
	parallel:
		maximumNumberOfProcesses: 1
```

Relative paths in the `includes` section are resolved based on the directory of the config file is in. So in this example, `phpstan.neon.dist` and `phpstan.neon` are next to each other in the same directory.

`includes` can also reference PHP files that can be used to define dynamic configurations, see [ignore-by-php-version.neon.php](https://github.com/phpstan/phpstan-src/blob/1.8.5/build/ignore-by-php-version.neon.php) and [phpstan.neon](https://github.com/phpstan/phpstan-src/blob/1.8.5/build/phpstan.neon#L10) as examples.

Ignoring errors
-------------------

Learn more about ignoring errors in the [user guide](/user-guide/ignoring-errors).

Related config keys: [`ignoreErrors`](/user-guide/ignoring-errors#ignoring-in-configuration-file),
[`reportUnmatchedIgnoredErrors`](/user-guide/ignoring-errors#reporting-unused-ignores).

Discovering symbols
------------------

Learn more about discovering symbols in the [user guide](/user-guide/discovering-symbols).

Related config keys: [`scanFiles`](/user-guide/discovering-symbols#third-party-code-outside-of-composer-dependencies),
[`scanDirectories`](/user-guide/discovering-symbols#third-party-code-outside-of-composer-dependencies).

Bootstrap
------------------

If you need to initialize something in PHP runtime before PHPStan runs (like your own autoloader), you can provide your own bootstrap files:

```yaml
parameters:
	bootstrapFiles:
		- phpstan-bootstrap.php
```

Relative paths in the `bootstrapFiles` key are resolved based on the directory of the config file is in.

Caching
------------------

By default, PHPStan stores its cache files in `sys_get_temp_dir() . '/phpstan'` (usually `/tmp/phpstan`). You can override this by setting the `tmpDir` parameter:

```yaml
parameters:
	tmpDir: tmp
```

Relative path in the `tmpDir` key is resolved based on the directory of the config file is in. In this example PHPStan cache will be stored in `tmp` directory that's next to the configuration file.

Analysed files
-----------------------

PHPStan accepts a list of files and directories on the command line:

```bash
vendor/bin/phpstan analyse -c src tests
```


You can save some keystrokes each time you run PHPStan by moving the analysed paths to the config file:

```yaml
parameters:
	paths:
		- src
		- tests
```

Relative paths in the `paths` key are resolved based on the directory of the config file is in.

If you provide analysed paths to PHPStan on the command line and in the config file at the same time, they are not merged. Only the paths on the command line will be used.

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">

You should only analyse files with the code you've written yourself. There's no need to analyse the `vendor` directory with 3rd party dependencies because it's not in your power to fix all the mistakes made by the developers you don't work with directly.

Yes, PHPStan needs to know about all the classes, interfaces, traits, and functions your code uses, but that's achieved through [discovering symbols](/user-guide/discovering-symbols), not by including the files in the analysis.

</div>

If your codebase contains some files that are broken on purpose (e. g. to test behaviour of your application on files with invalid PHP code), you can exclude them using the `excludePaths` key. Each entry is used as a pattern for the [`fnmatch()`](https://www.php.net/manual/en/function.fnmatch.php) function.

```yaml
parameters:
	excludePaths:
		- tests/*/data/*
```

This is a shortcut for:

```yaml
parameters:
	excludePaths:
	    analyseAndScan:
		    - tests/*/data/*
```

If your project's directory structure mixes your own code (the one you want to analyse and fix bugs in) and third party code (which you're using for [discovering symbols](https://phpstan.org/user-guide/discovering-symbols), but don't want to analyse), the file structure might look like this:

```
├── phpstan.neon
└── src
    ├── foo.php
    ├── ...
    └── thirdparty
        └── bar.php
```

In this case, you want to analyse the whole `src` directory, but want to exclude `src/thirdparty` from analysing. This is how to configure PHPStan:

```yaml
parameters:
    excludePaths:
        analyse:
            - src/thirdparty
```

Additionally, there might be a `src/broken` directory which contains files that you don't want to analyse nor use for discovering symbols. You can modify the configuration to achieve that effect:

```yaml
parameters:
    excludePaths:
        analyse:
            - src/thirdparty
        analyseAndScan:
            - src/broken
```

By default, PHPStan analyses only files with the `.php` extension. To include other extensions, use the `fileExtensions` key:

```yaml
parameters:
	fileExtensions:
		- php
		- module
		- inc
```

Rule level
-------------

Learn more about rule levels in the [user guide](/user-guide/rule-levels).

Instead of using the CLI option `--level`, you can save some keystrokes each time you run PHPStan by moving the desired level to the config file:


```yaml
parameters:
	level: 6
```

Custom ruleset
---------

PHPStan requires you to specify a level to run which means it will choose the enforced rules for you. If you don't want to follow the predefined rule levels and want to create your own ruleset, tell PHPStan to not require a level by setting `customRulesetUsed` to true:


```yaml
parameters:
	customRulesetUsed: true
```

You can then choose your own ruleset by copying parts of the [level configuration files](https://github.com/phpstan/phpstan-src/tree/1.12.x/conf) from PHPStan sources, or include your own custom rules.


Bleeding edge
-------------------

Bleeding edge offers a preview of the next major version. When you enable Bleeding edge in your configuration file, you will get new rules, behaviour, and bug fixes that will be enabled for everyone later when the next PHPStan's major version is released.

```yaml
includes:
	- phar://phpstan.phar/conf/bleedingEdge.neon
```

[Learn more](/blog/what-is-bleeding-edge) about bleeding edge.

Solving undefined variables
-------------------

Learn more about solving undefined variables in [Writing PHP Code](/writing-php-code/solving-undefined-variables).

Related config keys: [`earlyTerminatingMethodCalls`](/writing-php-code/solving-undefined-variables),
[`earlyTerminatingFunctionCalls`](/writing-php-code/solving-undefined-variables).

Universal object crates
----------------

Classes without predefined structure are common in PHP applications. They are used as universal holders of data - any property can be set and read on them. Notable examples include `stdClass`, `SimpleXMLElement` (these are enabled by default), objects with results of database queries etc. Use `universalObjectCratesClasses` key to let PHPStan know which classes with these characteristics are used in your codebase:


```yaml
parameters:
	universalObjectCratesClasses:
		- Dibi\Row
		- Ratchet\ConnectionInterface
```

See also [object shape](/writing-php-code/phpdoc-types#object-shapes) PHPDoc type for a better alternative that lets you describe types of properties of such objects.

Constants
---------------

Learn more about letting PHPStan know about your global constants in [the user guide](/user-guide/discovering-symbols).

Sometimes your constants can have different values in different environments, like `DATABASE_ENGINE` that can have different values like `mysql` or `pgsql`. To let PHPStan know that the value of the constant can be different, and prevent errors like `Strict comparison using === between 'pgsql' and 'mysql' will always evaluate to false.`, add the constant name to `dynamicConstantNames` key:


```yaml
parameters:
	dynamicConstantNames:
		- DATABASE_ENGINE
		- Foo::BAR_CONSTANT # class constants are also supported
```

Stub files
------------------

Learn more about stub files in the [user guide](/user-guide/stub-files).

Related config key: [`stubFiles`](/user-guide/stub-files).

Stricter analysis
-----------


Existing outside of rule levels there are additional parameters affecting analysis result you can tune.

### `polluteScopeWithLoopInitialAssignments`

**default**: `true` ([strict-rules](https://github.com/phpstan/phpstan-strict-rules) sets it to `false`)

**example**: [with `true`](https://phpstan.org/r/23bb1a01-c802-4e60-a50a-73b9b8c3d7bf), [with `false`](https://phpstan.org/r/ad4a5198-099b-4f1a-99a5-1dd9eb9cc7c6)

When set to `false` it prevents reading variables set in `for` loop initial statement and `while` loop condition after the loop.

### `polluteScopeWithAlwaysIterableForeach`

**default**: `true` ([strict-rules](https://github.com/phpstan/phpstan-strict-rules) sets it to `false`)

**example**: [with `true`](https://phpstan.org/r/daf81a57-4f06-44d5-9d76-8fa2ce937db5), [with `false`](https://phpstan.org/r/df2ac8cf-761d-4606-bdd9-46468010863d)

When set to `false` it prevents reading key and value variables set in foreach when iterating over a non-empty array.

### `polluteScopeWithBlock`

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.0</div>

**default**: `true` ([strict-rules](https://github.com/phpstan/phpstan-strict-rules) sets it to `false`)

**example**: [with `true`](https://phpstan.org/r/cdee163b-325a-4189-8b85-1fc6f103dcb7), [with `false`](https://phpstan.org/r/2d6facb9-e4bc-45bf-a8b1-52c70851bac4)

When set to `false` it prevents reading key and value variables set in a separate code block in `{...}`.

### `checkAlwaysTrueCheckTypeFunctionCall`

**default**: `false` ([strict-rules](https://github.com/phpstan/phpstan-strict-rules) sets it to `true`)

**example**: [with `false`](https://phpstan.org/r/0630a07b-ae95-4142-8497-1e0d0e9b9a3b), [with `true`](https://phpstan.org/r/0374a4f8-25b6-47cb-af15-2c769038bf23)

When set to `true`, it reports occurrences of type-checking functions always evaluated to true.

### `checkAlwaysTrueInstanceof`

**default**: `false` ([strict-rules](https://github.com/phpstan/phpstan-strict-rules) sets it to `true`)

**example**: [with `false`](https://phpstan.org/r/2911e3c4-4738-414c-81ae-46f4b3db992f), [with `true`](https://phpstan.org/r/28e463d8-a6e3-421c-8388-9a71ea8060e7)

When set to `true`, it reports `instanceof` occurrences always evaluated to true.

### `checkAlwaysTrueStrictComparison`

**default**: `false` ([strict-rules](https://github.com/phpstan/phpstan-strict-rules) sets it to `true`)

**example**: [with `false`](https://phpstan.org/r/cf5b3e50-9d59-4e91-9efc-3e86600a8ac0), [with `true`](https://phpstan.org/r/83723605-0cd3-4a4e-bfb2-7971d9eb554b)

When set to `true`, it reports `===` and `!==` occurrences always evaluated to true.

### `checkExplicitMixedMissingReturn`

**default**: `false` ([strict-rules](https://github.com/phpstan/phpstan-strict-rules) sets it to `true`)

**example**: [with `false`](https://phpstan.org/r/cc69160f-4c08-4e28-a210-119088381ae2), [with `true`](https://phpstan.org/r/d80c3739-bf4c-431f-abe9-3a0a37a6a13a)

When set to `true`, it reports code paths with missing `return` statement in functions and methods with `@return mixed` PHPDoc.

### `checkFunctionNameCase`

**default**: `false` ([strict-rules](https://github.com/phpstan/phpstan-strict-rules) sets it to `true`)

**example**: [with `false`](https://phpstan.org/r/1d6b7920-71e5-4f69-a1fd-8e0e7ed48e28), [with `true`](https://phpstan.org/r/3b876a1a-743e-42c7-8a5e-0c143372f74b)

When set to `true`, it reports function and method calls with incorrect name case.

### `checkInternalClassCaseSensitivity`

**default**: `false` ([strict-rules](https://github.com/phpstan/phpstan-strict-rules) sets it to `true`)

**example**: [with `false`](https://phpstan.org/r/0c0a8ef1-945c-4fc9-abe2-5e4e286186aa), [with `true`](https://phpstan.org/r/a2c4e602-b7ec-4f5e-9c72-bdd67800f8f5)

When set to `true`, it reports references to built-in classes with incorrect name case.

### `reportMaybesInMethodSignatures`

**default**: `false` ([strict-rules](https://github.com/phpstan/phpstan-strict-rules) sets it to `true`)

**example**: [with `false`](https://phpstan.org/r/34e1217e-8ac4-46b9-b753-49916ca74963), [with `true`](https://phpstan.org/r/772bbb00-21f7-4286-8856-f52f45a25c1a)

When set to `true`, it reports violations of parameter type contravariance and return type covariance. By default, PHPStan only reports completely incompatible types in signatures, [see this example](https://phpstan.org/r/65d77b12-3421-48b4-b27c-b76a01a22f98).

### `reportMaybesInPropertyPhpDocTypes`

**default**: `false` ([strict-rules](https://github.com/phpstan/phpstan-strict-rules) sets it to `true`)

**example**: [with `false`](https://phpstan.org/r/b9648bc4-619f-4fe5-8c75-d79b3cd2fc96), [with `true`](https://phpstan.org/r/4e2ad8f5-21b4-4382-a6d9-9a8a33e487a3)

When set to `true`, it reports violations of property type invariance. By default, PHPStan only reports completely incompatible PHPDoc typescheckInternalClassCaseSensitivity, [see this example](https://phpstan.org/r/64857536-abc5-49c6-b7f8-1600df1460cf).

### `reportStaticMethodSignatures`

**default**: `false` ([strict-rules](https://github.com/phpstan/phpstan-strict-rules) sets it to `true`)

**example**: [with `false`](https://phpstan.org/r/b132c693-006d-481d-a3b0-2535b3c33b5a), [with `true`](https://phpstan.org/r/8a2faef7-d64c-46f9-ada8-a68a4675c283)

When set to `true`, it reports violations of parameter type contravariance and return type covariance in static methods.

### `checkTooWideReturnTypesInProtectedAndPublicMethods`

**default**: `false`

When set to `true`, it reports return typehints that could be narrowed down because some of the listed types are never returned from a public or protected method. For [private methods](https://phpstan.org/r/45ebd3b5-3868-43b6-becc-2c31578b4b1c) PHPStan does this by default.

```php
public function doFoo(): ?string
{
	// Method Foo::doFoo() never returns null so it can be removed from the return typehint.
	return 'str';
}
```

### `checkUninitializedProperties`

**default**: `false`

When set to `true`, it reports properties with native types that weren't initialized in the class constructor.

```php
// "Class has an uninitialized property $foo. Give it default value or assign it in the constructor."
private int $foo;

public function setFoo(int $foo): void
{
	$this->foo = $foo;
}
```

### `checkDynamicProperties`

**default**: `false` ([strict-rules](https://github.com/phpstan/phpstan-strict-rules) sets it to `true`)

**example**: [with `false`](https://phpstan.org/r/3784cac0-9773-4975-8e10-2dc3412a059d), [with `true`](https://phpstan.org/r/e2c2a609-3181-44c5-ad04-e1fac42a40f8)

When set to `true`, it reports use of dynamic properties as undefined.

### `rememberPossiblyImpureFunctionValues`

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.8.0</div>

**default**: `true`

By default, PHPStan considers all functions that return a value to be pure. That means that second call to the same function in the same scope will return the same narrowed type:

```php
public function getName(): string
{
    return $this->name;
}

if ($this->getName() === 'Foo') {
    echo $this->getName(); // still 'Foo'
}
```

This is a sane default in case of getters but sometimes we have a function that can return different values based on a global state like a random number generator, database, or time.

```php
public function getRandomNumber(): int
{
    return rand();
}

if ($this->getRandomNumber() === 4) {
    echo $this->getRandomNumber(); // it's not going to be 4 but PHPStan will think it is
}
```

You can mark `getRandomNumber()` with `/** @phpstan-impure */` but if you have many functions like this in your codebase and don't want to assume functions return the same value when called multiple times, you can set `rememberPossiblyImpureFunctionValues` to `false`:

```yaml
parameters:
	rememberPossiblyImpureFunctionValues: false
```

### `checkImplicitMixed`

**default**: `false`

When set to `true`, PHPStan is strict about values with an unspecified (implicit `mixed`) type. It enables the same checks for values with no type specified that rule level 9 enables for explicitly specified `mixed` type values.

### `checkBenevolentUnionTypes`

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.9.0</div>

**default**: `false`

PHPStan defines benevolent union types, such as `array-key`. Benevolent unions aren't checked strictly even at the highest level:

```php
public function requireInt(int $value): void {}
public function requireString(string $value): void {}

/**
 * @param array-key  $value1 // array-key is a benevolent union (int|string)
 * @param int|string $value2
 */
public function test($value1, int|string $value2): int
{
    $this->requireInt($value1);    // No error
    $this->requireString($value1); // No error
    $this->requireInt($value2);    // Error
    $this->requireString($value2); // Error
}
```

Enable stricter analysis of benevolent union types with the `checkBenevolentUnionTypes` option (needs level 7 or higher):

```yaml
parameters:
    checkBenevolentUnionTypes: true
```

### `reportPossiblyNonexistentGeneralArrayOffset`

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.11.0</div>

By default PHPStan does not report possibly nonexistent offset on general arrays:

```php
/**
 * @param array<string, int> $array
 */
public function doFoo(array $array): int
{
    return $array['foo'];
}
```

By setting `reportPossiblyNonexistentGeneralArrayOffset` to `true` this will be reported as an error. This option has effect on [rule level](/user-guide/rule-levels) 7 and up.

### `reportPossiblyNonexistentConstantArrayOffset`

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.11.0</div>

By default PHPStan does not report possibly nonexistent offset on array shapes:

```php
public function doFoo(string $s): void
{
    $a = ['foo' => 1];
    echo $a[$s];
}
```

By setting `reportPossiblyNonexistentConstantArrayOffset` to `true` this will be reported as an error. This option has effect on [rule level](/user-guide/rule-levels) 7 and up.

### `reportAlwaysTrueInLastCondition`

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.10.0 + Bleeding Edge</div>

**default**: `false`

By default PHPStan does not report always true last condition in a series of `elseif` branches and match expression arms:

```php
// $o is A|B
if ($o instanceof A) {
    // ...
} elseif ($o instanceof B) { // "Instanceof between B and B will always evaluate to true." IS NOT reported
    // ...
}
```

By setting `reportAlwaysTrueInLastCondition` to `true` the error in `elseif` will be reported.

### `reportWrongPhpDocTypeInVarTag`

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.10.0 + Bleeding Edge</div>

**default**: `false` ([strict-rules](https://github.com/phpstan/phpstan-strict-rules) sets it to `true`)

**example**: [with `true`](https://phpstan.org/r/55f53970-7db4-41e9-8444-473fb1475690), [with `false`](https://phpstan.org/r/25b91a89-848a-4873-8c2f-5422b32ed217)

By default PHPStan reports wrong type in `@var` tag only for native types on the right side of `=`. With `reportWrongPhpDocTypeInVarTag` set to `true` it will consider PHPDoc types too.

This effectively means that inline `@var` cast can only be used to narrow down original types. PHPStan offers many utilities to use instead:

* [Conditional return types](/writing-php-code/phpdoc-types#conditional-return-types)
* [`@phpstan-assert`](/writing-php-code/narrowing-types#custom-type-checking-functions-and-methods)
* [Generics](/blog/generics-in-php-using-phpdocs)
* [Stub files](/user-guide/stub-files) for overriding 3rd party PHPDocs
* [Dynamic return type extensions](/developing-extensions/dynamic-return-type-extensions)

### `reportAnyTypeWideningInVarTag`

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.10.60 + Bleeding Edge</div>

**default**: `false`

This option strictens checks used in `reportWrongPhpDocTypeInVarTag`. It strictly disallows any type widening. Compared to default behaviour, it disallows array key widening in inline PHPDoc `@var` tags, so those would get reported:

* `list<int>` => `array<int>`
* `array<int, int>` => `array<int>`

### `checkMissingOverrideMethodAttribute`

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.10.42</div>

**default**: `false`

When set to `true`, PHPStan reports missing `#[\Override]` attribute above methods that override a method coming from their parent classes, implemented interfaces, or abstract method from a used trait.

```php
class Foo
{
	public function doFoo(): void
	{

	}
}

class Bar extends Foo
{
	public function doFoo(): void
	{
		// missing #[\Override] above this method
	}
}
```

Please note that aside from setting this config parameter to `true`, you also need to set [`phpVersion`](#phpversion) to `80300` or higher. If you're not changing `phpVersion` in your config, make sure you're running PHPStan with PHP 8.3 or newer.

Exceptions
------------

Advanced exceptions-related rules are available. [Read this article for more details »](/blog/bring-your-exceptions-under-control).

Related config keys: `exceptions.implicitThrows`, `exceptions.uncheckedExceptionRegexes`, `exceptions.uncheckedExceptionClasses`, `exceptions.checkedExceptionRegexes`, `exceptions.checkedExceptionClasses`, `exceptions.reportUncheckedExceptionDeadCatch`, `exceptions.check.missingCheckedExceptionInThrows`, `exceptions.check.tooWideThrowType`

Vague typehints
------------

If you use callables, you might find that `callable` or `\Closure` is too vague for your liking.
For example, PHPStan won't complain if you pass `callable(int) : void` to `callable`, but it will complain if you pass `callable(int) : void` to `callable(string) : void`. So, being specific with callable signatures helps PHPStan find more bugs.

However, by default PHPStan won't complain if you don't specify a signature for a callable or closure.

You can make PHPStan require that callable signatures are specified with PHPStan's [callable PHPDoc syntax](/writing-php-code/phpdoc-types#callables) by setting the `checkMissingCallableSignature` key to `true` (disabled by default):

```yaml
parameters:
	checkMissingCallableSignature: true
```

This also requires level 6 and up.

Type aliases
-------------

Learn more about type aliases in [Writing PHP Code](/writing-php-code/phpdoc-types#global-type-aliases).

Related config key: [`typeAliases`](/writing-php-code/phpdoc-types#global-type-aliases).


PHPStan Pro
-------------

You can override the DNS servers [PHPStan Pro](https://phpstan.org/blog/introducing-phpstan-pro){.phpstan-pro-label} uses to download the application:

```yaml
parameters:
	pro:
		dnsServers:
			- '8.8.8.8'
```

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.10.39</div>

By default, [PHPStan Pro](https://phpstan.org/blog/introducing-phpstan-pro){.phpstan-pro-label} stores its files in `sys_get_temp_dir() . '/phpstan-fixer'`. If that does not work for you for example because of multi-user environment, you can change this setting. You can also take advantage of [environment variables](#environment-variables):

```yaml
parameters:
	pro:
		tmpDir: tmp
```

Relative path in the `pro.tmpDir` key is resolved based on the directory of the config file is in. In this example PHPStan cache will be stored in `tmp` directory that's next to the configuration file.

Parallel processing
----------------


PHPStan runs in multiple threads by default, taking advantage of multiple cores in your CPU. It uses [a formula](https://github.com/phpstan/phpstan-src/blob/a97bdead71d24694dfe2bbe96de80486119b27a1/src/Parallel/Scheduler.php#L28-L45) to figure out how many child processes to spawn. The child processes are given "jobs" - each job represents a batch of a certain number of files to process. The inputs to the formula are:

* Auto-detected number of logical CPU cores on your system. If you have 8 physical cores and hyper-threading-enabled CPU, you have 16 logical CPU cores.
* Maximum number of desired spawned processes, to prevent CPU hogging of the whole system if it's resource-constrained. (`maximumNumberOfProcesses`)
* Minimum number of jobs per process. Process is spawned only if it will process at least 2 jobs by default.  (`minimumNumberOfJobsPerProcess`)
* Job size - how many files are analysed in a single batch (`jobSize`)
* Number of analysed files - it's different each time because of how your codebase changes, and also thanks to the [result cache](/blog/from-minutes-to-seconds-massive-performance-gains-in-phpstan).

These variables can be changed in the configuration. Here are the defaults:

```yaml
parameters:
	parallel:
		jobSize: 20
		maximumNumberOfProcesses: 32
		minimumNumberOfJobsPerProcess: 2
```

You might encounter this error message when running PHPStan:

> Child process timed out after 60 seconds. Try making it longer with `parallel.processTimeout` setting.

This can happen when a single job takes longer than the set timeout. It prevents PHPStan from running indefinitely when something unexpected occurs. In case of large files, it might be normal that the analysis of some of them takes longer. You can make the timeout longer in the configuration:

```yaml
parameters:
	parallel:
		processTimeout: 300.0
```

To disable parallel processing altogether, set `maximumNumberOfProcesses` to `1`:

```yaml
parameters:
	parallel:
		maximumNumberOfProcesses: 1
```

Parallel processing is also disabled when running with [`--debug`](/user-guide/command-line-usage#--debug). [^notWantToRunWithDebug]

[^notWantToRunWithDebug]: Although you don't want to always run PHPStan with this option.

Clickable editor URL
-----------------

You can configure PHPStan to show clickable editor URL leading to the file and line with error in the default `table` error formatter. [Learn more](/user-guide/output-format#opening-file-in-an-editor)

Related config key: `editorUrl`

Miscellaneous parameters
-----------------

### `phpVersion`

**default**: `null` (current PHP version is used)

If you want to analyse a codebase as if it was written for a different PHP version than you're currently running, change the `phpVersion` parameter:

```yaml
parameters:
    phpVersion: 70400 # PHP 7.4
```

PHPStan will automatically infer the `config.platform.php` version from the last `composer.json` file it can find, if not configured in the PHPStan configuration file.

### `inferPrivatePropertyTypeFromConstructor`

**default**: `false`

When set to `true`, it doesn't require typehints for properties if the types can be inferred from constructor injection:

```php
private $foo; // PHPStan infers $foo to be Foo

public function __construct(Foo $foo)
{
	$this->foo = $foo;
}
```

### `treatPhpDocTypesAsCertain`

**default**: `true`

**example**: [with `true`](https://phpstan.org/r/af59f2c2-9700-4c0a-9fe6-c51b70a08d43), [with `false`](https://phpstan.org/r/c7d4173c-d3ba-4857-a260-ce9cac36e399)

PHPStan by default doesn't differentiate between PHPDoc and native types. It considers them both as certain.

This might not be what you want in case you're writing a library whose users might pass a wrong argument type to a function. Setting `treatPhpDocTypesAsCertain` to `false` relaxes some of the rules around type-checking.

### `tipsOfTheDay`

**default**: `true`

From time to time, PHPStan shows "💡 Tips of the Day" at the end of a successful analysis. You can turn this off by setting `tipsOfTheDay` key to `false`.

### `errorFormat`

**default**: `null`

If you want to change the default [error formatter](/user-guide/output-format), you can specify that using:

```yaml
parameters:
    errorFormat: json
```

### `additionalConstructors`

**default**: `[]`

If you want to avoid errors caused by writing to readonly properties outside of constructors, configure `additionalConstructors` to specify methods to treat as constructors:

```yaml
parameters:
	additionalConstructors:
		- PHPUnit\Framework\TestCase::setUp
```

This is useful when injecting dependencies with setter methods, or when writing "wither" methods on immutable objects.

Environment variables
-------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.10.33</div>

Parameters can reference environment variables:

```yaml
parameters:
    tmpDir: %env.PHPSTAN_TMP_DIR%
```

This example assumes you've set an environment variable called `PHPSTAN_TMP_DIR` before running PHPStan:

```
export PHPSTAN_TMP_DIR=/home/ondrej/phpstan-temp
```

Expanding paths
-------------

There are two predefined parameters usable to expand in config parameters that represent paths:

* `%rootDir%` - root directory where PHPStan resides (i.e. `vendor/phpstan/phpstan` in Composer installation)
* `%currentWorkingDirectory%` - current working directory where PHPStan was executed

These are no longer very useful as all relative paths in config parameters are resolved based on the directory of the config file is in, resulting in a short and concise notation.

Consider this directory structure:

```
app/
  model/
  ui/
	Foo.php
build/
  phpstan.neon
src/
tests/
vendor/
```

To reference the `Foo.php` file from `phpstan.neon`, you can simply use `../app/ui/Foo.php`.

To reference the `Foo.php` involving `%rootDir%`, you'd have to use `%rootDir%/../../../app/ui/Foo.php` which isn't really nice.

Custom parameters
----------------------

When [developing custom PHPStan extensions](/developing-extensions/extension-types), you might need a custom config parameter to provide customization to the user.

PHPStan does not allow unknown parameters to be present in the config in order to prevent typos. Any new parameter also needs to be added to the top-level `parametersSchema` section of the config file.

The schema is defined using [nette/schema](https://github.com/nette/schema) library. Definition of some of the built-in PHPStan parameters looks like this:

```yaml
parametersSchema:
	earlyTerminatingMethodCalls: arrayOf(listOf(string()))
	earlyTerminatingFunctionCalls: listOf(string())
	memoryLimitFile: string()
	staticReflectionClassNamePatterns: listOf(string())
	dynamicConstantNames: listOf(string())
	customRulesetUsed: bool()
```

If your parameters are nested arrays, you need to use the `structure` keyword:

```yaml
parametersSchema:
	symfony: structure([
		container_xml_path: schema(string(), nullable())
		constant_hassers: bool()
		console_application_loader: schema(string(), nullable())
	])
```
