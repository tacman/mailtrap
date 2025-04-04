# Email Factory Service

Our app sends two emails: in `SendBookingRemindersCommand`, and
`TripController::show()`. There is... a lot of duplication here.
It hurts my eyes! But no worries! We can reorganize this into an *email factory*
service. And because we have tests covering both emails,
we can refactor and be confident that we haven't broken anything. I can't say it
enough: I love tests!

## `BookingEmailFactory`

Start by creating a new class: `BookingEmailFactory` in the `App\Email` namespace.
Add a constructor, copy the `$termsPath` argument from `TripController::show()`,
paste it here, and make it a private property:

[[[ code('11961225d5') ]]]

Now, stub out two *factory* methods: `public function createBookingConfirmation()`,
which will accept `Booking $booking`, and return `TemplatedEmail`. Then,
`public function createBookingReminder(Booking $booking)` also returning a `TemplatedEmail`:

[[[ code('62556c35ed') ]]]

Create a method to house that darn duplication: `private function createEmail()`,
with arguments `Booking $booking` and `string $tag` that returns a `TemplatedEmail`:

[[[ code('0d542d17dc') ]]]

Jump to `TripController::show()`, copy *all* the email creation code, and paste it
here. Up top, we need two variables: `$customer = $booking->getCustomer()` and
`$trip = $booking->getTrip()`. Remove `attachFromPath()`, `subject()`, and
`htmlTemplate()`. In this `TagHeader`, use the passed `$tag` variable. We can leave the
metadata the same. Finally, return the `$email`:

[[[ code('aedc8d0d37') ]]]

With our shared logic in place, use it in `createBookingConfirmation()`. Write
`return $this->createEmail()`, passing the `$booking` variable and `booking` for
the tag. Now, `->subject()`, copy this from `TripController::show()`, changing the `$trip`
variable to `$booking->getTrip()`. Finally, `->htmlTemplate('email/booking_confirmation.html.twig')`:

[[[ code('0ddbf27985') ]]]

For `createBookingReminder()`, copy the insides of `createBookingConfirmation()` and
paste here. Change the tag to `booking_reminder`, the subject to `Booking Reminder`,
and the template to `email/booking_reminder.html.twig`:

[[[ code('0ddbf27985') ]]]

## The Refactor

Now the fun part! *Using* our factory and *removing* a whole wack of code!

In `TripController::show()`, instead of injecting `$termsPath`, inject
`BookingEmailFactory $emailFactory`:

[[[ code('ff3a7c2d7d') ]]]

Delete all the email creation code and
inside `$mailer->send()`, write `$emailFactory->createBookingConfirmation($booking)`:

[[[ code('a2987df642') ]]]

Over in `SendBookingRemindersCommand`, again, remove all the email creation code. Up
in the constructor, autowire `private BookingEmailFactory $emailFactory`:

[[[ code('aed5fb9865') ]]]

Down here,
inside `$this->mailer->send()`, write `$this->emailFactory->createBookingReminder($booking)`:

[[[ code('7cec573aac') ]]]

## Test It

Oh yeah, that felt good! But did we break anything? We Canadians are known for being
a bit wild. Check by running the tests:

```terminal
bin/phpunit
```

Uh oh, a failure! Good thing we have these tests, eh?

The failure comes from `BookingTest`:

> Message does not include file with filename [Terms of Service.pdf].

## Fix It

Easy fix! During our refactor, I forgot to attach the
thrilling terms of service PDF to the booking confirmation email. And our
customers depend on that. Find
`BookingEmailFactory::createBookingConfirmation()`, and add
`->attachFromPath($this->termsPath, 'Terms of Service.pdf')`:

[[[ code('27928d071f') ]]]

Re-run the tests:

```terminal-silent
bin/phpunit
```

Passing! Successful refactor? Check!

Next, let's switch gears a bit and dive into *two* new Symfony components
to consume the email webhook *events* from Mailtrap.
