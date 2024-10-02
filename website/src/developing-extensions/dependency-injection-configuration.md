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

PHPStan itself is [configured and assembled the same way](https://github.com/phpstan/phpstan-src/tree/2.0.x/conf).

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

The schema is enforced using the [`nette/schema`](https://doc.nette.org/en/3.1/schema) library. See how [PHPStan's own schema](https://github.com/phpstan/phpstan-src/blob/0ebfea013b4d625bc0bc31642679e85f78b456ca/conf/config.neon#L181-L354) is defined to get an idea how to define yours.
