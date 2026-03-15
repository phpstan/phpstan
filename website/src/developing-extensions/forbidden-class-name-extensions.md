---
title: Forbidden Class Name Extensions
---

If your project has classes that should never be referenced directly — for example, generated Doctrine proxy classes — you can write a forbidden class name extension to report their usage.

This is [the interface](https://apiref.phpstan.org/__BRANCH__/PHPStan.Classes.ForbiddenClassNameExtension.html) your extension needs to implement:

```php
namespace PHPStan\Classes;

interface ForbiddenClassNameExtension
{

	/** @return array<string, string> */
	public function getClassPrefixes(): array;

}
```

The `getClassPrefixes()` method returns a map where keys are project/library names (used in error messages) and values are class name prefixes to forbid. Any class whose fully qualified name starts with one of these prefixes will be reported.

Here's an example that forbids direct usage of Doctrine-generated proxy classes:

```php
namespace App\PHPStan;

use PHPStan\Classes\ForbiddenClassNameExtension;

class ForbidDoctrineProxiesExtension implements ForbiddenClassNameExtension
{

	public function getClassPrefixes(): array
	{
		return [
			'Doctrine' => 'App\\GeneratedProxy\\__CG__\\',
		];
	}

}
```

With this extension, PHPStan reports errors when forbidden classes are referenced:

```php
// Referencing prefixed Doctrine class: App\GeneratedProxy\__CG__\App\Entity\User.
//  💡 This is most likely unintentional. Did you mean to type \App\Entity\User?
function foo(App\GeneratedProxy\__CG__\App\Entity\User $user): void
{

}
```

The implementation needs to be registered in your [configuration file](/config-reference):

```yaml
services:
	-
		class: App\PHPStan\ForbidDoctrineProxiesExtension
		tags:
			- phpstan.forbiddenClassNamesExtension
```
