---
title: "A working PHPStan Configuration for CodeIgnitor 3"
date: 2021-11-24
tags: guides
---

Working with CodeIgnitor 3 can be tricky. However ondrejmirtes and webbgroup have been able to find a working configuration that inspects the PHP application, but not bring in all of the libraries that CodeIgnitor depends on.

The prerequsites will be you need to have phpstan already brought in through composer. Once that is completed, you'll need 2 additional things:

1. A bootstrap file, named phpstan-bootstrap.php
```
<?php

define('BASEPATH', __DIR__);
define('URI_PATH', 'URI_PATH_SECURE');
define('ENVIRONMENT', "development");

?>
```

2. A Neon Configuration, named phpstan.neon. 
Customize the configuration as you wish. Just use this as a starting point. 

```
# Run this in the base directory: ./vendor/bin/phpstan --debug
# https://phpstan.org/user-guide/getting-started
parameters:
  level: 0
  scanFiles:
    - index.php
  bootstrapFiles:
    - phpstan-bootstrap.php
  paths:
    - .
  excludePaths:
    analyse:
      - tests/*
      - application/third_party/*
      - assets/*
      - system/*
      - vendor/*
  fileExtensions:
    - php
  ignoreErrors:
```

3. Lastly run this your root Codeignitor directory :-)

Debug flag is optional, but helps you understand how it works.

```
./vendor/bin/phpstan --debug
```

Happy Code Wrangling!
