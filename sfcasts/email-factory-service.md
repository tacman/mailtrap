# Email Factory Service

Our app sends two emails: one in our `SendBookingRemindersCommand`, and the
other in `TripController::show()`. There is... a lot of duplication here.
It hurts my eyes. I want to refactor this into an *email factory* service
and reduce the duplication. Because we have tests covering both emails,
we can refactor and be sure we haven't broken anything. I can't say it
enough: I love tests!

Start by creating a new class: `BookingEmailFactory` in the `App\Email` namespace.
Add a constructor, copy the `$termsPath` argument from `TripController::show()`,
paste it here, and make it a private property.

Now, stub our two *factory* methods: `public function createBookingConfirmation()`,
which will accept `Booking $booking`, and return `TemplatedEmail`. Then,
`public function createBookingReminder(Booking $booking)`, returning `TemplatedEmail`.

Create a private method to house the duplication: `private function createEmail()`,
with arguments `Booking $booking` and `string $tag`. Return `TemplatedEmail`.
Jump to `TripController::show()`, copy *all* the email creation code, and paste it
here. Up top, we need two variables: `$customer = $booking->getCustomer()` and
`$trip = $booking->getTrip()`. Remove `attachFromPath()`, `subject()`, and
`htmlTemplate()`. In this `TagHeader`, use the passed `$tag` variable. We can leave the
metadata the same. Finally, return the `$email`.

With our shared logic in place, use it in `createBookingConfirmation()`. Write
`return $this->createEmail()`, passing the `$booking` variable and `booking` for
the tag. Now, `->subject()`, copy this from `TripController::show()`, changing the `$trip`
variable to `$booking->getTrip()`. Now, `->htmlTemplate('email/booking_confirmation.html.twig')`.

For `createBookingReminder()`, copy the insides of `createBookingConfirmation()` and
paste it here. Change the tag to `booking_reminder`, the subject to `Booking Reminder`,
and the template to `email/booking_reminder.html.twig`.

Now the fun part! *Using* our factory and *removing* a whole wack of code!

In `TripController::show()`, instead of injecting `$termsPath`, inject
`BookingEmailFactory $emailFactory`. Delete all the email creation code and
inside `$mailer->send()`, write `$emailFactory->createBookingConfirmation($booking)`.

Now, in `SendBookingRemindersCommand`, again, remove all the email creation code. Up
in the constructor, inject `private BookingEmailFactory $emailFactory`. Down here,
inside `$this->mailer->send()`, write `$this->emailFactory->createBookingReminder($booking)`.

Oh yeah, that felt good! But did we break anything? Check by running the tests:

```terminal
bin/phpunit
```

Uh oh, a failure. Good thing we have these tests, eh?

The failure originated in our `BookingTest` and the failure message is:

> Message does not include file with filename [Terms of Service.pdf].

This is an easy fix! During our refactor, we forgot to attach the
terms of service to our booking confirmation email. Jump back to
`BookingEmailFactory::createBookingConfirmation()`, and add
`->attachFromPath($this->termsPath, 'Terms of Service.pdf')`.

Re-run the tests:

```terminal-silent
bin/phpunit
```

Passing! Successful refactor? Check!

Next, we'll switch gears a bit and utilize two new Symfony components
to consume the email *events* Mailtrap triggers on their end.
