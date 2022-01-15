# Workflow Process
[![PHP Composer](https://github.com/creatortsv/workflow/actions/workflows/php.yml/badge.svg)](https://github.com/creatortsv/workflow/actions/workflows/php.yml)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/creatortsv/workflow-process)

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
        $previous = $switcher->previous();
    
        try {
            // Get the previous stage position number
            // in the order of the stages
            $position = $previous->number();
            
            // Switch to specific stage or continue
            $switcher->switchTo(
                name: IncrementStage::class,
                number: $position ++
            );
            
            // Or if the stage which should be next is unknown
            // it needs to return to back and continue
            // with the previous order of stages
            $switcher->back()->skip(1);
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

### Switching between stages
As it is described above stages will be executed in the order in which they were passed to the constructor of the ```Workflow``` class.

#### Switch by the name of the stage
```php
// ...

$workflow = new Workflow(
    new MyStage(),
    new MyStage(),
    
    // Switched to the first stage
    fn (StageSwitcher $switch) => $switch(
        name: MyStage::class,
    ),
);

// ...
```
#### Switch by the name and specific number
In this case will be used both the name of the specific stage and the number, which is assigned automatically and depends on how many stages with the same name were passed in the ```Workflow``` class object.
```php
// ...

$workflow = new Workflow(
    new MyStage(), // number 1
    new MyStage(), // number 2
    
    // Switched to the previous stage
    fn (StageSwitcher $switch) => $switch(
        name: MyStage::class,
        number: 2,
    ),
);

// ...
```
#### Switch to the previous stage
In this case the previous stage can be anything.

For example, if the stage sends some message to a message broker and for some reason it was failed, before continue the next stage should switch it to back and try again.
```php
// ...

$workflow = new Workflow(
    new MyStage(),
    new MyStage(),
    
    // Switched to the previous stage
    fn (StageSwitcher $switch, ?Failed $failed = null)
        => $failed && $switch->back(),
        
    // Switch to the first stage by this
    fn (StageSwitcher $switch, ?Failed $failed = null)
        => $failed && $switch->back(length: 2),
);

// ...
```
The ```StageSwitcher::back``` method with ```length``` argument more than ```1``` switches to the previous stage and then decrements the position of the stack from the previous stage by ```length - 1```. If the ```length``` argument too big, it switches to the beginning.
```php
// ...

$workflow = new Workflow(
    new MyStage(),
    new MyStage(),
    new SwitchToTheEndStage(),
    new MyStage(), // Stage won't be executed
    
    //  Switched to the second stage
    fn (StageSwitcher $switch, ?Failed $failed = null)
        => $failed && $switch->back(length: 2),
    //  All the lines below switch to the first stage
    //  => $failed && $switch->back(length: 3),
    //  => $failed && $switch->back(length: 5),
);

// ...
```
The ```StageSwitcher::restart``` method also switches to the beginning, the difference between them is that the last one reset all the process, but with persisted artifacts.
```php
// ...

$workflow = new Workflow(
    function (
        StageSwitcher $switcher,
        MyObject ...$objects,
    ): void {
        print_r(count($objects)); // Is 0 for the first time
        print_r($switcher->previous()) // Always be null
    }
    
    fn (): MyObject => new MyObject(),
    
    // After this stage the first one
    // will print 1 instead of 0 and
    fn (StageSwitcher $switch) => $switch->restart();
);

// ...
```
#### Skip stages
Sometimes needed to log some result of each stage and doing this by the stage itself is not a good idea.

How it can be done ?
```php
// ...

// This example uses a Closure function as logger stage
$after = fn (StageSwitcher $switch) => $switch(
    name: Closure::class,
)

// Should be done for each of the stages
// that needed to be logged
$stageOne = CallbackWrapper::of(new MyStage());
$stageOne->after(callback: $after);

$stageTwo = new MyStage();
$workflow = new Workflow(
    $stageOne, // This stage will be logged
    $stageTwo, // And this one is not
    
    // Just skip the logger stage
    // Because it could be endless
    fn (StageSwitcher $switch) => $switch->skip(1),
    
    function (StageSwitcher $switch): void {
        $previous = $switch
            ->previos()
            ->name();
            
        // ... log message
        
        // Switched to the $stageOne and skip it.
        // It moves to the $stageTwo class
        $switch->back()->skip(1);
        
        // Or with the name of the stage
        $switch(name: $previous)->skip(1);
    },
);

// ...
```
### Artifacts injection
Artifacts can be injected in any order that needed.

The object of the ```Workflow``` class with 3 stages is given
- The first one returns ```MyObject``` with the value equals to 10
- The second one returns ```MyObject``` with the value equals to 20
- The third one returns ```MyMessage``` with the value "message"
```php
// ...

$workflow = new Workflow(
    fn (): MyObject => new MyObject(value: 10),
    fn (): MyObject => new MyObject(value: 20),
    fn (): MyMessage => new MyMessage(string: 'message'),
);

// ...
```

Let's create the stage which takes 2 objects each of types
```php
// ...

$stages = $workflow->getStages();
$stages[] = function (
    MyMessage $m,
    MyObject $obj,
): void {
    echo $m->message(); // Prints "message"
    echo $obj->value(); // Prints 20
};

$stages[] = function (
    MyMessage $m = null, // Exception won't be thrown if MyMessage is not ready
    MyObject ...$objects, // Exception won't be thrown if MyObject is not ready
    StageSwitcher $switcher // Predefined artifact that is always ready 
): void {
    // Prints 10 and 20
    aray_walk($objects, fn (MyObject $obj) => echo $obj->value())
}

$workflow->setStages(...$stages);

// ...
```
If the stage is trying to get some objects which:
- Is not nullable
- Is not variadic
- have not been passed as an argument into ```Workflow::makeRunner``` method
- Have not been returned by the stages before
- Will be returned by the stages after

**Exception will be thrown by these cases.** Except the ```StageSwitcher``` class object