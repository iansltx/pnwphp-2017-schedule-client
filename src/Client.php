<?php

namespace iansltx\PNWPHP2017ScheduleClient;

use Symfony\Component\DomCrawler\Crawler;

class Client
{
    const RELATIVE_TIMESTAMP = 1504565507; // date earlier in the conference week so relative dates will work

    protected $crawler;

    public static function create() : Client
    {
        return new self(new \Goutte\Client());
    }

    public function __construct(\Goutte\Client $client)
    {
        $this->crawler = $client;
    }

    public function getSchedule()
    {
        $tz = new \DateTimeZone('America/Los_Angeles');
        $doc = $this->crawler->request('GET', 'http://pnwphp.com/schedule');
        $events = [];

        $doc->filter('.events .events-group')->each(function (Crawler $group) use ($tz, &$events, $doc) {
            @list($dayOfWeek, $location) = explode(' ', $group->filter('.top-info span')->text(), 2);
            $date = date('Y-m-d', strtotime($dayOfWeek, static::RELATIVE_TIMESTAMP));

            // setting to array indexes to filter out Thursday events that show up in two columns for the same event
            $group->filter('.single-event')->each(function(Crawler $event) use ($tz, &$events, $date, $location, $doc) {
                $events[($startsAt = $date . ' ' . $event->attr('data-start')) . ' ' .
                    ($title = $event->filter('.event-name')->text())] = new Event(
                        \DateTimeImmutable::createFromFormat('Y-m-d H:i', $startsAt, $tz),
                        \DateTimeImmutable::createFromFormat('Y-m-d H:i', $date . ' ' . $event->attr('data-end'), $tz),
                        $title . (isset($location) ? (' in ' . $location) : ''),
                        $doc->filter('#' . $event->attr('data-content') . ' p')->text()
                );
            });
        });

        $events = array_values($events);
        $events[] = new Event( // WurstCon isn't in the normal place on the schedule, so we're adding it here
            \DateTimeImmutable::createFromFormat('Y-m-d H:i', '2017-09-10 11:00', $tz),
            \DateTimeImmutable::createFromFormat('Y-m-d H:i', '2017-09-10 14:00', $tz),
            "WurstCon at Shutzy's Bar and Grill",
            "Join us for this PHP community post-conference tradition!"
        );

        return new Schedule($events);
    }
}
