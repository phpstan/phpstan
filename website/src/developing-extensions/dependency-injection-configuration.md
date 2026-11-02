---
title: Dependency Injection & Configuration
---

Dependency injection controls the way how extension objects are constructed for usage by PHPStan.

PHPStan is like any other application that utilizes dependency injection. The objects that exist during PHPStan's run fall into two categories: services and value objects.

Services
------------

Objects that exist throughout the whole analysis. They're created at the beginning and destroyed at the end. There's usually only a single service object per class - there's very little reason for multiple objects of the same class to exist. Some examples of service objects are: [`ReflectionProvider`](/developing-extensions/reflection), and [`FileTypeMapper`](/developing-extensions/reflection#retrieving-custom-phpdocs).

All [extension types](/developing-extensions/extension-types) require us to register them as services.

If we want to obtain a different service in our service class, we ask for it through the constructor:

```php
private ReflectionProvider $reflectionProvider;

public function __construct(
	ReflectionProvider $reflectionProvider
)
{
	$this->reflectionProvider = $reflectionProvider;
}
```

Value objects
------------

Value objects carry values. Many value objects are created and destroyed throughout the analysis. It make sense for many objects of the same class to exist at the same time. Some examples of value objects are: instances of [Type implementations](/developing-extensions/type-system) like `ObjectType`, instances of [reflection objects](/developing-extensions/reflection) like `MethodReflection`.

In contrast with services, value objects are not obtained by asking for them through constructor. They're either created with the `new` operator, or obtained from places like [`Scope`](/developing-extensions/scope) or [`ReflectionProvider`](/developing-extensions/reflection).

Dependency injection container
------------

The dependency injection container is responsible for creating our service classes and supplying the requested services to the constructor. This is happening entirely in the background. Developers can configure what the container does through the [configuration file](/config-reference).

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">

PHPStan itself is [configured and assembled the same way](https://github.com/phpstan/phpstan-src/tree/__BRANCH__/conf).

The final configuration is the product of merging all the involved configuration files together.

</div>

Registering services
------------

PHPStan utilizes [`nette/di`](https://doc.nette.org/en/3.1/di-services) as its dependency injection container. All the `nette/di` documentation about `services` section applies to PHPStan's `.neon` files as well.

A new service object is registered by adding a new entry into `services` section:

```yaml
services:
	-
		class: App\MyExtension
```

All the extension types have associated tags needed to recognize the extension:

```yaml
services:
	-
		class: App\MyExtension
		tags:
			- phpstan.broker.dynamicMethodReturnTypeExtension
```

Autowiring
------------

Service objects can ask for other service objects through their constructor. No additional configuration is needed if there's a single registered service of that type. The dependency injection container will supply it automatically. That's called autowiring.

If there are multiple services of the requested type, it's not obvious which one should be injected into the constructor. The `services` entry can contain `arguments` section to specify which service should be injected:

```yaml
services:
	serviceOne:
		class: App\MyService

	serviceTwo:
		class: App\MyService

	-
		# App\MyExtension has "myService" constructor parameter
		class: App\MyExtension
		arguments:
			myService: @serviceTwo
```

Non-object constructor parameters have to always be specified in the `arguments` section. Let's say we're interested in a built-in PHPStan parameter [`checkUninitializedProperties`](/config-reference#checkuninitializedproperties):

```yaml
services:
	-
		# App\MyExtension has "checkUninitializedProperties" constructor parameter
		class: App\MyExtension
		arguments:
			checkUninitializedProperties: %checkUninitializedProperties%
```

Custom parameters
------------

[PHPStan extensions](/user-guide/extension-library) can introduce custom parameters so that users can influence the extension behaviour in their project's [configuration file](/config-reference):

```yaml
parameters:
	myExtension:
		myOwnParameter: true

services:
	-
		# App\MyExtension has "myOwnParameter" constructor parameter
		class: App\MyExtension
		arguments:
			myOwnParameter: %myExtension.myOwnParameter%
```

But that's not sufficient. In order to prevent typos, PHPStan requires custom parameters to be defined in `parametersSchema` section of the configuration file:

```yaml
parametersSchema:
	myExtension: structure([
		myOwnParameter: bool()
	])
```

The schema is enforced using the [`nette/schema`](https://doc.nette.org/en/3.1/schema) library. See how [PHPStan's own schema](https://github.com/phpstan/phpstan-src/blob/__BRANCH__/conf/parametersSchema.neon) is defined to get an idea how to define yours.

Attribute services
------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.3.5</div>

Instead of the `services`, `rules` and `conditionalTags` sections, services can be registered with PHP attributes on the classes themselves. List the directories with these classes in the top-level `attributeServicesDirectories` section of the extension's configuration file. Relative paths are resolved against the file:

```yaml
attributeServicesDirectories:
	- src
```

PHPStan reads the attributes with native reflection, so the package must require PHP 8.0 or later.

### `#[AutowiredService]`

Registers the class as a service. Replaces a `services` entry:

```yaml
services:
	-
		class: App\MyExtension
```

```php
use PHPStan\DependencyInjection\AutowiredService;

#[AutowiredService]
class MyExtension implements DynamicMethodReturnTypeExtension
```

Extension tags like `phpstan.broker.dynamicMethodReturnTypeExtension` are added automatically based on the implemented interfaces. This includes `phpstan.rules.rule` and `phpstan.collector`, so a rule or a collector registered with `#[AutowiredService]` runs on all rule levels. Use `#[RegisteredRule]` or `#[RegisteredCollector]` to register it only from a given level up.

Attribute arguments:

* `name`: service name, replaces the key of a named `services` entry. Default: none.
* `factory`: a method of another service like `'@App\MyFactory::create'`, or a static method like `'App\MyFactory::create'`. Replaces `factory`. Default: none.
* `as`: the type or list of types the service is autowired as. A single type also becomes the type of the service, so `as: App\MyInterface::class` on the implementation class replaces a `services` entry with `class: App\MyInterface` and `factory: App\MyImplementation`: the service is autowired only as `App\MyInterface`, not as the implementation class, and gets the tags of the extension interfaces `App\MyInterface` extends or implements. A list of types replaces `autowired: [App\FirstInterface, App\SecondInterface]`. Default: all types of the class.
* `autoTag`: set to `false` to not add the extension tags. Default: `true`.

For example this `services` entry:

```yaml
services:
	myExtension:
		class: App\MyExtension
		factory: @App\MyExtensionFactory::create
		autowired: [App\MyInterface]
```

is replaced by:

```php
#[AutowiredService(
	name: 'myExtension',
	factory: '@App\MyExtensionFactory::create',
	as: [MyInterface::class],
)]
class MyExtension implements MyInterface
```

### `#[NonAutowiredService]`

Registers the class as a service that is not autowired. Replaces a named `services` entry with `autowired: false`:

```yaml
services:
	myService:
		class: App\MyService
		autowired: false
```

```php
use PHPStan\DependencyInjection\NonAutowiredService;

#[NonAutowiredService(name: 'myService')]
class MyService
```

Other services ask for it with `#[AutowiredParameter(ref: '@myService')]`.

Attribute arguments:

* `name`: service name. Required.
* `factory`: a method of another service like `'@App\MyFactory::create'`, or a static method like `'App\MyFactory::create'`. Replaces `factory`. Default: none.

### `#[RegisteredRule]` and `#[RegisteredCollector]`

Registers a [custom rule](/developing-extensions/rules) or a [collector](/developing-extensions/collectors). Replaces the `rules` section and services tagged with `phpstan.rules.rule` or `phpstan.collector`:

```php
use PHPStan\DependencyInjection\RegisteredRule;

#[RegisteredRule(level: 0)]
class MyRule implements Rule
```

Attribute arguments:

* `level`: the lowest [rule level](/user-guide/rule-levels) the rule or collector is registered on. Use `0` for rules that run on every level, like the ones in the `rules` section.

### `#[GenerateFactory]`

Generates an implementation of the factory interface that creates the class. Replaces the `implement` key of a `services` entry:

```yaml
services:
	-
		implement: App\MyValueFactory
```

```php
use PHPStan\DependencyInjection\GenerateFactory;

#[GenerateFactory(interface: MyValueFactory::class)]
class MyValue
```

Attribute arguments:

* `interface`: the factory interface with the `create()` method.
* `resultType`: the type the factory creates, when it differs from the return type of `create()`. Default: none.

### `#[AutowiredParameter]`

On a constructor parameter. Supplies a parameter or a service that can't be autowired. Replaces the `arguments` key of a `services` entry:

```php
use PHPStan\DependencyInjection\AutowiredParameter;

public function __construct(
	#[AutowiredParameter]
	private bool $checkUninitializedProperties,
	#[AutowiredParameter(ref: '%myExtension.myOwnParameter%')]
	private bool $myOwnParameter,
	#[AutowiredParameter(ref: '@serviceTwo')]
	private MyService $myService,
)
```

Attribute arguments:

* `ref`: the configuration parameter like `'%myExtension.myOwnParameter%'`, the service like `'@serviceTwo'`, or a value like `'App\MyClass::MY_CONSTANT'`. As in the `arguments` of a `services` entry, `Class::CONSTANT` and `@service` strings become constants and services, also in the value of the parameter. Default: the parameter with the same name as the constructor parameter.

### `#[Conditional]`

Adds the tags of the wrapped `#[AutowiredService]`, `#[RegisteredRule]` or `#[RegisteredCollector]` only when the condition is met. Replaces the `conditionalTags` section:

```yaml
conditionalTags:
	App\MyRule:
		phpstan.rules.rule: %myExtension.myRule%
```

```php
use PHPStan\DependencyInjection\Conditional;
use PHPStan\DependencyInjection\RegisteredRule;

#[Conditional(
	attribute: new RegisteredRule(level: 0),
	if: '%myExtension.myRule%',
)]
class MyRule implements Rule
```

The service is always registered so that other services can still ask for it.

Attribute arguments:

* `attribute`: `new AutowiredService(...)`, `new RegisteredRule(...)` or `new RegisteredCollector(...)`.
* `if`: the same values as in `conditionalTags`: a parameter like `'%myExtension.myRule%'`, its negation `'not(%myExtension.myRule%)'`, or a list of them that must all be met, like `['%myExtension.myRule%', '%featureToggles.bleedingEdge%']`.

`new` in attribute arguments requires PHP 8.1. On PHP 8.0, write the wrapped attribute as an array:

```php
#[Conditional(
	attribute: ['olvlvl\ComposerAttributeCollector\NewExpression' => [RegisteredRule::class, ['level' => 0]]],
	if: '%myExtension.myRule%',
)]
```

### Custom extension interfaces

An extension can define its own extension interface with its own tag, and collect the implementations with `tagged()`. Map the interface to the tag with `#[ExtensionInterface]` on the interface in one of the `attributeServicesDirectories`:

```php
use PHPStan\DependencyInjection\ExtensionInterface;

#[ExtensionInterface(tag: 'myExtension.myOwnExtension')]
interface MyOwnExtension
```

Or in the top-level `extensionInterfaces` section, which does not need PHP 8.0:

```yaml
extensionInterfaces:
	App\MyOwnExtension: myExtension.myOwnExtension
```

Then the tag works like the tags of PHPStan's own extension interfaces:

* `#[AutowiredService]` adds it to the services implementing the interface.
* It can be used in `conditionalTags` and `#[Conditional]`.
* A service with the tag must implement the interface.

Each interface has one tag and each tag one interface, so a custom interface cannot reuse PHPStan's own tags.

### Migrating from NEON

The `migrate:neon2attributes` command converts the `services`, `rules`, `conditionalTags` and `extensionInterfaces` entries in the project's configuration files to attributes. It finds the configuration file the same way as `analyse`, or reads it from `--configuration`, and adds the files from `extra.phpstan.includes` in `composer.json`. It rewrites the included `.neon` files outside `vendor/`, and adds the attributes to the classes in `attributeServicesDirectories`, which must be set first. The package must require PHP 8.0 or later:

```bash
vendor/bin/phpstan migrate:neon2attributes
```

Entries the attributes cannot express stay in the configuration files, and the command lists them with the reason.

The command migrates the configuration files of one entry point. A project that runs PHPStan with several configuration files registering different services, like separate analysis phases, has to be migrated by hand: the attribute services are registered in every run that uses `attributeServicesDirectories`, so services needed only in some runs need `#[Conditional]` on a parameter set by those runs.

Services registered with attributes are ordered by their class names, so a list collected with `tagged()` can come in a different order than the entries in the configuration file.
