# Workflow Process
[![PHP Composer](https://github.com/creatortsv/workflow/actions/workflows/php.yml/badge.svg)](https://github.com/creatortsv/workflow/actions/workflows/php.yml)
![GitHub release (release name instead of tag name)](https://img.shields.io/github/v/release/creatortsv/workflow-process?include_prereleases)

This stand-alone PHP package helps to create and organize processes using stages, which can be easily configured to build different workflows
***
## Installation
Install this package using composer
```
composer require creatortsv/workflow-process
```
## Going deeper into stages

There are a few things to know about the stages.
- The stage can be any ```callable``` object like ```Closure``` function, object which implements the ```__invoke``` method and other.
- If a stage returns some data it will be saved as an artifact which might be injected in other stages later.
- By default, the stages will be executed in the order in which they were passed to the constructor of the ```Workflow``` class.
- Execution order can be changed by the stages themselves.
- If this behaviour is not managed, execution will be continued in the order of the stages.
> **NOTE** It is highly recommended to use objects as stages, that implement the ```__invoke``` method, to be able to switch between them more conveniently and clearly.

## Usage

The basic usage example:

```php
use Creatortsv\WorkflowProcess\Workflow;

class Amount
{
    public function __construct(private int $amount) {}
    
    public function increment(int $plus): Amount
    {
        $this->amount += $plus;
        
        return $this;
    }
    
    public function getAmount(): int
    {
        return $this->amount;
    }
}

class IncrementStage
{
    public function __construct(private int $plus) {}
    
    public function __invoke(Amount $amount): Amount
    {
        return $amount->increment(plus: $this->plus);
    }
}

$workflow = new Workflow(
    new IncrementStage(plus: 3),
    new IncrementStage(plus: 7),
);

$amount = new Amount(amount: 0);
$runner = $workflow->makeRunner(context: $amount);
$runner->run();

echo $amount->getAmount(); // Prints 10
```
Now it needs to be printed the result message for each of the stages. But the point is that the class ```IncrementStage``` cannot be used for that. How it can be done ?
```php
use Creatortsv\WorkflowProcess\Workflow;
use Creatortsv\WorkflowProcess\Stage\StageSwitcher;
use Creatortsv\WorkflowProcess\Exception\StageNotFoundException;

// ... declaration of the Amount class

class IncrementStage
{
    // ... constructor
    
    public function __invoke(
        Amount $amount,
        StageSwitcher $switcher,
    ): Amount {
        // Switch to specific stage
        // The same behaviour as switchTo method
        $switcher(name: MakeMessageStage::class);
    
        return $amount->increment(plus: $this->plus);
    }
}

class Message
{
    public function __construct(private string $message) {}
    
    public  function __toString(): string
    {
        return $this->message;
    }
}

class MakeMessageStage
{
    public function __invoke(
        Amount $amount,
        StageSwitcher $switcher,
    ): Message {
        // Get info about the previous stage
        $previous = $switcher->prev();
    
        try {
            // Get the previous stage position number
            // in the order of the stages
            $position = $previous->number();
            
            // Switch to specific stage or continue
            $switcher->switchTo(
                name: IncrementStage::class,
                number: $position ++
            );
            
            // Checking if the next stage exists
            $switcher->next();
        } catch (StageNotFoundException $e) {
        }
        
        $message = sprintf(
            'Result of the "%s" with position "%s" is: %s',
            $previous->name(),
            $previous->number(),
            $amount->getAmount(),
        );
        
        return new Message(message: $message);
    }
}

$workflow = new Workflow(
    new IncrementStage(plus: 3),
    new IncrementStage(plus: 7),
    new MakeMessageStage(),
    // Add the anonymous stage for example
    // That takes all the messages and prints each of them
    function (Message ...$messages): void {
        array_walk($messages, fn (Message $m) => echo (string) $m)
    },
);

$amount = new Amount(amount: 0);
$runner = $workflow->makeRunner(context: $amount);
$runner->run();

// It prints
// Result of the "IncrementStage" with position "1" is: 3
// Result of the "IncrementStage" with position "2" is: 10

echo $amount->getAmount() // Still prints 10
```
The last stage is not necessary, it could be done directly in the MakeMassageStage, but this example just shows how to work with artifact's injection.

### Using Workflow as the stage of the process

Each ```Workflow``` class can be used like the other stages, for example:
```php
use Creatortsv\WorkflowProcess\Workflow;

// ... Classes declaration

class MyWorkflow extends Workflow
{
    public function __invoke(Amount $amount): Amount
    {
        $this->makeRunner(context: $amount)->run();   
    
        return $amount;
    }
}

$stages = array_fill(0, 3, new MyWorkflow(
    new IncrementStage(plus: 3),
    new IncrementStage(plus: 7),
    new MakeMessageStage(),
    function (Message ...$messages): void {
        array_walk($messages, fn (Message $m) => echo (string) $m)
    },
));

$workflow = new Workflow(...$stages);

$amount = new Amount(amount: 0);
$runner = $workflow->makeRunner(context: $amount);
$runner->run();

// It prints
// Result of the "IncrementStage" with position "1" is: 3
// Result of the "IncrementStage" with position "2" is: 10
// Result of the "IncrementStage" with position "1" is: 13
// Result of the "IncrementStage" with position "2" is: 20
// Result of the "IncrementStage" with position "1" is: 23
// Result of the "IncrementStage" with position "2" is: 30

echo $amount->getAmount(); // Prints 30 
```
Any of the artifacts, which is generated by the ```Workflow``` class, can be extracted from its ```WorkflowRunner``` object, for example:
```php
$workflow = new Workflow(
    new IncrementStage(plus: 3),
    new IncrementStage(plus: 7),
    new MakeMessageStage(),
    function (Message ...$messages): void {
        array_walk($messages, fn (Message $m) => echo (string) $m)
    },
);

$amount = new Amount(amount: 0);
$runner = $workflow->makeRunner(context: $amount);
$runner->run();

[$amount, $messageOne, $messageTwo] = $runner->then(
    callback: fn (
        Amount $amount,
        Message ...$messages
    ): array => [$amount, ...$messages],
);
```