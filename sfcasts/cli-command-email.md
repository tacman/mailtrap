# Email from CLI Command

We've done the prep work for our reminder email feature. Now, let's actually
create and send the emails!

In `templates/email`, the new email template will be super similar to
`booking_confirmation.html.twig`. Copy that file and name it `booking_reminder.html.twig`.
Inside, I don't want to spend too much time on this, so just change the
accent title to say "Coming soon!". Ship it! Accidental space pun!

The logic to send the emails needs to be something we can schedule to run every hour or
every day. Perfect job for a CLI command! At your terminal, run:

```
symfony make:command
```

Bah!

```terminal
symfony console make:command
```

Call it: `app:send-booking-reminders`.

Go check it out! `src/Command/SendBookingRemindersCommand.php`. Change the description to
"Send booking reminder emails". 

In the constructor, autowire & set properties for `BookingRepository`, `EntityManagerInterface`
and `MailerInterface`.

This command doesn't need any arguments or options, so remove the `configure()`
method entirely.

Clear out the guts of `execute()`. Start by adding a nice:
`$io->title('Sending booking reminders')`. Then, grab the bookings that need
reminders sent, with `$bookings = $this->bookingRepo->findBookingsToRemind()`.

To be the absolute best, let's show a progress bar as we loop over the bookings.
The `$io` object has a trick for this.
Write `foreach ($io->progressIterate($bookings) as $booking)`. This handles
all the boring progress bar logic for us! Inside, we need to create a new
email. In `TripController`, copy that email - including these headers, and
paste it here.

But we need to adjust this a bit: remove the attachment. And for the subject: replace
"Confirmation" with "Reminder". Above, add some variables for convenience:
`$customer = $booking->getCustomer()` and `$trip = $booking->getTrip()`. Down here,
keep the same metadata, but change the tag to `booking_reminder`. This will
help us better distinguish these emails in Mailtrap.

Oh, and of course, change the template to `booking_reminder.html.twig`.

Still in the loop, send the email with `$this->mailer->send($email)` and mark
the booking as having the reminder sent with
`$booking->setReminderSentAt(new \DateTimeImmutable('now'))`.

Perfect! Outside the loop, call `$this->em->flush()` to save the changes to the database.
Finally, celebrate with
`$io->success(sprintf('Sent %d booking reminders', count($bookings)))`.

Testing time! Pop over to your terminal. To be sure we have a booking that
needs a reminder sent, reload the fixtures with:

```terminal
symfony console doctrine:fixture:load
```

Now, run our new command!

```terminal
symfony console app:send-booking-reminders
```

Nice, 1 reminder sent! And the output will impress our colleagues!
Before we check Mailtrap, run the command again:

```terminal-silent
symfony console app:send-booking-reminders
```

"Sent 0 booking reminders". Perfect! Our logic to mark bookings as having reminders
sent works!

Now check Mailtrap... here it is! As expected, it looks super similar
to our confirmation email but, it says "Coming soon!" here: it's using the new
template.

When using "Mailtrap Testing", Mailer tags and metadata are not converted to Mailtrap
categories and custom variables like they are when sent in production. But you can still
make sure they're being sent! Click this "Tech Info" tab and scroll down a bit.
When Mailer doesn't know how to convert tags and metadata, it adds them as these generic
custom headers: `X-Tag` and `X-Metadata`.

Sure enough, `X-Tag` is `booking_reminder`. Awesome, that's what we expect too!

Ok, new feature? Check! Test for the new feature? That's next!
