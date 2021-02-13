# Changelog

All notable changes to `laravel-eloquent-state-machines` will be documented in this file

## v4.0.0 - 2021-02-12

- Added `changed_attributes` field in `state_histories` to record model old/new 
  values during transition (**Breaking change**)

## v3.0.0 - 2021-02-10

- Added `beforeTransitionHooks`
- Renamed `transitionHooks` to `afterTransitionHooks` and changed arguments for callbacks (**Breaking Change**)
- Refactored tests

## v2.3.0 - 2020-01-26

- Added support for PHP 8.
- Fixed `Str` import.

## v2.2.1 - 2020-12-22

- Added `snake_case()` and `camelCase()` method for state machine field.

## v2.2.0 - 2020-12-21

- Added macros on query builder to interact with `state_history`

## v2.1.2 - 2020-12-16

- Added auth()->user() in state history during model creation 

## v2.1.1 - 2020-12-15

- Fixed exported migrations

## v2.1.0 - 2020-12-15

- Added ability to postpone transitions

## v2.0.0 - 2020-12-15

- Added responsible property to StateHistory model

## v1.1.0 - 2020-12-14

- Added check for current state when transitioning to same state

## v1.0.1 - 2020-12-13

- Added check for auto recording history when on creating model event

## v1.0.0 - 2020-12-07

- Initial release. Enjoy 👍
