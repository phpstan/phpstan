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

If you do not provide a config file explicitly, PHPStan will look for files named `phpstan.neon` or `phpstan.neon.dist` in current directory.

The resolution priority is as such:

1. If config file is provided as a command line option, it will be used.
2. Otherwise, if `phpstan.neon` exists in the current working directory, it will be used.
3. Otherwise, if `phpstan.neon.dist` exists in the current working directory, it will be used.
4. If none of the above is true, no config will be used.

The usual practice is to have `phpstan.neon.dist` under version control, and allow the user to override certain settings in their environment (on their own computer or on a continuous integration server) by creating `phpstan.neon` that's present in `.gitignore` file. See [Multiple files](#multiple-files) for more details:

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

Autoloading
------------------

Autoloading has been deprecated in favor of [Discovering Symbols](/user-guide/discovering-symbols) in PHPStan 0.12.26.

Related config keys: [`autoload_directories`](/user-guide/autoloading),
[`autoload_files`](/user-guide/autoloading).

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

If your codebase contains some files that are broken on purpose (e. g. to test behaviour of your application on files with invalid PHP code), you can exclude them using the `excludes_analyse` key. Each entry is used as a pattern for the [`fnmatch()`](https://www.php.net/manual/en/function.fnmatch.php) function.

```yaml
parameters:
	excludes_analyse:
		- tests/*/data/*
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

You can then choose your own ruleset by copying parts of the [level configuration files](https://github.com/phpstan/phpstan-src/tree/master/conf) from PHPStan sources, or include your own custom rules.

Solving undefined variables
-------------------

Learn more about solving undefined variables in [Writing PHP Code](/writing-php-code/solving-undefined-variables).

Related config keys: [`earlyTerminatingMethodCalls`](/writing-php-code/solving-undefined-variables),
[`earlyTerminatingFunctionCalls`](/writing-php-code/solving-undefined-variables).

Universal object crates
----------------

<!-- TODO move to Solving undefined properties -->

Classes without predefined structure are common in PHP applications. They are used as universal holders of data - any property can be set and read on them. Notable examples include `stdClass`, `SimpleXMLElement` (these are enabled by default), objects with results of database queries etc. Use `universalObjectCratesClasses` key to let PHPStan know which classes with these characteristics are used in your codebase:


```yaml
parameters:
	universalObjectCratesClasses:
		- Dibi\Row
		- Ratchet\ConnectionInterface
```

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

### `polluteCatchScopeWithTryAssignments`

**default**: `false`

If you use some variables from a try block in your catch blocks, set this parameter to `true`.

```php
try {
	$author = $this->getLoggedInUser();
	$post = $this->postRepository->getById($id);
} catch (PostNotFoundException $e) {
	// $author is probably defined here
	throw new ArticleByAuthorCannotBePublished($author);
}
```

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

### `checkMissingClosureNativeReturnTypehintRule`

**default**: `false` ([strict-rules](https://github.com/phpstan/phpstan-strict-rules) sets it to `true`)

**example**: [with `false`](https://phpstan.org/r/c3373d13-4ab1-443b-8e97-55c5b795b2a2), [with `true`](https://phpstan.org/r/7066aec7-ae60-45c5-ad96-08b1c3f2e5c0)

When set to `true`, it reports closures that could have a native return typehint.

### `reportMaybesInMethodSignatures`

**default**: `false` ([strict-rules](https://github.com/phpstan/phpstan-strict-rules) sets it to `true`)

**example**: [with `false`](https://phpstan.org/r/34e1217e-8ac4-46b9-b753-49916ca74963), [with `true`](https://phpstan.org/r/772bbb00-21f7-4286-8856-f52f45a25c1a)

When set to `true`, it reports violations of parameter type contravariance and return type covariance. By default, PHPStan only reports completely incompatible types in signatures, [see this example](https://phpstan.org/r/65d77b12-3421-48b4-b27c-b76a01a22f98).

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
// "Class has an uninitialized property $bar. Give it default value or assign it in the constructor."
private int $foo;

public function setFoo(int $foo): void
{
	$this->foo = $foo;
}
```

Vague typehints
------------

[Level 6](#rule-level) checks for missing typehints. Not only it reports when there's no typehing at all, but also when it's not specific enough. PHPStan needs to know about array item types. So `array` isn't specific enough but `int[]` is.

If you want to use level 6 to report missing typehints, but are fine with `array` instead of `int[]`, you can disable this behaviour by setting `checkMissingIterableValueType` key to false:

```yaml
parameters:
	checkMissingIterableValueType: false
```

If you're using [generics](/blog/generics-in-php-using-phpdocs), another thing that level 6 does is that it requires type variables always be specified in typehints. So `ReflectionClass` isn't sufficient but `ReflectionClass<Foo>` is.

You can disable this strict approach to generics by setting `checkGenericClassInNonGenericObjectType` key to false:

```yaml
parameters:
	checkGenericClassInNonGenericObjectType: false
```

Type aliases
-------------

Learn more about type aliases in [Writing PHP Code](/writing-php-code/phpdoc-types#type-aliases).

Related config key: [`typeAliases`](/writing-php-code/phpdoc-types#type-aliases).

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

Miscellaneous parameters
-----------------

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
