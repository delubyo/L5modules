# L5modules
Laravel 5 Modular implementation

## Installation

Install via Composer.

Add the following line to the `composer.json` file and fire `composer update`

```
"delubyo/l5modules": "dev-master"
```

Update your project's service provider in `config/app.php`

#### Service Provider
```
delubyo\L5modules\ModuleServiceProvider::class,
```

Update your project's `composer.json`

#### composer.json
```
    "autoload": {
        "classmap": [
            "database",
            "app/Modules"
        ],
        "psr-4": {
            "App\\": "app/",
            "Modules\\": "app/Modules"
        }
    }
```

#### Update \app\console\Kernel.php
```
\delubyo\L5modules\Console\ModuleCommand::class,
```