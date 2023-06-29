* Clone the repository
* `composer install`
* `php8.1 ./vendor/bin/phpstan analyse app --memory-limit=1G`

* Deleting methods `payloadRow`, `payloadRowIsSame` and `payloadRowOther` at `app/Domains/Log/Model/Traits/Payload.php` it works
* Deleting database migration table `log` at `database/migrations/2021_01_14_000000_base.php` it works
