---
title: Stub Files Extensions
---

PHPStan uses [stub files](/user-guide/stub-files) to override PHPDocs for third-party code. Normally, stub files are listed statically in the [`stubFiles`](/config-reference) configuration parameter.

If you need to load stub files conditionally — for example, based on the PHP version or whether a certain extension is installed — you can write a stub files extension.

This is [the interface](https://apiref.phpstan.org/__BRANCH__/PHPStan.PhpDoc.StubFilesExtension.html) your extension needs to implement:

```php
namespace PHPStan\PhpDoc;

interface StubFilesExtension
{

	/** @return string[] */
	public function getFiles(): array;

}
```

The `getFiles()` method returns an array of absolute paths to stub files that should be loaded.

Here's an example that loads different stubs based on the PHP version:

```php
namespace App\PHPStan;

use PHPStan\PhpDoc\StubFilesExtension;
use PHPStan\Php\PhpVersion;

class MyStubFilesExtension implements StubFilesExtension
{

	public function __construct(
		private PhpVersion $phpVersion,
	)
	{
	}

	public function getFiles(): array
	{
		$files = [
			__DIR__ . '/../../stubs/common.stub',
		];

		if ($this->phpVersion->getVersionId() >= 80000) {
			$files[] = __DIR__ . '/../../stubs/php8.stub';
		}

		return $files;
	}

}
```

The loaded stub files override third-party PHPDocs, just like with the static `stubFiles` configuration. For example, a stub file `stubs/php8.stub` could narrow a return type:

```php
// stubs/php8.stub
namespace Vendor\Lib;

class Connection
{
	/** @return array<string, mixed> */
	public function fetchRow(): array {}
}
```

```php
// In your code, PHPStan now uses the narrower type from the stub
$row = $connection->fetchRow();
\PHPStan\dumpType($row); // array<string, mixed> instead of mixed
```

The implementation needs to be registered in your [configuration file](/config-reference):

```yaml
services:
	-
		class: App\PHPStan\MyStubFilesExtension
		tags:
			- phpstan.stubFilesExtension
```
