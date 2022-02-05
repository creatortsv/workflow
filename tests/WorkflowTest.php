<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Tests;

use Closure;
use Creatortsv\WorkflowProcess\Artifacts\ArtifactsStorage;
use Creatortsv\WorkflowProcess\Exception\StagesNotFoundException;
use Creatortsv\WorkflowProcess\Exception\WorkflowRequirementsException;
use Creatortsv\WorkflowProcess\Exception\WorkflowRequirementsMissedException;
use Creatortsv\WorkflowProcess\Runner\WorkflowRunner;
use Creatortsv\WorkflowProcess\Stage\Stage;
use Creatortsv\WorkflowProcess\Stage\StageSwitcher;
use Creatortsv\WorkflowProcess\Support;
use Creatortsv\WorkflowProcess\Tests\Proto\Amount;
use Creatortsv\WorkflowProcess\Workflow;
use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class WorkflowTest extends TestCase
{
    public function testCreate(): Workflow
    {/* Default empty workflow */
        $workflow = new Workflow();

        $this->assertNull($workflow->required);
        $this->assertCount(0, Support\Helper\SpyHacker::hack($workflow)->stages ?? []);

    /** Workflow with requirements error */
        try {
            new #[Support\Workflow(required: 'some')] class {};
        } catch (Exception $e) {
            $this->assertInstanceOf(WorkflowRequirementsException::class, $e);
            $this->assertSame(
                'Neither class nor interface of the requirement with the given name "some" do not exist',
                $e->getMessage(),
            );
        }

    /** Workflow with required object & stages */
        $workflow = new #[Support\Workflow(required: Amount::class)] class (
            fn (Amount $amount): Amount => $amount->add(1),

            #[Support\Stage(enabled: false)]
            fn (Amount $amount): Amount => $amount->add(2),

            #[Support\Stage(name: 'stage.third')]
            fn (Amount $amount): Amount => $amount->add(3),

            #[Support\Stage(name: 'stage.fourth')]
            fn (Amount $amount): Amount => $amount->add(4),
        ) extends Workflow {};

        $stages = Support\Helper\SpyHacker::hack($workflow)->stages ?? [];

        $this->assertSame(Amount::class, $workflow->required);
        $this->assertCount(4, $stages);
        $this->assertCount(1, array_filter($stages, fn (Stage $s): bool => $s->enabled !== true));

        return $workflow;
    }

    /**
     * @depends testCreate
     * @throws StagesNotFoundException
     */
    public function testEnable(Workflow $workflow): Workflow
    {
        $workflow->enable(Closure::class);
        $stages = Support\Helper\SpyHacker::hack($workflow)->stages ?? [];

        $this->assertCount(0, array_filter($stages, fn (Stage $s): bool => $s->enabled !== true));

        try {
            $workflow->enable('some', 'another');
        } catch (Exception $e) {
            $this->assertInstanceOf(StagesNotFoundException::class, $e);
            $this->assertSame('Stages "some,another" not found', $e->getMessage());
        }

        return $workflow;
    }

    /**
     * @depends testEnable
     * @throws StagesNotFoundException
     */
    public function testDisable(Workflow $workflow): Workflow
    {
        $workflow->disable('stage.third');
        $stages = Support\Helper\SpyHacker::hack($workflow)->stages ?? [];

        $this->assertCount(1, $stages = array_filter($stages, fn (Stage $s): bool => $s->enabled !== true));
        $this->assertSame('stage.third', current($stages)?->name);

        try {
            $workflow->disable('some', 'another');
        } catch (Exception $e) {
            $this->assertInstanceOf(StagesNotFoundException::class, $e);
            $this->assertSame('Stages "some,another" not found', $e->getMessage());
        }

        return $workflow;
    }

    /**
     * @depends testDisable
     */
    public function testGetEnabled(Workflow $workflow): Workflow
    {
        $this->assertNotContainsOnly('stage.third', $workflow->getEnabled());

        return $workflow;
    }

    /**
     * @depends testGetEnabled
     * @throws WorkflowRequirementsMissedException
     */
    public function testMakeRunner(Workflow $workflow): void
    {
        try {
            $workflow->makeRunner();
        } catch (Exception $e) {
            $this->assertInstanceOf(WorkflowRequirementsMissedException::class, $e);
            $this->assertSame(
                'One of passed arguments must be instance of the "'.Amount::class.'"',
                actual: $e->getMessage(),
            );
        }

        $runner = $workflow->makeRunner(new Amount(amount: 10));

        $this->assertInstanceOf(WorkflowRunner::class, $runner);
    }

    /**
     * @throws WorkflowRequirementsMissedException
     * @throws StagesNotFoundException
     * @throws ReflectionException
     */
    public function testRealCase(): void
    {
        $workflow = new Workflow(
            #[Support\Stage('stage.one')]
            fn (Amount $amount) => $amount->add(plus: 2),
            #[Support\Stage('stage.two')]
            fn (Amount $amount) => throw new Exception('Some error'),
            #[Support\Stage('stage.three')]
            fn (Amount $amount) => $amount->add(plus: 10),
            #[Support\Stage('stage.four')]
            fn (Amount $amount) => $amount->add(plus: 20),
            #[Support\Stage('stage.five')]
            fn (Amount $amount) => $amount->add(plus: 40),
        );

        $amount = new Amount();
        $runner = $workflow->makeRunner($amount);

        try {
            $runner->run();
        } catch (Exception) {
            $runner->then(function (StageSwitcher $switch): void {
                $switch('stage.four');
            });

            $runner->run();
        }

        $this->assertSame(62, $amount->amount);
    }
}
