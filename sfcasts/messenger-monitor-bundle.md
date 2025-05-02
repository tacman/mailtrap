# Messenger Monitor Bundle

Coming soon...

"Hey, you're still here? Okay, great. Let's do one final bonus chapter.
So, when you have a bunch of messages and schedules running, it can be
hard to kind of understand or know what's happening, and even when errors
occur, exactly what the error is. I mean, we have logs, but logs. So, I'm
going to show you a cool bundle that can provide a UI to get some
visibility on what's going on. So, at your terminal, we're running
`composer require zenstruck/messenger-monitor-bundle`. It's going to ask
us to install a recipe, so I'll say yes. All right, and let's jump back to
our IDE and see what this added. So, first thing, under source, you can
see there's this new `schedule.php`. This isn't from the bundle we just
installed, but since we did the last chapter, Symfony Scheduler now
contains its own recipe that basically just adds a default schedule, kind
of like what we saw in the last course. So, because we already did that,
I'm just going to delete this because we don't need it. We already have
our schedule. Okay, and we can see here that there was a controller added
under `admin/messenger/monitor/controller`, and if we open up this, this
is just kind of a stub to add a route, to add the UI messenger monitor
route to our app. So, as you can see, it adds the route prefix of
`admin/messenger`, and right here we can see we're using
`isGrantedRoleAdmin`. So, we're not using security in this course, but it
is very important that you have this endpoint under some kind of security
that only site admins can use because some damage can be done with this
UI. So, we'll just delete that now for this app, and we also have a new
entity, `EntityProcessedMessage`, and again, this is kind of a stub that
just adds an ID to this base message process, and this contains all the
data, all the fields we need for reviewing the history of our messenger
runs. So, we don't need to do anything here, so let's close these, and
because we added a new entity, we should be adding a migration, but I
don't have migrations configured for this project, so I'm just going to
use `Symfony Console Doctrine Schema Update Force`. `Symfony Console
Doctrine Schema Update Force`. Okay, so we're almost ready to check out
the UI, but the bundle actually has two optional dependencies that just
help make the UI look a little nicer. So, the first one is
`ComposerRequireKNPLabKNPTimeBundle`. This is just going to make our
timestamps in the UI look a little nicer. It's going to say, you know,
four days ago or four minutes ago, as opposed to the raw timestamp, just
makes it a little bit easier when you're just reviewing, and then we'll
clear this, and the last one is `ComposerRequireLorisLivaCronTranslator`.
Because we're using cron expressions for our scheduled tasks, what this
package does is it makes those look a little bit nicer, like human
readable, so it'll say, you know, every day at 12.02 a.m. or something
like that. Okay, we're ready to check out the UI, so let's spin up the web
server. `Symfony serve-d`. Okay, and we'll jump over to our browser.

-------SPLIT-------

Okay, let's just refresh the page to make sure we're working. And then the
UI is at `Admin Messenger`. Okay, and so this is the dashboard. So we can
see here on the left, we can see the worker status. This is showing us
that our `async` transport is running, and that's what we configured
earlier in the course in the `Symfony CLI`, so that is expected. And then
we can see we have two transports, `async` and then our `scheduler
default`, and we can see our `async` has one worker, and the `scheduler`
has none, and that's expected because we didn't configure the `scheduler`
to run at any time. Down here gives you a little snapshot of the last 24
hours of different statistics about your messages, and here shows the last
15 messages, and of course we have none processed yet. All these little
widgets on the dashboard, they refresh every five seconds, as you can see
by the little spinner. 

To get started, at the top here, let's go to `Schedule`, and so here we
can see our single scheduled message, and that's going to be our `run
command` message that's set to run the command `app send booking
reminders`, and we can see it's a cron expression, and it runs every day
at 2.11 a.m., and it's had zero runs. So let's trigger this manually, so
we can just run it, and when you trigger you have to choose which of your
`async` transports you want to use, so we'll use `async`. All right, and
now if we jump back to dashboard, we can see that it ran, so here's our
recently processed message, and then if we click details here, we can see
some more information about it. 

So we can see that, you know, it was time in queue was 100 milliseconds,
time to handle 58 milliseconds, and then the dispatched received that
finish that date times, and we can see it added a bunch of tags. These are
kind of, there's a way in the bundle to add your own tags, but these are
kind of the default ones that are added for a `schedule`, so we can see
it's on `schedule`, it's tagged the `schedule`, and it's a manual run, and
it's our `schedule default`, and this is the specific ID for this
scheduled task, so it really makes it easy to filter on these, which we'll
show in a little bit, and then here, most importantly, is the output, so
we can see we had zero bookings sent. 

So let's actually run this `schedule task` again, but with a booking that
we expect to be sent. So how we can do that is jump over to our terminal,
and let's reload our fixtures, so we know there's an email that's ready to
be sent, so `symfony console doctrine fixtures load`. Okay, and if we jump
back to the dashboard, this has been cleared out because we reloaded our
fixtures, and the history of our messages is that `doctrine entity`, so
that's expected. So now if we jump over to `schedule`, and trigger back
again on our `async` transport, and then jump back to the dashboard, okay,
we can see now we have two commands that run. One is the `run command
message`, what we saw before, and then if we look at the details here, we
can see that one booking reminder was sent, and then the second one is
this `send email message` to actually send the email because of that one
that was sent, and we can see it was automatically tagged with the tag we
used for that email message `booking reminder`. So if we check out these
details, we can just get some more information about the headers that were
used to be sent, and the same that you saw before. 

Okay, so one last thing. First of all, there also is the transport, so you
can view the transports if you have queued messages on them. These are
both empty, so it's good. The neat thing is about the `fail transport`, is
when there's messages here, so after your messages fail, you have the
option to remove or retry right from the UI. 

So if we go to history, this is just kind of a more advanced way to search
messages, so we can switch the period to a bunch of different variables
here, to a bunch of different options, and then you can choose the
transport that we want to filter on, the status, success or failed,
whether to include the schedule or not, whether to include scheduled tasks
or not, and then the message type. So you can see filter on that. And then
this last one, or this other one, statistics, that this just gives you per
message statistics to review. 

Now, in `schedule`, in this history tab, you can imagine if your app runs
a lot of messages and schedules and stuff, this is going to pile up really
fast. So the bundle has an option to purge old messages, and what you can
do is you can just schedule these commands. So if we go to the docs for
the bundle and scroll down to `messenger monitor purge command`, so what
we want to do is copy this and then back in our IDE. 

So we're going to go to our `scheduler` and then `main schedule`, and down
here we're going to add a new task, and then this will be
`RecurringMessage::cron`, and we're going to run this at `midnight` every
night, and this will be a new `RunCommandMessage`, and we'll add that
command, and then we're going to add another option here, which is
`exclude schedules`, and what this does is it purges everything except for
tasks that were triggered by a schedule, so we're going to have those on a
separate command. 

So if we jump back to our browser, there's this `monitor schedule
messenger monitor schedule purge command`, so we'll just copy this one,
and we'll use the defaults, and then we'll add another task,
`RecurringMessage::cron`, we'll also run this one at `midnight`, and then
`new RunCommandMessage`. 

Okay, so the reason why there's two separate commands is some of your
scheduled tasks might run like once a month or once every two months or
even once every year or something, so having them on a separate purge
cycle, the `scheduler` specific one, actually what it does is instead of
deleting after a certain amount of days, it deletes ones older than a
specific amount, so by default I believe it keeps the last 30 or
something, and then up here this deletes all the other messages that
aren't considered `scheduled tasks` after 30 days or that are older than
30 days, so it's a slight distinction, but I think it allows you to better
get the history of your `scheduled tasks` regardless of their frequency. 

So let's just jump back to the `messenger monitor bundle`, UI, and click
schedule, and there we go, we can see here are two new commands that we've
added and they've never been run, and we can see here that we actually do
have the last run date is kept for that one that we ran manually. 

Okay, so I hope this bundle can help you out. Check out the docs for
documentation and all the other features of it, but that's kind of a basic
run through of this bundle, and happy monitoring.

