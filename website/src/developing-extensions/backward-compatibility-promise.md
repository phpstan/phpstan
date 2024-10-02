---
title: Backward Compatibility Promise
---

There are multiple aspects to backward compatibility in case of PHPStan. This article talks about classes, interfaces, and methods that can be safely used by extension developers without the risk of breaking in minor versions.

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">

To read about backward compatibility from the point of view of end users, <a href="/user-guide/backward-compatibility-promise">head here</a>.

</div>

PHPStan makes use of a lot of internal code with implementation details that are not meant to be used by third parties writing PHPStan extensions. But the internal code is available to extensions anyway because of how PHP works. To be able to develop PHPStan further, we need some wiggle room to be able to make changes to the internals.

That's why not all the PHPStan's internal code is supposed to be a surface area for extension developers. When the extensions are analysed by PHPStan itself, it checks whether only the intended code is used by the extension. Reported risky usages point out that such API can change in the next minor version and that it's not covered by the backward compatibility promise.

Here's the human-readable summary of these rules. If you want more code to be covered by this backward compatibility promise, please [open a discussion](https://github.com/phpstan/phpstan/discussions).

You can browse all the backward compatibility-covered code in [**API Reference**](https://apiref.phpstan.org/2.0.x/namespace-PHPStan.html).

Classes
---------

* Non-final classes with `@api` in their PHPDoc can be extended.
* Objects of classes with `@api` in their PHPDoc cannot be created via `new` unless there's also `@api` above the public constructor. This encourages using [dependency injection](/developing-extensions/dependency-injection-configuration) in case the class is registered as a service in the dependency injection container.
* All public methods from classes with `@api` in their PHPDoc can be called.

Interfaces
---------

* Interfaces with `@api` in their PHPDoc can be implemented and extended, except for `PHPStan\Type\Type`. Instead of implementing this interface, extend an existing implementation like `PHPStan\Type\ObjectType` and similar.
* All methods from interfaces with `@api` in their PHPDoc can be called.

Methods
---------

* Public methods with `@api` in their PHPDoc (or in the PHPDoc of the declaring class) can be called.

Traits
---------

* No internal PHPStan traits can be used.
