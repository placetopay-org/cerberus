# Placetopay multitenancy package

This package is based on the third version of package `spatie/laravel-multitenancy`.

Because it is a customization, it requires override steps mentioned below for proper installation.

[More information about the package](https://github.com/spatie/laravel-multitenancy/tree/v1).

This package aims to standardize the configuration of the ``tenants`` table of the landlord database, in addition to reducing the number of queries made to the same database by using cache.

## Prerequsites
- `php8.0+`
- `Laravel 8.0+`

## Installation

This package can be installed via composer:

``` bash
composer require "placetopay/cerberus:^3.0"
```

### Publishing the config file

You must publish the config file:

``` bash
php artisan vendor:publish --tag="multitenancy-config"
```

### Publishing the migrate file

``` bash
php artisan vendor:publish --tag="multitenancy-migrations"
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
        //...
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
You need to update the connection and tables for jobs and failed_jobs, ``config/queue.php``:
```
[
//...
'connections' => [
    'database' => [
        'connection' => env('DB_LANDLORD_CONNECTION'),
        'driver' => 'database',
        'table' => '{project_identifier}_jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
    ],
    //...
]
//...
]

//...
'failed' => [
    'driver' => env('QUEUE_FAILED_DRIVER', 'database'),
    'database' => env('DB_LANDLORD_CONNECTION', 'landlord'),
    'table' => '{project_identifier}_failed_jobs',
],
```

### Storage
This package will overwrite the Storage Facade by default, setting a tenant's name as a prefix for folders that use 
with Storage Facade, if you need to suffix the ``storage_path()`` method too, you need to set to true the variable 
``suffix_storage_path`` in ``config/multitenant.php`` file.

### How change the commands
To execute any command for one tenant you need to execute the next command structure
```php artisan tenants:artisan "command:execute" --tenant={tenant_domain} ```

Addig the ``--tenant={tenant_domain}`` flag, will be executed the commando only for the specific tenant, without this it will execute by each tenant.

### translatable attributes

You can use the translate method in the tenant model to translate some keys from the config JSON. 
This method uses the app locale and fallback to search the correct values from the JSON, 
addionaly you should use a ``config/tenant.php`` file to set default values for translations in case if doesn't 
exist in the JSON data:

Json data from database
```json 
{
    "tenant": {
        "terms_and_privacy": {
            "es_CO": "Al continuar acepto la  <a class='underline' target='_blank' href='https: //www.placetopay.com/web/politicas-de-privacidad'> política de protección</a> de datos personales de <strong>Empresas del Grupo Evertec y sus filiales y subsidiarias</strong>"
        }
    }
}
```

Default values in ``config/tenant.php``:
```
return [
'terms_and_privacy' => [
        'en' => sprintf('By continuing, you accept the <a class="underline" target="_blank" href="%s"> personal data protection policy </a> of <strong>Companies of the Evertec Group and its affiliates and subsidiaries</strong>', 'https://www.placetopay.com/web/politicas-de-privacidad'),
        'it' => sprintf('Continuando ad accettare la <a class="underline" target="_blank" href="%s"> politica di protezione dei dati personali </a> di <strong>Società del Gruppo Evertec e delle sue affiliate e sussidiarie</strong>', 'https://www.placetopay.com/web/politicas-de-privacidad'),
        'pt' => sprintf('Ao continuar, você aceita a <a class="underline" target="_blank" href="%s"> política de proteção de dados pessoais </a> da <strong>Empresas do Grupo Evertec e suas afiliadas e subsidiárias</strong>', 'https://www.placetopay.com/web/politicas-de-privacidad'),
    ],
]
```

Example of use:
```
app('currentTenant')->translate('terms_and_privacy')
```

### Clear cache remotely
Probably you need to clear the app cache when you update the tenant information, 
to do this Cerberus publish a new POST route `clean-cache` that you can call from another application.

this route use middleware to validate if the application can be connected, this is an example of how you need to make the request 

```
$data = [
    'action' => 'cache:clear', //allowed action to perform
];

$signature = hash_hmac('sha256', json_encode($data), config('multitenancy.middleware_key'));

$url = 'https://tenant1.app.com/clean-cache';

Http::withHeaders(['Signature' => $signature])->post($url, $data);
```

You need to set this header in the request to clean the cache