---
title: Always-read and written properties
---

PHPStan is [able to detect unused, never-read and never-written private properties](/blog/detecting-unused-private-properties-methods-constants). There might be some cases where PHPStan thinks a property is unused, but the code might actually be correct. For example libraries like Doctrine ORM might take advantage of reflection to write and read private properties which static analysis cannot understand, but fortunately you can write a custom extension to make PHPStan understand what's going on and avoid false-positives.

The implementation is all about applying the [core concepts](/developing-extensions/core-concepts) like [reflection](/developing-extensions/reflection) so check out that guide first and then continue here.

This is [the interface](https://apiref.phpstan.org/2.0.x/PHPStan.Rules.Properties.ReadWritePropertiesExtension.html) your extension needs to implement:

```php
namespace PHPStan\Rules\Properties;

use PHPStan\Reflection\PropertyReflection;

interface ReadWritePropertiesExtension
{

	public function isAlwaysRead(PropertyReflection $property, string $propertyName): bool;

	public function isAlwaysWritten(PropertyReflection $property, string $propertyName): bool;

	public function isInitialized(PropertyReflection $property, string $propertyName): bool;

}
```

The implementation needs to be registered in your [configuration file](/config-reference):

```yaml
services:
	-
		class: MyApp\PHPStan\PropertiesExtension
		tags:
			- phpstan.properties.readWriteExtension
```

Properties for Doctrine ORM are already covered by the [official PHPStan extension](https://github.com/phpstan/phpstan-doctrine). The following logic is applied (see the [extension itself](https://github.com/phpstan/phpstan-doctrine/blob/ecc4aecaaf34871a2961c4c7a046bc2e092b0300/src/Rules/Doctrine/ORM/PropertiesExtension.php)):

* Property is always read (it doesn't need a getter) if it's a persisted field or association. This is because we might save some data into the database without reading them in the code, only referencing the fields in DQL queries, or maybe having them read directly from the database by another company department.
* Property is always written (it doesn't need assigning in code) if it's a primary key with a generated value, or if it's a [read-only entity](https://www.doctrine-project.org/projects/doctrine-orm/en/3.2/reference/attributes-reference.html#attrref_entity) without a constructor. Records for read-only entities are usually inserted into the database through some other means and are for tables where data doesn't change often.
