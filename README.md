<br>

<p align="center">
    <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/boone-studios/laravel-surrealdb/main/assets/logo-light.svg">
        <img alt="SurrealDB for Laravel" src="https://raw.githubusercontent.com/boone-studios/laravel-surrealdb/main/assets/logo-dark.svg">
    </picture>
</p>

<br>

<p align="center">
    <a href="https://packagist.org/packages/boone-studios/laravel-surrealdb"><img src="https://img.shields.io/packagist/v/boone-studios/laravel-surrealdb.svg?style=flat-square" alt="Latest Version on Packagist"></a>
    <a href="https://packagist.org/packages/boone-studios/laravel-surrealdb"><img src="https://img.shields.io/packagist/dt/boone-studios/laravel-surrealdb" alt="Total Downloads"></a>
    <img src="https://github.com/boone-studios/laravel-surrealdb/actions/workflows/main.yml/badge.svg" alt="GitHub Actions">
</p>

## Overview

This package allows you to add a connection to SurrealDB in your Laravel project.

## Installation

You can install the package via Composer:

```bash
composer require boone-studios/laravel-surrealdb
```

## Usage

### Laravel

If you are using an older version of Laravel that doesn't support autoloading packages, add the service provider to `config/app.php`:

```php
BooneStudios\Surreal\SurrealServiceProvider::class
```

### Lumen

Add the service provider to `bootstrap/app.php` in your project.

```php
$app->register(BooneStudios\Surreal\SurrealServiceProvider::class);
```

## Configuration

To configure a new SurrealDB connection, add a new connection entry to `config/database.php`:

```php
'surrealdb' => [
    'driver' => 'surrealdb',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', 8000),
    'namespace' => env('DB_NAMESPACE', 'laravel'),
    'database' => env('DB_DATABASE', 'app'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', 'root'),
],
```

Regarding the `namespace` parameter, from the [SurrealDB documentation](https://surrealdb.com/docs/surrealql/statements/define/namespace):

>  SurrealDB has a multi-tenancy model which allows you to scope databases to a namespace. There is no limit to the number of databases that can be in a namespace, nor is there a limit to the number of namespaces allowed. Only users root users are authorized to create namespaces.
>
> Let's say that you're using SurrealDB to create a multi-tenant SaaS application. You can guarantee that the data of each tenant will be kept separate from other tenants if you put each tenant's databases into separate namespaces. In other words, this will ensure that information will remain siloed so user will only have access the information in the namespace they are a member of.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email support@boonestudios.org instead of using the issue tracker.

## Credits

- [Boone Studios, LLC](https://github.com/boone-studios)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Star History

[![Star History Chart](https://api.star-history.com/svg?repos=boone-studios/laravel-surrealdb&type=Date)](https://star-history.com/#boone-studios/laravel-surrealdb&Date)
