# Placetopay multitenancy package

This package is based on the first version of package `spatie/laravel-multitenancy`.

Because it is a customization, it requires override steps mentioned below for proper installation.

[More information about the package](https://github.com/spatie/laravel-multitenancy/tree/v1).

This package aims to standardize the configuration of the ``tenants`` table of the landlord database, in addition to reducing the number of queries made to the same database by using cache.

## Prerequsites
- `php7.4+`
- `Laravel 7.0+`

## Installation

This package can be installed via composer:

``` bash
composer require "placetopay/cerberus:^1.0"
```

### Publishing the config file

You must publish the config file:

``` bash
php artisan vendor:publish --provider="Placetopay\Cerberus\TenancyServiceProvider" --tag="config"
```

### Publishing the migrate file

``` bash
php artisan vendor:publish --provider="Placetopay\Cerberus\TenancyServiceProvider" --tag="migrations"
```

### Create storage folder by tenancy
This is allowed to run only if the application has the configuration variable **multitenancy.suffix_storage_path** set to true.

``` bash
php artisan tenants:skeleton-storage --tenant=*
```

## How to use
After publish the config and migrations files, you need to create a new connection in ``config/database.php``,
This connection will allow the management of the landlord database, in which the tenants of the application will be stored.

```
'connections' => [
    ...
    'landlord' => [
            'driver' => env('DB_LANDLORD_DRIVER', 'mysql'),
            'url' => env('DB_LANDLORD_URL'),
            'host' => env('DB_LANDLORD_HOST', '127.0.0.1'),
            'port' => env('DB_LANDLORD_PORT', '3306'),
            'database' => env('DB_LANDLORD_DATABASE', 'forge'),
            'username' => env('DB_LANDLORD_USERNAME', 'forge'),
            'password' => env('DB_LANDLORD_PASSWORD', ''),
            'unix_socket' => env('DB_LANDLORD_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
  ...
  ]
```

The migration of the landlord table in relation to the spatie package was modified, adding a `config` field of json type, 
with which it's intended to centralize the configuration that is carried out in front of each tenant, 
in this field you can define the connection to the database using the following structure.
```JSON
{
  "app": {
    "url": "...", 
    "name": "..."
  }, 
  "database": {
    "connections": {
      "mysql": {
        "host": "...", 
        "port": "...", 
        "database": "...", 
        "username": "..."
      }
    }
  }
}
```
You can add all configurations that you needed, this json will be convert in array dot structure
and then will be set in the laravel config.

Additionally, the variable ``APP_IDENTIFIER`` is provided in the file ``config/multitenancy.php`` which will be the project identifier

### Execute migrations

To execute the migrations of the landlord database, it's necessary to specify the connection and the path 
to the folder where the migrations are located:
```` 
php artisan migrate --database=landlord --path=database/migrations/laandlord/ 
````

### Jobs
