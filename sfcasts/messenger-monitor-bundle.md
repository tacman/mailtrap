# Bonus: Messenger Monitor Bundle

Hey, you're *still* here? Great! Let's do one final bonus chapter!

When you have a bunch of messages and schedules running in the background,
it can be hard to know what's happening. Are my workers running? Is my schedule
running? And where is it running to? What about failures? I mean, we have logs,
but... *logs*. Instead, let's explore a cool bundle that gives us a UI to get some
visibility on what's going on with our army of worker robots!

## Installation

At your terminal, run:

```terminal
composer require zenstruck/messenger-monitor-bundle
```

It's asking to install a recipe, say yes. Jump back to our IDE and see
what was added.

First, a `src/Schedule.php` was added. This is unrelated to this bundle.
Since the last chapter, where we added the `Symfony Scheduler`, it now
has an official recipe that adds a default schedule. Since we already
have one, delete this file.

## `MessengerMonitorController`

A new controller was added: `src/Controller/Admin/MessengerMonitorController.php`.
This is a *stub* to enable the bundle's UI. It extends this `BaseMessengerMonitorController`
from the bundle and adds a route prefix of `/admin/messenger`. It also
adds this `#[IsGranted('ROLE_ADMIN')]` attribute. This is *super* important
for your *real* apps. You *only* want site admins to access the UI as it
shows sensitive information. We don't have security configured in this app,
so I'll just remove this line:

[[[ code('56502c4e95') ]]]

## `ProcessedMessage`

`src/Entity/ProcessedMessage.php` is a new entity added by the recipe. This is
also a *stub* that extends this `BaseProcessedMessage` class and
adds an ID column. This is used to track the history of your messenger messages. For
every message processed, a new one of these entities is persisted. Don't worry
though, this is done in your worker process, so it won't slow down your
app's frontend.

Since we have a new entity, we *should* be adding a migration, but I
don't have migrations configured for this project. So in your terminal, run:

```terminal
symfony console doctrine:schema:update --force
```

## Install Optional Dependencies

Before we check out the UI, the bundle has two optional dependencies that
I want to install. First:

```terminal
composer require knplabs/knp-time-bundle
```

This makes the UI's timestamps human-readable - like "4 minutes ago". Next:

```terminal
composer require lorisleiva/cron-translator
```

Since we're using cron expressions for our scheduled tasks, this package
makes them human-readable. So instead of "11 2 * * *", it will display this
as "every day at 2:11 AM". Slick!

We're ready to go! Start the server with:

```terminal
symfony serve -d
```

## Dashboard

Jump over to the browser and visit: `/admin/messenger`. This is the
Messenger Monitor dashboard!

This first widget shows running workers and their status. We can see we
have 1 worker running for our `async` transport. This is the one we
configured to run with our Symfony CLI server.

Below, we see our available transports, how many messages are queued, and
how many workers are running them. Notice it shows our `scheduler_default`
transport as not running. This is expected, as we didn't configure it to run
locally.

Below that, we have a snapshot of statistics for the last 24 hours.

On the right, we will see the last 15 messages processed. This is of course
empty right now.

All these widgets autorefresh every 5 seconds.

## Schedule

Let's create some history! In the top bar, click on `Schedule` (note the
icon is red to further indicate the schedule isn't running). This is kind
of a "more advanced `debug:schedule` command". We see our single scheduled
task: `RunCommandMessage` for `app:send-booking-reminders`. It uses a
`CronExpressionTrigger` to run "every day at 2:11 AM". 0 runs so far but
we can run it manually by clicking "Trigger"... and selecting our `async`
transport.

## "Details"

Jump back to the dashboard. It ran successfully, took 58ms, and consumed
31MB of memory. Click "Details" to see even more information! "Time in Queue",
"Time to Handle", timestamps... lots of good stuff.

These tags are super helpful for filtering messages.
You can add your own tags but some are added by the bundle: `manual`, because
we "manually" ran a scheduled task, `schedule`, because it was a scheduled
task, `schedule:default`, because it's part of our *default* schedule.
This `schedule:default:<hash>` is the unique ID for this scheduled task.

On the right here is the "result" of the message "handler" - in this case,
`RunCommandMessageHandler`. Different handlers have different results (some
have none). In this case, the result is the command's exit code and output.

> Sent 0 booking reminders

Let's run this task again, but this time, with a booking that needs a reminder
sent. Back in your terminal, reload our fixtures:

```terminal
symfony console doctrine:fixtures:load
```

Back to the browser. The dashboard is empty now but that's expected: reloading our
fixtures also cleared our message history. Click "Schedule", then "Trigger" on our
"async" transport.

Back on the dashboard, we have 2 messages now. `RunCommandMessage` again but
click its "Details":

> Sent 1 booking reminders

Now our second message: `SendEmailMessage`. This was dispatched by the
command. Click its "Details" to see email-related information for its
results. Note the tag, `booking_reminder`. The bundle automatically
detected that we were sending an email with a "Mailer" tag, so it added
it here.

## Transports

In the top menu, you can click "Transports" to see more details on each
one's pending messages (if applicable). The `failed` transport shows
failed messages and gives you the option to retry or remove them, right
from the UI!

## History

"History" is where we can filter messages: Period, limit to a specific
date range. Transport, limit to a specific transport. Status, show just
successes or failures. Schedule, whether to include or exclude messages triggered
by a schedule. Message type, filter by message class.

## Statistics

"Statistics" shows a per-message-class stat summary and can be limited
to a specific date-range.

## Purge Message History

As you can probably imagine, if your app executes a lot of messages, our
history table can get *really* big. The bundle provides some commands to
purge older messages.

In the bundle docs, scroll down to "messenger:monitor:purge" and copy the
command. We need to schedule this... but how? With
Symfony Scheduler of course! Open `src/Scheduler/MainSchedule.php` and
add a new task with `->add(RecurringMessage::cron())`. Use `#midnight`
so it runs daily between midnight and 3am. Add `new RunCommandMessage()`
and paste the command. Add the `--exclude-schedules` option:

[[[ code('066bc292db') ]]]

This will purge
messages older than 30 days *except* messages triggered by a schedule.
This is important because your scheduled tasks might run once a month or even
once a year. This enables you to keep a history of them regardless of their frequency.

## Purge Schedule History

We should still clean these up though. So, back in the docs, copy a
second command: `messenger:monitor:schedule:purge`. And in the schedule,
add it with `->add(RecurringMessage::cron('#midnight', new RunCommandMessage()))`
and paste:

[[[ code('65f53c81b7') ]]]

This will purge the history of scheduled messages
skipped by the command above *but* keep the last 10 runs of each.

Let's make sure these tasks were added to our schedule. Back in the browser,
click "Schedule" and here we go: our two new tasks.

For the task we ran manually earlier, we can see the last run summary, details,
and even its history.

Ok friends! That's a quick run-through of the `zenstruck/messenger-monitor-bundle`.
Check out the [docs](https://github.com/zenstruck/messenger-monitor-bundle) for
more information on all it's features.

'Til next time, happy monitoring!
