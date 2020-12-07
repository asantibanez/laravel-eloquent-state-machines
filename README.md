[![GitHub release](https://img.shields.io/github/release/asantibanez/laravel-eloquent-state-machines.svg)](https://github.com/asantibanez/laravel-eloquent-state-machines/releases/)

![Laravel Eloquent State Machines](https://banners.beyondco.de/Laravel%20Eloquent%20State%20Machines.png?theme=light&packageManager=composer+require&packageName=asantibanez%2Flaravel-eloquent-state-machines&pattern=circuitBoard&style=style_1&description=State+machines+for+your+Laravel+Eloquent+models+in+no+time&md=1&showWatermark=1&fontSize=100px&images=duplicate)

## Introduction

This package allows you to simplify the transitioning of states an Eloquent model could have by
defining the transition logic in specific StateMachine classes. Each class allows you to register
validations, hooks and allowed transitions and states making each StateMachine class the only source
of truth when moving from a state to the next.

Laravel Eloquent State Machines also allow you to automatically record history of all states a model
may have and query this history to take specific actions accordingly.

At its core, this package has been created to provide a simple but powerful API so Laravel developers
feel right at home. 

**Examples**

Model with two status fields

```php
$salesOrder->status; // 'pending', 'approved', 'declined' or 'processed'

$salesOrder->fulfillment; // null, 'pending', 'completed'
```

Transitioning from one state to another
  
```php
$salesOrder->status()->transitionTo('approved');

$salesOrder->fulfillment()->transitionTo('completed');

//With custom properties
$salesOrder->status()->transitionTo('approved', [
    'comments' => 'Customer has available credit',
]);
```

Checking available transitions
  
```php
$salesOrder->status()->canBe('approved');

$salesOrder->status()->canBe('declined');
```

Checking transitions history
  
```php
$salesOrder->status()->was('approved');

$salesOrder->status()->timesWas('approved');

$salesOrder->status()->whenWas('approved');

$salesOrder->fulfillment()->snapshowWhen('completed');

$salesOrder->status()->history()->get();
```

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

### Defining our StateMachine 

Imagine we have a `SalesOrder` model which has a `status` field for tracking the different stages
our sales order can be in the system: `REGISTERED`, `APPROVED`, `PROCESSED` or `DECLINED`.

We can manage and centralize all of these stages and transitions within a StateMachine class. To define
one, we can use the `php artisan make:state-machine` command.

For example, we can create a `StatusStateMachine` for our SalesOrder model

```bash
php artisan make:state-machine StatusStateMachine
```

After running the command, we will have a new StateMachine class created 
in the `App\StateMachines` directory. The class will have the following code.

```php
use Asantibanez\LaravelEloquentStateMachines\StateMachines\StateMachine;

class StatusStateMachine extends StateMachine
{
    public function recordHistory(): bool
    {
        return false;
    }

    public function transitions(): array
    {
        return [
            //
        ];
    }

    public function defaultState(): ?string
    {
        return null;
    }
}
```

Inside this class, we can define our states and allowed transitions

```php
public function transitions(): array
{
    return [
        'pending' => ['approved', 'declined'],
        'approved' => ['processed'],
    ];
}
```

We can define the default/starting state too

```php
public function defaultState(): ?string
{
    return 'pending'; // in can be null too 
}
```

The StateMachine class allows recording each one of the transitions automatically for you. To 
enable this behavior, we must set `recordHistory()` to return `true`;

```php
public function recordHistory(): bool
{
    return true; 
}
```

### Registering our StateMachine

Once we have defined our StateMachine, we can register it in our `SalesOrder` model, in a `$stateMachine`
attribute. Here, we set the bound model `field` and state machine class that will control it.  

```php
use Asantibanez\LaravelEloquentStateMachines\Traits\HasStateMachines;
use App\StateMachines\StatusStateMachine;

class SalesOrder extends Model 
{
    Use HasStateMachines;

    public $stateMachines = [
        'status' => StatusStateMachine::class
    ];   
}
```

### Transitioning State

WIP

### Querying History

WIP

### Adding Validations

WIP 

### Adding Hooks 

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
