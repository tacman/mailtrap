# Async & Retryable Sending with Messenger

When we send this email, it's sent right away -
*synchronously*. This means that our the user sees a delay while we connect to the
mailer transport to send the email. And if there's a network issue where the email
fails, the user will see a 500 error: not exactly inspiring confidence in a company
that's going to strap you to a rocket.

Instead, let's send our emails *asynchronously*. This means that, during the
request, the email will be sent to a queue to be processed later. Symfony Messenger
is perfect for this! And we get the following benefits: faster responses for the user,
automatic retries if the email fails, and the ability to flag emails for manual review
if they fail too many times.

Let's install messenger! At your terminal, run:

```terminal
composer require messenger
```

Like Mailer, Messenger has the concept of a transport: this is where the messages are
sent to be queued. We'll use the
Doctrine transport as it's easiest to set up.

```terminal
composer require symfony/doctrine-messenger
```

Back in our IDE, the recipe added this `MESSENGER_TRANSPORT_DSN` to our `.env`
and it defaulted to Doctrine - perfect! This transport adds a table to our database
so *technically* we should create a migration for this. But... we're going to cheat a bit
and have it automatically create the table if it doesn't exist. To allow this, set
`auto_setup` to `1`.

The recipe also created this `config/packages/messenger.yaml` file. Uncomment
the `failure_transport` line. This enables the manual failure review system I mentioned
earlier. Then, uncomment the `async` line under `transports` - this enables the transport
configured with `MESSENGER_TRANSPORT_DSN` and names it `async`. It's not obvious here,
but failed messages are retried 3 times, with an increasing delay
between each attempt. If a message still fails after 3 attempts, it's sent to the
`failure_transport`, called `failed`, so uncomment this transport too.

The `routing` section is where we tell Symfony which messages should be sent to which
transport. Mailer uses a specific message class for sending emails. So send
`Symfony\Component\Mailer\Messenger\SendEmailMessage` to the `async` transport.

That's it! Symfony Messenger and Mailer dock together beautifully so there's nothing
we need to change in our code.

Let's test this! Back in our app... book a trip. We're back to using
Mailtrap's testing transport so we can use any email. Now watch how much faster this
processes.

Boom!

Open the profiler for the last request and check out the "Emails" section. This looks
normal, but notice the *Status* is "Queued". It was sent to our messenger transport, not
our mailer transport. We have this new "Messages" section. Here, we can see the
`SendEmailMessage` that contains our `TemplatedEmail` object.

Jump over to Mailtrap and refresh... nothing yet. Of course! We need to process our queue.

Spin back to your terminal and run:

```terminal
symfony console messenger:consume async -vv
```

This processes our `async` transport (the `-vv` just adds more output so we can see
what's happening). Righteous! The message was received and
handled successfully. Meaning: this should have *actually* sent the email.

Go check Mailtrap... it's already here! Looks correct... but... click one of our links.

What the heck? Check out the URL: that's the wrong domain! Boo.
Let's find out which part of our email rocket ship has caused this
and fix it next!
