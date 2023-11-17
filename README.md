[![Latest Version on Packagist](https://img.shields.io/packagist/v/ahmedashraf093/better-eloquent-state-machines.svg?style=flat-square)](https://packagist.org/packages/ahmedashraf093/better-eloquent-state-machine)
[![Total Downloads](https://img.shields.io/packagist/dt/ahmedashraf093/better-eloquent-state-machines.svg?style=flat-square)](https://packagist.org/packages/ahmedashraf093/better-eloquent-state-machine)

![Eloquent State Machine](https://banners.beyondco.de/Eloquent%20State%20Machine.jpeg?theme=dark&packageManager=composer+require&packageName=ahmedashraf093%2Flaravel-eloquent-state-machines&pattern=circuitBoard&style=style_2&description=A+better+state+machine+for+your+eloquent+models+with+states&md=1&showWatermark=1&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg)


## Introduction

This package provides a very simple and easy API to reliably manage your state machines withing your eloquent models all in one file. using a simple one liners to do all the validation, logging and execution of your state transitions.

> based on the work of [asantibanez](https://github.com/asantibanez)'s state machine [laravel-eloquent-state-machines](https://github.com/asantibanez/laravel-eloquent-state-machines)


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

# php named args example
$salesOrder->status()->transitionTo(to: 'approved', responsible: auth()->user())
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

$salesOrder->fulfillment()->snapshowWhen('completed');

$salesOrder->status()->history()->get();
```


## Features

- Define your state machines in a single file
- Define your state machines with states and allowed transitions
- Allow wildcards to allow any state change
- Allow custom properties to be saved with each transition
- Allow responsible to be saved with each transition
- Allow to record history of state transitions
- Allow to query models based on state transitions
- Allow to validate state transitions
- Allow to add hooks/callbacks before/after state transitions

<!-- ## Demo

You can check a demo and examples [here](https://github.com/ahmedashraf093/better-eloquent-state-machines-demo)

![demo](https://github.com/ahmedashraf093/better-eloquent-state-machines/raw/master/demo.gif) -->

## Installation

You can install the package via composer:

```bash
composer require ahmedashraf093/better-eloquent-state-machines
```

Next, you must export the package migrations

```bash
php artisan vendor:publish --provider="Ashraf\EloquentStateMachine\LaravelEloquentStateMachinesServiceProvider" --tag="migrations"
```

Finally, prepare required database tables

```bash
php artisan migrate
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
use Ashraf\EloquentStateMachine\StateMachines\StateMachine;

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
        'pending' => [
            'approved' => fn($model, $who): bool => true, 
            'declined' => fn($model, $who): bool => $who->getTable() === User::tableName(), // only allow users to decline
        ],
        'approved' => [
            'processed' // no need for extra validation
        ],
    ];
}
```

Wildcards are allowed to allow any state change

```php
public function transitions(): array
{
    return [
        '*' => ['approved', 'declined'], // From any to 'approved' or 'declined'
        'approved' => '*', // From 'approved' to any
        '*' => '*', // From any to any
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
use Ashraf\EloquentStateMachine\Traits\HasStateMachines;
use App\StateMachines\StatusStateMachine;

class SalesOrder extends Model
{
    Use HasStateMachines;

    /**
     *  mark the `status` to be controlled by `StatusStateMachine`
     */
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
```php
# PHP8 named args
$salesOrder->status()->transitionTo(to: 'approved');
```

You can also pass in `$customProperties` if needed
```php
$salesOrder->status()->transitionTo($to = 'approved', $customProperties = [
    'comments' => 'All ready to go'
]);
```
```php
# PHP8 named args
$salesOrder->status()->transitionTo(
    to: 'approved', 
    customProperties: [
        'comments' => 'All ready to go'
    ]
);
```

A `$responsible` can be also specified. By default, `auth()->user()` will be used
```php
$salesOrder->status()->transitionTo(
    $to = 'approved',
    $customProperties = [],
    $responsible = User::first()
);
```
```php
# PHP8 named args
$salesOrder->status()->transitionTo(
    to: 'approved',
    responsible: User::first()
);
```

When applying the transition, the state machine will verify if the state transition is allowed according
to the `transitions()` states we've defined. If the transition is not allowed, a `Ashraf\EloquentStateMachine\Exceptions\TransitionNotAllowed`
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

The `$from` and `$to` parameters can be either a status name as a string or an array of status names.

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

#### Using closure functions

Using closure function to do per state validation before transitioning to the next state.

here is an example of a state machine that allows any user to approve a sales order but only users can decline it.
```php
public function transitions(): array
{
    return [
        'pending' => [
            'approved' => fn($model, $who): bool => true, 
            'declined' => fn($model, $who): bool => $who->getTable() === User::tableName(), // only allow users to decline
            #             ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
        ],
        'approved' => [
            'processed' // no need for extra validation
        ],
    ];
}
```
the arrow function 
```php 
fn($model, $who): bool => $who->getTable() === User::tableName()
``` 
will be called before transitioning to the next state and will be passed the model and the responsible for the transition. the `bool` value returned by the function will determine if the transition will be allowed or not.

a more complex example would be to allow only users with a specific role to approve a sales order.
```php
public function transitions(): array
{
    return [
        'pending' => [
            'approved' => fn($model, $who): bool => $who->hasRole('sales_manager') && $who->can('approve', $model),
            'declined' => fn($model, $who): bool => $who->getTable() === User::tableName(), // only allow users to decline
        ],
        'approved' => [
            'processed' // no need for extra validation
        ],
    ];
}
```


### Get available transitions

using the `->availableTransitions()` method you can get all the available transitions from the current state with all validation applied.

```php
$salesOrder->status()->availableTransitions(); // ['approved', 'declined']
```

different validations can be applied to the same transition depending on the responsible for the transition.

```php
$user = User::first();
$salesOrder->status()->availableTransitions($user); // ['approved']
```


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


### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email ahmedashraaf093+ghissues@gmail.com instead of using the issue tracker.

## Credits
- [Ahmed Ashraf](https://github.com/ahmedashraf093)
- [Andrés Santibáñez](https://github.com/asantibanez)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
