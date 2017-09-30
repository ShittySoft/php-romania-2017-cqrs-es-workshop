<?php

namespace Specification;

use Behat\Behat\Context\Context;
use Building\Domain\Aggregate\Building;
use Building\Domain\DomainEvent\NewBuildingWasRegistered;
use Building\Domain\DomainEvent\UserCheckedIn;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\Aggregate\AggregateType;
use Rhumsaa\Uuid\Uuid;

final class CheckInCheckOut implements Context
{
    /**
     * @var Uuid
     */
    private $buildingId;

    /**
     * @var Building|null
     */
    private $aggregate;

    /**
     * @var AggregateChanged[]
     */
    private $pastEvents = [];

    /**
     * @var AggregateChanged[]|null
     */
    private $recordedEvents;

    /**
     * @Given a building has been registered
     */
    public function a_building_has_been_registered()
    {
        $this->buildingId = Uuid::uuid4();
        $this->recordPastEvent(NewBuildingWasRegistered::occur(
            $this->buildingId->toString(),
            ['name' => 'DX Solutions']
        ));
    }

    /**
     * @When the user checks into the building
     */
    public function the_user_checks_into_the_building()
    {
        $this->building()->checkInUser('Bob');
    }

    /**
     * @Then the user was checked into the building
     */
    public function the_user_was_checked_into_the_building()
    {
        if (! $this->popLastRecordedEvent() instanceof UserCheckedIn) {
            throw new \UnexpectedValueException();
        }
    }

    private function recordPastEvent(AggregateChanged $event)
    {
        $this->pastEvents[] = $event;
    }

    private function building() : Building
    {
        if (! $this->aggregate) {
            $this->aggregate = (new AggregateTranslator())
                ->reconstituteAggregateFromHistory(
                    AggregateType::fromAggregateRootClass(Building::class),
                    new \ArrayIterator($this->pastEvents)
                );

            $this->pastEvents = [];
        }

        return $this->aggregate;
    }

    private function popLastRecordedEvent() : AggregateChanged
    {
        if (! isset($this->recordedEvents)) {
            $this->recordedEvents = (new AggregateTranslator())
                ->extractPendingStreamEvents($this->building());
        }


        return \array_shift($this->recordedEvents);
    }
}
