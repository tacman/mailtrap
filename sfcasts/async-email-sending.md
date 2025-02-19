# Async Sending with Messenger

Currently, when we send this email in our controller, it's sent right away -
*synchronously*. This means, before sending the response back to the user,
we're connecting to our mailer transport and sending the email. This makes
the request slower, and if there's a network issue, the email could fail and
throw a 500 error to the user - gross!

I want to send our emails *asynchronously* instead. This means that, during the
request, the email will be sent to a queue to be processed later. Symfony Messenger
is perfect for this! And we get the following benefits: faster responses for the user,
automatic retries if the email fails, and the ability to flag emails that fail too
many times for manual review by an admin.

Let's install messenger! In your terminal, run:

```terminal
composer require messenger
```

Like Mailer, Messenger has the concept of a transport - this is where the messages are
queued. They also provide bridge packages for the different options. We'll use the
Doctrine transport as it's easiest to get started with:

```terminal
composer require symfony/doctrine-messenger
```

Back in our IDE, the recipe added this `MESSENGER_TRANSPORT_DSN` to our `.env`
and it's defaulted to Doctrine - perfect! This transport adds a table to our database
so technically we should create a migration for this. But... we're going to cheat a bit
and have it automatically create the table if it doesn't exist. To allow this, set this
`auto_setup` option to `1`.

The recipe also created this `config/packages/messenger.yaml` file. First, uncomment
the `failure_transport` line. This enables the manual failure review system I mentioned
earlier. Then, uncomment the `async` line under `transports` - this enables the transport
configured with `MESSENGER_TRANSPORT_DSN` and names it `async`. It's not obvious here,
because it's the default, but failed messages are retried 3 times, adding increasing
time between each retry. If a message still fails after 3 attempts, it's sent to the
`failure_transport`, called `failed`, so uncomment this transport too.

This `routing` section is where we tell Symfony which messages should be sent to what
transport. Mailer provides a message for sending emails so write:
`Symfony\Component\Mailer\Messenger\SendEmailMessage` and send it to the `async` transport.

That's it! Symfony Messenger and Mailer dock together beautifully so there's nothing
we need to change in our code.

Let's test this out! Back in our app... book a trip. Remember, we're back to using
Mailtrap testing so we can use any email. Right away, I can tell this request was
faster.

Open the profiler for the last request and check out the "Emails" section. This looks
normal, but notice the *Status* is "Queued". It was sent to our messenger transport, not
our mailer transport. We have this new "Messages" section. Here, we can see our
`SendEmailMessage` that contains our `TemplatedEmail` object.

If we jump over to Mailtrap and refresh... nothing yet. We need to process our queue.

Jump back to your terminal and run:

```terminal
symfony console messenger:consume async -vv
```

This processes our `async` transport (the `-vv` just adds more output so we can see
what's happening). Right away, we see some activity here. The message was received and
handled successfully - this should have actually sent the email.

So check Mailtrap... it's already here! Looks correct... but... click one of our links.

What the heck? Check out the URL: this isn't our dev server. We'll fix this next!
