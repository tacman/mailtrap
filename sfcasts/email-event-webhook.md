# The Webhook Component for Email Events

In Mailtrap, when we send emails in production, remember that we can check
each email: was it sent, delivered, opened, bounced (which is important!)
and more. Mailtrap lets us set a webhook URL so it can to send info about
these events to *to* us.

As a bonus, we get to discover *two* new Symfony commponents! Find your terminal
and install them:

```terminal
composer require webhook remote-event
```

The webhook component gives us a single endpoint to send all webhooks to.
It parses the data sent to us - called the payload, converts it to a *remote event*
object, and sends it to a *consumer*.
You can think of remote events as similar to Symfony events. Instead of
your app dispatching an event, a third-party service does it - hence
*remote event*. And instead of *event listeners*, we say that remote events
have *consumers*.

Run

```terminal
git status
```

to see what the recipe added: `config/routes/webhook.yaml`. Cool!
That adds the webhook controller. Check out the route with:

```terminal
symfony console debug:route webhook
```

Check the first one. The path is `/webhook/{type}`. So now we need to
configure some sort of a *type*.

3rd party webhooks - like from Mailtrap or a payment processor or a supernova
alert system - can send us *wildly* different payloads, we typically
need to create our own parsers and remote events. Since email events are
pretty standard, Symfony provides some out-of-the-box remote events for
these: `MailerDeliveryEvent` and `MailerEngagementEvent`. Some mailer bridges,
including the Mailtrap bridge we're using, provide parsers for each service's
webhook payload to create these objects. We just need to set it up.

In `config/packages/`, create a `webhook.yaml` file. Add: `framework`,
`webhook`, `routing`, `mailtrap` (this is the *type* used in the URL),
and then `service`. To figure out the Mailtrap parser service id, pop over to the
[Symfony Webhook documentation](https://symfony.com/doc/current/webhook.html).
Find the service id for the Mailtrap parser, copy it... and paste it here.

Now we need a consumer. Create a new class called `EmailEventConsumer`
in the `App\Webhook` namespace. This needs to implement
`ConsumerInterface` from `RemoteEvent`. Add the necessary `consume()` method.
To tell Symfony know which webhook *type* we want this to consume, add
the `#[AsRemoteEventConsumer]` attribute with `mailtrap`.

Above `consume()`, add a docblock to help our IDE:
`@param MailerDeliveryEvent|MailerEngagementEvent $event`. These are the
generic mailer remote events Symfony provides. Inside,
write `$event->` to see the methods available.

In a real app, this would be where you'd do something with these events like
save them to the database or notify an admin if an email bounced. Actually
if an email bounces a few times, you may want to update something to *prevent*
trying again as this can hurt your email reliability.
But for our purposes, just `dump($event)`.

One last thing: the webhook controller sends the remote event to the consumer
via Symfony Messenge inside of a message class called `ConsumeRemoteEventMessage`.

To handle this asynchronously & keep your webhook responses fast, in 
`config/packages/messenger.yaml`, under `routing`, add
`Symfony\Component\RemoteEvent\Messenger\ConsumeRemoteEventMessage` and
send it to our `async` transport.

Ok! We're ready to demo this webhook. That's next!
