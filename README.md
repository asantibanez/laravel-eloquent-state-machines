[![Latest Version on Packagist](https://img.shields.io/packagist/v/asantibanez/laravel-eloquent-state-machines.svg?style=flat-square)](https://packagist.org/packages/asantibanez/laravel-eloquent-state-machines)
[![Total Downloads](https://img.shields.io/packagist/dt/asantibanez/laravel-eloquent-state-machines.svg?style=flat-square)](https://packagist.org/packages/asantibanez/laravel-eloquent-state-machines)

![Laravel Eloquent State Machines](https://banners.beyondco.de/Laravel%20Eloquent%20State%20Machines.png?theme=light&packageManager=composer+require&packageName=asantibanez%2Flaravel-eloquent-state-machines&pattern=circuitBoard&style=style_1&description=State+machines+for+your+Laravel+Eloquent+models+in+no+time&md=1&showWatermark=1&fontSize=100px&images=collection)

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

//With responsible
$salesOrder->status()->transitionTo('approved', [], $responsible); // auth()->user() by default
```

Checking available transitions
  
```php
$salesOrder->status()->canBe('approved');

$salesOrder->status()->canBe('declined');
```

Checking current state
  
```php
$salesOrder->status()->is('approved');

$salesOrder->status()->responsible(); // User|null 
```

Checking transitions history
  
```php
$salesOrder->status()->was('approved');

$salesOrder->status()->timesWas('approved');

$salesOrder->status()->whenWas('approved');

$salesOrder->fulfillment()->snapshotWhen('completed');

$salesOrder->status()->history()->get();
```

## Demo

You can check a demo and examples [here](https://github.com/asantibanez/laravel-eloquent-state-machines-demo)

![demo](https://github.com/asantibanez/laravel-eloquent-state-machines/raw/master/demo.gif)

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
    return 'pending'; // it can be null too 
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

### State Machine Methods  

When registering `$stateMachines` in our model, each state field will have it's own custom method to
interact with the state machine and transitioning methods. The `HasStateMachines` trait defines
one method per each field mapped in `$stateMachines`. Eg. 

For
```php 
'status' => StatusStateMachine::class,
'fulfillment_status' => FulfillmentStatusStateMachine::class
```

We will have an accompanying method 
```php
status();
fulfillment_status(); // or fulfillmentStatus()
```

with which we can use to check our current state, history and apply transitions. 

> Note: the field "status" will be kept intact and in sync with the state machine

### Transitioning States

To transition from one state to another, we can use the `transitionTo` method. Eg:

```php
$salesOrder->status()->transitionTo($to = 'approved');
```

You can also pass in `$customProperties` if needed
```php
$salesOrder->status()->transitionTo($to = 'approved', $customProperties = [
    'comments' => 'All ready to go'
]);
``` 

A `$responsible` can be also specified. By default, `auth()->user()` will be used
```php
$salesOrder->status()->transitionTo(
    $to = 'approved', 
    $customProperties = [], 
    $responsible = User::first()
);
``` 

When applying the transition, the state machine will verify if the state transition is allowed according
to the `transitions()` states we've defined. If the transition is not allowed, a `TransitionNotAllowed`
exception will be thrown.

### Querying History

If `recordHistory()` is set to `true` in our State Machine, each state transition will be recorded in
the package `StateHistory` model using the `state_histories` table that was exported when installing the
package.

With `recordHistory()` turned on, we can query the history of states our field has transitioned to. Eg:

```php
$salesOrder->status()->was('approved'); // true or false

$salesOrder->status()->timesWas('approved'); // int 

$salesOrder->status()->whenWas('approved'); // ?Carbon
``` 

As seen above, we can check whether or not our field has transitioned to one of the queried states.

We can also get the latest snapshot or all snapshots for a given state   

```php
$salesOrder->status()->snapshotWhen('approved');

$salesOrder->status()->snapshotsWhen('approved');
```

The full history of transitioned states is also available 

```php
$salesOrder->status()->history()->get();
``` 

The `history()` method returns an Eloquent relationship that can be chained with the following
scopes to further down the results.

```php
$salesOrder->status()->history()
    ->from('pending')
    ->to('approved')
    ->withCustomProperty('comments', 'like', '%good%')
    ->get();
``` 

### Using Query Builder

The `HasStateMachines` trait introduces a helper method when querying your models based on the state history of each 
state machine. You can use the `whereHas{FIELD_NAME}` (eg: `whereHasStatus`, `whereHasFulfillment`) to add constraints 
to your model queries depending on state transitions, responsible and custom properties.

The `whereHas{FIELD_NAME}` method accepts a closure where you can add the following type of constraints:

- `withTransition($from, $to)`
- `transitionedFrom($to)`
- `transitionedTo($to)`
- `withResponsible($responsible|$id)`
- `withCustomProperty($property, $operator, $value)`

```php
SalesOrder::with()
    ->whereHasStatus(function ($query) {
        $query
            ->withTransition('pending', 'approved')
            ->withResponsible(auth()->id())
        ;
    })
    ->whereHasFulfillment(function ($query) {
        $query
            ->transitionedTo('complete')
        ;
    })
    ->get();
```


### Getting Custom Properties

When applying transitions with custom properties, we can get our registered values using the
`getCustomProperty($key)` method. Eg.

```php
$salesOrder->status()->getCustomProperty('comments');
```

This method will reach for the custom properties of the current state. You can get custom 
properties of previous states using the snapshotWhen($state) method.

```php
$salesOrder->status()->snapshotWhen('approved')->getCustomProperty('comments');
```

### Getting Responsible

Similar to custom properties, you can retrieve the `$responsible` object that applied the state transition.

```php
$salesOrder->status()->responsible();
```

This method will reach for the responsible of the current state. You can get responsible of previous states 
using the snapshotWhen($state) method.

```php
$salesOrder->status()->snapshotWhen('approved')->responsible;
```

>Note: `responsible` can be `null` if not specified and when the transition happens in a background job. This is
>because no `auth()->user()` is available. 

## Advanced Usage

### Tracking Attribute Changes

When `recordHistory()` is active, model state transitions are recorded in the `state_histories` table. Each transition
record contains information about the attributes that changed during the state transition. You can get information
about what has changed via the `changedAttributesNames()` method. This method will return an array of the attributes
names that changed. With these attributes names, you can then use the methods `changedAttributeOldValue($attributeName)`
and `changedAttributeNewValue($attributeName)` to get the old and new values respectively.

```php
$salesOrder = SalesOrder::create([
    'total' => 100,
]);

$salesOrder->total = 200;

$salesOrder->status()->transitionTo('approved');

$salesOrder->changedAttributesNames(); // ['total']

$salesOrder->changedAttributeOldValue('total'); // 100
$salesOrder->changedAttributeNewValue('total'); // 200
```

### Adding Validations

Before transitioning to a new state, we can add validations that will allow/disallow the transition. To
do so, we can override the `validatorForTransition($from, $to, $model)` method in our state machine class.

This method must return a `Validator` that will be used to check the transition before applying it. If
the validator `fails()`, a `ValidationException` is thrown. Eg:

```php
class StatusStateMachine extends StateMachine
{
    public function validatorForTransition($from, $to, $model): ?Validator
    {
        if ($from === 'pending' && $to === 'approved') {
            return ValidatorFacade::make([
                'total' => $model->total,
            ], [
                'total' => 'gt:0',
            ]);
        }
        
        return parent:validatorForTransition($from, $to, $model);
    }
}
```

In the example above, we are validating that our Sales Order model total is greater than 0 before
applying the transition. 

### Adding Hooks 

We can also add custom hooks/callbacks that will be executed before/after a transition is applied. 
To do so, we must override the `beforeTransitionHooks()` and `afterTransitionHooks()` methods in our state machine 
accordingly.

Both transition hooks methods must return a keyed array with the state as key, and an array of callbacks/closures
to be executed.

> NOTE: The keys for beforeTransitionHooks() must be the `$from` states.
> NOTE: The keys for afterTransitionHooks() must be the `$to` states.

Example 

```php
class StatusStateMachine extends StateMachine
{
    public function beforeTransitionHooks(): array
    {
        return [
            'approved' => [
                function ($to, $model) {
                    // Dispatch some job BEFORE "approved changes to $to"
                },
                function ($to, $model) {
                    // Send mail BEFORE "approved changes to $to" 
                },
            ],
        ];
    }
    
    public function afterTransitionHooks(): array
    {
        return [
            'processed' => [
                function ($from, $model) {
                    // Dispatch some job AFTER "$from transitioned to processed"
                },
                function ($from, $model) {
                    // Send mail AFTER "$from transitioned to processed" 
                },
            ],
        ];
    } 
}
```

### Postponing Transitions

You can also postpone transitions to other states by using the `postponeTransitionTo` method.
This method accepts the same parameters as `transitionTo` plus a `$when` Carbon instance to specify
when the transition is to be run. 
 
`postponeTransitionTo` doesn't apply the transition immediately. Instead, it saves it 
into a `pending_transitions` table where it keeps track of all pending transitions for all
models. 

To enable running this transitions at a later time, you must schedule the 
`PendingTransitionsDispatcher` job class into your scheduler to run every one, five or ten minutes.

```php
$schedule->job(PendingTransitionsDispatcher::class)->everyMinute();
```

`PendingTransitionsDispatcher` is responsible for applying the postponed transitions at the specified
`$when` date/time.

You can check if a model has pending transitions for a particular state machine using the 
`hasPendingTransitions()` method

```php
$salesOrder->status()->hasPendingTransitions();
```

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
