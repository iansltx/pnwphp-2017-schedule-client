<?php

namespace iansltx\PNWPHP2017ScheduleClient;

/**
 * Immutable collection of events
 */
class Schedule implements \Countable
{
    protected $events;

    public function __construct(array $events)
    {
        usort($events, function(Event $event1, Event $event2) {
            return $event1->getStartsAt() <=> $event2->getStartsAt() ?:
                (strpos($event2->getTitle(), 'Room 111') !== false) <=>
                (strpos($event1->getTitle(), 'Room 111') !== false); // allow for stable sort based on Thursday room
        });
        $this->events = $events;
    }

    public function count()
    {
        return count($this->events);
    }

    public function filter(callable $filter) : Schedule
    {
        return new static(array_filter($this->events, $filter));
    }

    /**
     * Returns a new schedule containing only events that start at or after $now
     *
     * @param \DateTimeInterface $now
     * @return Schedule
     */
    public function filterOutPast(\DateTimeInterface $now) : Schedule
    {
        return $this->filter(function(Event $event) use ($now) {
            return $event->getStartsAt() >= $now;
        });
    }

    /**
     * @return Event[]
     */
    public function getEvents() : array
    {
        return $this->events;
    }

    public function first() : Event
    {
        if (!isset($this->events[0])) {
            throw new NoMoreEventsException;
        }
        return $this->events[0];
    }

    public function second() : Event
    {
        if (!isset($this->events[1])) {
            throw new NoMoreEventsException;
        }
        return $this->events[1];
    }
}
