# Workflow Process
[![PHP Composer](https://github.com/creatortsv/workflow/actions/workflows/php.yml/badge.svg)](https://github.com/creatortsv/workflow/actions/workflows/php.yml)
![GitHub release (release name instead of tag name)](https://img.shields.io/github/v/release/creatortsv/workflow-process?include_prereleases)

This stand-alone PHP package helps you create and organize processes using stages, which you can easily configure to build different workflows
***
## Installation
Install this package using composer
```
composer require creatortsv/workflow-process
```
***

## Usage
Let's create the workflow process which will be used, for example, to create transaction for deposit money.

1. Create object of ```Creatortsv\WorkflowProcess\Workflow``` class and add stages to it. Stage is a callable object.

2. Call the ```makeRunner``` method with any objects as context, these objects you can use in your stages, and then call the ```run``` method.
3. Depending on the situation, use the ```then``` method to get data you need.
```php
use Creatortsv\WorkflowProcess\Workflow;

$workflow = new Workflow(
    // Checking the signature of the request
    new CheckSignature(),
    // Will be used 2 times through this process
    new CreateOrUpdate(),
    // Make request to some payment aggregator service
    new MakeRequestToExternalService(),
    fn (Transaction $transaction): JsonResponse => new JsonResponse($transaction),
);

$runner = $workflow
    ->makeRunner(new Transaction())
    ->run();

// If you need the Transaction object
$transaction = $runner->then(fn (Transaction $transaction): Transaction => $transaction);

// After all, you also can get the response object
// because every single stage produce artifacts
// which you can later use in your stages and get them after execution
// these artifacts will be automatically injected
// all you have to do define them as arguments
$response = $runner->then(fn (JsonResponse $response): JsonResponse => $response);
```
