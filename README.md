[![GitHub release](https://img.shields.io/github/release/asantibanez/laravel-eloquent-state-machines.svg)](https://github.com/asantibanez/laravel-eloquent-state-machines/releases/)

![Laravel Eloquent State Machines](https://banners.beyondco.de/Laravel%20Eloquent%20State%20Machines.png?theme=light&packageManager=composer+require&packageName=asantibanez%2Flaravel-eloquent-state-machines&pattern=circuitBoard&style=style_1&description=State+machines+for+your+Laravel+Eloquent+models+in+no+time&md=1&showWatermark=1&fontSize=100px&images=duplicate)

## Installation

You can install the package via composer:

```bash
composer require asantibanez/laravel-eloquent-state-machines
```

Next, you must export the package migrations

```bash
php artisan vendor:publish --provider="Asantibanez\LaravelEloquentStateMachines\LaravelEloquentStateMachinesServiceProvider" --tag="migrations"
```


## Usage

WIP

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email santibanez.andres@gmail.com instead of using the issue tracker.

## Credits

- [Andrés Santibáñez](https://github.com/asantibanez)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
