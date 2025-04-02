# Demoing our Webhook & Remote Event Setup

Time to test-drive the Mailtrap webhook!

First, we need to switch our development environment to send in production again.
In `.env.local`, switch to your production Mailtrap `MAILER_DSN` and in
`config/services.yaml`, make sure the `global_from_email`'s domain is the one
you configured with Mailtrap.

Over in Mailtrap, go to "Settings" > "Webhooks" and click "Create New Webhook".
First thing we need is a Webhook URL. Hmm, this needs to be `/webhook/mailtrap`
but it needs to be an absolute URL. In production, this wouldn't be a problem:
it would be your production domain. In development, it's a bit trickier. We
can't just use the URL the Symfony CLI server gives us...

Somehow we need to *expose* our local Symfony server to the public. And there's a neat
tool that does exactly this: [ngrok](https://ngrok.com/). Create a free account, log in, and follow the
instructions to configure the ngrok CLI client.

Over in the terminal, restart with Symfony webserver:

```terminal
symfony server:stop
```

Oh, it isn't running. Start it with:

```terminal
symfony serve -d
```

This is the URL we need to expose, copy it and run:

```terminal
ngrok http <paste-url>
```

Paste the URL, and hit enter. Wormhole open!

This crazy looking "Forwarding" URL is the public URL. Copy and paste it into
your browser. This warning just lets you know you're running through a tunnel.
Click "Visit Site" to see your app. Cool!

Back in Mailtrap, paste this URL and add `/webhook/mailtrap` to the end. For
"Select Stream", choose "Transactional". For "Select Domain", choose your
configured Mailtrap domain. Go nuts and select *all* events, then "Save".

Go back into the new webhook and click "Run Test".

> Webhook URL test completed successfully

That's a good sign!

Remember in our `EmailEventConsumer`, we're just dumping the event? Since hitting
the webhook happens behind the scenes, we can't see the dump... or can we? In
a new terminal run:

```terminal
symfony console server:dump
```

This hooks into our app and any dumps will be output here live. Clever!

In your browser, book a trip, remember to use a real email address (but not mine!)

Moment of truth! Back in the terminal running the dump server, wait a bit...
Alright! We have a dump! Scroll up a bit... This is a `MailerDeliveryEvent` for
`delivered`. We see the internal ID Mailtrap assigned, the raw payload, date,
recipient email, even our custom metadata and tag.

Let's try an engagement event! In your email client, open the email.

Back in the dump server terminal, wait a bit... and boom! Another event! This
time, it's a `MailerEngagementEvent` for `open`. This is cool!

Alright space cadets, that's it for this course! We managed to covere almost all
of Symfony Mailer features without SPAM'ing our users. Win!

'Til next time, happy coding!
