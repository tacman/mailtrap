# Webhook for Email Events

In MailTrap, when we're sending emails in production, remember that we can audit
sent emails and see the different events: Sent, Delivered, Opened, etc.
Mailtrap allows us to configure a webhook to send these events to our app.

To do this, we need to install two new Symfony components:

```terminal
composer require webhook remote-event
```

The webhook component provides a single endpoint to send all webhooks to.
It parses the raw payload and dispatches a *remote event* to a *consumer*.
You can think of remote events as similar to Symfony events. Instead of
your app dispatching an event, a third-party service does it - hence
*remote event*. Instead of *event listeners*, we have *remote event consumers*.

Run:

```terminal
git status
```

To see what the recipe configured. `config/routes/webhook.yaml` - that
adds the webhook controller. Check out the route with:

```terminal
symfony console debug:route webhook
```

Chose the first one. The path is `/webhook/{type}`. So now we need to
configure a *type*.

As 3rd party webhooks can have wildly different payloads, we typically
need to create our own parsers and remote events. Since email events are
pretty standard, Symfony provides some out-of-the-box remote events for
these. Some mailer bridges, including the Mailtrap bridge we're using,
provide the proper parsers for each service's webhooks. We just need
to configure it.

In `config/packages`, create a `webhook.yaml` file. Add: `framework`,
`webhook`, `routing`, `mailtrap` (this is the *type* used in the URL),
and then `service`. To find the Mailtrap specific parser id, pop over to the
[Symfony Webhook documentation](https://symfony.com/doc/current/webhook.html).
Find the service id for the Mailtrap parser. Copy and paste that here.

Now we need a consumer. Create a new class called `EmailEventConsumer`
in the `App\Webhook` namespace. This needs to implement
`ConsumerInterface` from `RemoteEvent`. Implement the `consume()` method.
To let Symfony know what webhook *type* we want this to consume, add
the `#[AsRemoteEventConsumer]` attribute with `mailtrap` as the type.

Above `consume()`, add a docblock to help our IDE:
`@param MailerDeliveryEvent|MailerEngagementEvent $event`. These are the
generic mailer remote events Symfony provides. Inside the method,
write `$event->` to see the methods available.

In a real app, this would be where you'd do something with these events like
save them to the database or notify an admin if an email bounced. For our
purposes, just write `dump($event)`.

One last thing: the webhook controller sends the remote event to the consumer
via Symfony Messenger so we can make this asynchronous. In
`config/packages/messenger.yaml`, under `routing`, add
`Symfony\Component\RemoteEvent\Messenger\ConsumeRemoteEventMessage` and
send to our `async` transport.

Ok! We're ready to demo this webhook. That's next!
