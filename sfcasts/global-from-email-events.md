# Global From with Email Events

It's likely that most, if not all the emails your app sends will
be *from* the same email address. In our case, `info@universal-travel.com`.

Let's prevent the need to add the same *from* address to every email by setting it
globally. We can do this with a Mailer Event, specifically, the `MessageEvent`.
This is dispatched before an email is sent, giving us a chance to modify it.

To listen to an event, we need an event listener, and there's a maker for that! In
your terminal, run:

```terminal
symfony console make:listener
```

Call it `GlobalFromEmailListener`. The command is giving us a list of events we
can listen to. The first one is what we want: `Symfony\Component\Mailer\Event\MessageEvent`.
Start typing `Symfony` and it's autocompleted it for us. Hit enter.

Listener created!

I want to configure our global *from* address as a parameter. In `config/services.yaml`,
under `parameters`, add a new parameter called `global_from_email`. This will be a string,
but check this out: set it to `Universal Travel `, then in angle brackets, put the email:
`<info@universal-travel.com>`. When Symfony sets a string that looks like this as an
email address, it'll create the proper `Address` object with both a name and email set.
Sweet!

Find our new listener class in `src/EventListener/GlobalFromEmailListener.php`. First,
add a constructor with a `private string $fromEmail` argument and an `#[Autowire]`
attribute with our parameter name: `%global_from_email%`.

Down here, the `#[AsEventListener]` attribute is what *marks* this method as an event
listener. We can actually remove this `event` argument - it'll be inferred from the
method argument's type-hint: `MessageEvent`.

Inside, first grab the message from the event: `$message = $event->getMessage()`. Jump
into the `getMessage()` method to see what it returns. `RawMessage`... jump into this
and look at what classes extend it. `TemplatedEmail`! Perfect!

Back in our listener, write `if (!$message instanceof TemplatedEmail)`, and inside, `return;`.
This will likely never be the case, but it's good practice to double-check. Plus, it
helps our IDE know that `$message` is a `TemplatedEmail` now.

It's possible that an email might still set its own `from` address. In this case,
we don't want to override it. So, add a guard clause: `if ($message->getFrom())`, `return;`.

Now, we can set the global `from`: `$message->from($this->fromEmail)`. Perfect!

Back in `TripController::show()`, remove the `->from()` for the email.

Time to test this! In our app, book a trip, and check Mailtrap for the email. Here it is...
and the `from` is set correctly! Our listener is working!

A quick aside to talk about a scenario you might encounter. Consider a contact form
where the user fills their name, email, and a message. This fires off an email with
these details to your support team. In their email clients, it'd be nice if, when
they hit reply, it goes to the email from the form - not your "global from".

You might think that you should set the `from` address to the user's email. We'll see
why shortly, but this won't work. We're not authorized to send emails on behalf of
that user. So, are you forced to make your support team copy-paste the email address?

Nope! There's a special email header called `Reply-To` for just this scenario.
When building your email, use `->replyTo()` to set it.

Ok! We're ready to send emails in production! That's next.
