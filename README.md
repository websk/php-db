# WebSK php-db

PDO-based database service and migration commands for PHP 8.3, 8.4, and 8.5.

## Requirements

- PHP 8.3 or newer
- PDO
- `pdo_mysql` when using `DBConnectorMySQL`

## Installation

```bash
composer require websk/php-db
```

## Configuration

```php
<?php

$config = [
    'settings' => [
        'db' => [
            'db_skif' => [
                'host' => 'mysql',
                'db_name' => 'skif',
                'user' => 'root',
                'password' => 'root',
                'dump_file_path' => __DIR__ . '/migrations.sql',
            ],
        ],
    ],
];
```

## Creating a service

```php
<?php

use WebSK\DB\DBServiceFactory;

$db_service = DBServiceFactory::factoryMySQL(
    $config['settings']['db']['db_skif']
);
```

When using a PSR-11 container:

```php
<?php

use Psr\Container\ContainerInterface;
use WebSK\DB\DBServiceFactory;

$container->set('DB_SERVICE_CONTAINER_ID', function (ContainerInterface $container) {
    $db_config = $container->get('settings.db.db_skif');

    return DBServiceFactory::factoryMySQL($db_config);
});
```

## Using `DBWrapper`

```php
<?php

use WebSK\DB\DBWrapper;

DBWrapper::setDbService($container->get('DB_SERVICE_CONTAINER_ID'));

$rows = DBWrapper::readAssoc(
    'SELECT id, name FROM users WHERE active = ?',
    [true]
);
```

## Migrations

The migrations file contains one SQL statement per non-empty line. Multiline SQL statements are not supported.

```sql
CREATE TABLE example (id int NOT NULL AUTO_INCREMENT PRIMARY KEY)
ALTER TABLE example ADD COLUMN name varchar(255) NOT NULL
```

The CLI looks for the application configuration in `config/config.php` and provides two commands:

```bash
vendor/bin/websk_db_migration.php migrations:migration_auto
vendor/bin/websk_db_migration.php migrations:migration_handle
```

`migrations:migration_auto` executes every statement that has not been recorded in the `db_executed_queries` table. `migrations:migration_handle` asks before executing, skipping, or marking each statement as executed.

## Development

```bash
composer install
composer check
```

MySQL integration tests are enabled when all of these variables are set:

```bash
MYSQL_TEST_HOST=127.0.0.1 \
MYSQL_TEST_DATABASE=php_db_test \
MYSQL_TEST_USER=root \
MYSQL_TEST_PASSWORD=secret \
vendor/bin/phpunit --group mysql
```
