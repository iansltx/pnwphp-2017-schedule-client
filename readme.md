PNWPHP 2017 Schedule Client
===========================

Scrapes the PNWPHP 2017 schedule, providing a list of conference events.

This file is syntactically valid PHP, so you can run it to see how the examples
work. And yes, that's why there are close-tags everywhere.

To start, `composer require iansltx/pnwphp-2017-schedule-client`. Then:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

$client = \iansltx\PNWPHP2017ScheduleClient\Client::create(); // set up a client with default params
print("Getting schedule...\n");
$schedule = $client->getSchedule(); // make the call to the PNWPHP website and scrape the schedule

?>
```

A schedule is an event collection with a few convenience methods, containing
Event objects, which implement __toString() and jsonSerialize(). Both return
a string suitable for display, so if you want more data in your JSON blob
you'll want to pull data out of each event manually.

```php
<?php

date_default_timezone_set('America/Los_Angeles');
/** @var \iansltx\PNWPHP2017ScheduleClient\Schedule $schedule */
print("The next event is " . $schedule->filterOutPast(new \DateTimeImmutable('2017-09-10'))->first() . ".\n");

?>
```
