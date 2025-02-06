# Global From (and Fun) with Email Events

I bet that most, if not every email your app sends will
be *from* the same email address, something clever like
`hal9000@universal-travel.com` or the tried-and-true but sleepier
`info@universal-travel.com`.

Because every email will have the same *from* address, there's
no point to set it in every email. Instead, let's set it globally.
Oddly, there isn't any tiny config option for this. But that's
great for us: it gives us a chance to learn about events! Very powerful,
very nerdy.

Before an email is sent, Mailer dispatches a `MessageEvent`.

To listen to this, find your terminal and run:

```terminal
symfony console make:listener
```

Call it `GlobalFromEmailListener`. The gives us a list of events we
can listen to. We want the first one: `MessageEvent`.
Start typing `Symfony` and it's autocompleted for us. Hit enter.

Listener created!

To be extra cool, let's set our global *from* address as a parameter. In `config/services.yaml`,
under `parameters`, add a new one: `global_from_email`. This will be a string,
but check this out: set it to `Universal Travel `, then in angle brackets, put the email:
`<info@universal-travel.com>`. When Symfony Mailer sees a string that looks like this as an
email address, it'll create the proper `Address` object with both a name and email set.
Sweet!

Open the new class `src/EventListener/GlobalFromEmailListener.php`.
Add a constructor with a `private string $fromEmail` argument and an `#[Autowire]`
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

Time to test this! In our app, book a trip and check Mailtrap for the email. Drumroll...
the `from` is set correctly! Our listener works! I never doubted us.

One more detail to make this completely airtight (like most of our ships).

Imagine a contact form
where the user fills their name, email, and a message. This fires off an email with
these details to your support team. In their email clients, it'd be nice if, when
they hit reply, it goes to the email from the form - not your "global from".

You might think that you should set the `from` address to the user's email.
But that won't work, as we're not authorized to send emails on behalf of
that user. More on email security soon.

Fortunately, there's a special email header called `Reply-To` for just this scenario.
When building your email, set it with `->replyTo()` and pass the user's email address.

Strap in because the booster tanks are full and ready for launch!
Time to send *real* emails in production! That's next.
