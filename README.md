# Laravel MySQL stored procedures

## Motivation
Laravel's `DB` facade does not support calling stored procedures that returns multiple data sets in result out of the box. This package provides a simple service for calling and retrieving MySQL stored procedures by name and getting all its returned datasets back.

Based on [@tommyready's](https://github.com/tommyready)  [`PDOService`](https://gist.github.com/tommyready/2803f4d7ae7522f707bd090c03bd1c6b) class. Refactored, covered with unit tests and issued as Composer package from my side.

# Installation
`composer require rodion-arr/laravel-mysql-stored-procedures`

# Usage
```php
require_once __DIR__.'/vendor/autoload.php'; // Autoload files using Composer

use RodionARR\PDOService;
use Illuminate\Support\Facades\App;

/**
@var PDOService $service
*/
$service = App::make(PDOService::class);
$multipleRowsets = $service->callStoredProcedure('store_procedure_name', ['param1', 'param2', '....']);

dd($multipleRowsets);
```
