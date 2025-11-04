Minimal repo for https://github.com/phpstan/phpstan/discussions/13755

Steps:
1. Checkout repo
1. install composer packages `composer i`
1. run phpstan `./deploy/app/vendor/bin/phpstan analyze --memory-limit 2G -v ./deploy/app/services`

Note:

* The directory structure is like we use in our current project
* except for a single php file (containing just `<?php`) no additional php file is needed

Environment:

```
mark.roeling@laptop ~/p/phpstan (main) [1]> php -v
PHP 8.4.14 (cli) (built: Oct 27 2025 20:53:56) (NTS)
Copyright (c) The PHP Group
Built by Debian
Zend Engine v4.4.14, Copyright (c) Zend Technologies
    with Zend OPcache v8.4.14, Copyright (c), by Zend Technologies
    with Xdebug v3.4.5, Copyright (c) 2002-2025, by Derick Rethans

mark.roeling@laptop ~/p/phpstan (main) [1]> composer diagnose
Checking composer.json: WARNING
No license specified, it is recommended to do so. For closed-source software you may use "proprietary" as license.
Checking composer.lock: OK
Checking platform settings: OK
Checking git settings: OK git version 2.43.0
Checking http connectivity to packagist: OK
Checking https connectivity to packagist: OK
Checking github.com oauth access: OK does not expire
Checking disk free space: OK
Checking pubkeys: 
Tags Public Key Fingerprint: 57815BA2 7E54DC31 7ECC7CC5 573090D0  87719BA6 8F3BB723 4E5D42D0 84A14642
Dev Public Key Fingerprint: 4AC45767 E5EC2265 2F0C1167 CBBB8A2B  0C708369 153E328C AD90147D AFE50952
OK
Checking Composer version: OK
Checking Composer and its dependencies for vulnerabilities: OK
Composer version: 2.8.12
PHP version: 8.4.14
PHP binary path: /usr/bin/php8.4
OpenSSL version: OpenSSL 3.0.13 30 Jan 2024
curl version: 8.5.0 libz 1.3 brotli 1.1.0 zstd supported ssl OpenSSL/3.0.13
zip: extension present, unzip present, 7-Zip present (7z)
```

## result

```
mark.roeling@laptop ~/p/p/phpstan-discussion-13755 (main) [1]> ./deploy/app/vendor/bin/phpstan analyze --memory-limit 2G -v ./deploy/app/config ./deploy/app/services
 1/1 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100% < 1 sec

 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- 
  Line   deploy/app/vendor/phpstan/phpstan-doctrine/stubs/MongoClassMetadataInfo.stub                                                                                           
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- 
  40     Template type T is declared as covariant, but occurs in invariant position in return type of method Doctrine\ODM\MongoDB\Mapping\ClassMetadata::getReflectionClass().  
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- 

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------- 
  Line   deploy/app/vendor/phpstan/phpstan-doctrine/stubs/ORM/Mapping/ClassMetadataInfo.stub                                                                                
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------- 
  59     Template type T is declared as covariant, but occurs in invariant position in return type of method Doctrine\ORM\Mapping\ClassMetadataInfo::getReflectionClass().  
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- 
  Line   deploy/app/vendor/phpstan/phpstan-doctrine/stubs/Persistence/Mapping/ClassMetadata.stub                                                                                
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- 
  20     Template type T is declared as covariant, but occurs in invariant position in return type of method Doctrine\Persistence\Mapping\ClassMetadata::getReflectionClass().  
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- 

                                                                                                                        
 [ERROR] Found 3 errors                                                                                                 
                                                                                                                        

Elapsed time: 0.59 seconds
Used memory: 68.5 MB

```
