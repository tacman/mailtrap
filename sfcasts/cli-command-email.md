# Email from CLI Command

We've done the prep work for our reminder email feature. Now, let's actually
create and send the emails!

First, in `templates/email`, the new email template will be super similar to
`booking_confirmation.html.twig`. So, copy that file and name it `booking_reminder.html.twig`.
Inside, I don't want to spend too much time on this, so just change the
accent title to say "Coming soon!". Good enough.

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

Let's check it out! Open `src/Command/SendBookingRemindersCommand.php`. First,
change the command description to "Send booking reminder emails". Then, inject the
following services into the constructor: we need to find the bookings reminders
need to be sent for, so `private BookingRepository $bookingRepo`. We'll need to
update the reminder flag on bookings, so `private EntityManagerInterface $em`. And, of
course, we need to send the email, so `private MailerInterface $mailer`.

We don't need any arguments or options for this command, so remove the `configure()`
method entirely.

Clear out the guts of `execute()`. Start by adding a nice title to the output:
`$io->title('Sending booking reminders')`. Then, grab the bookings that need
reminders sent, with `$bookings = $this->bookingRepo->findBookingsToRemind()`.

I want to show a progress bar for iterating over these bookings. `$io` has a trick up
its sleeve. Write `foreach ($io->progressIterate($bookings) as $booking)`. This handles
all the boring progress bar output logic for us! Inside, we need to create a new
email. This will be super similar to the one we created in `TripController`, so copy
that, including these headers, and paste it here.

We need to adjust this a bit. Remove the attachment. The subject: replace
"Confirmation" with "Reminder". Above, add these variables for convenience:
`$customer = $booking->getCustomer()` and `$trip = $booking->getTrip()`. Down here,
we can keep the same metadata, but change the tag to `booking_reminder`. This will
help us better distinguish these emails in Mailtrap.

Oh, and of course, we need to change the template to `booking_reminder.html.twig`.

Still in the loop, send the email with `$this->mailer->send($email)` and mark
the booking as having the reminder sent with
`$booking->setReminderSentAt(new \DateTimeImmutable('now'))`.

Perfect! Outside the loop, call `$this->em->flush()` to save the changes to the database.
Finally, add a success message:
`$io->success(sprintf('Sent %d booking reminders', count($bookings)))`.

Let's see if it works, pop over to your terminal. To be sure we have a booking that
needs a reminder sent, reload the fixtures with:

```terminal
symfony console doctrine:fixture:load
```

Now, run our new command!

```terminal
symfony console app:send-booking-reminders
```

Nice, 1 reminder sent! Here's our title, a cool progress bar and the success message.
Before we check Mailtrap, run the command again:

```terminal-silent
symfony console app:send-booking-reminders
```

"Sent 0 booking reminders". Perfect! Our logic to mark bookings as having reminders
sent works!

Now check Mailtrap... here's our reminder email! As expected, it looks super similar
to our confirmation email but, it says "Coming soon!" here - it's using the new
template.

When using "Mailtrap Testing", Mailer tags and metadata are not converted to Mailtrap
categories and custom variables like they are when sent in production. You can still
confirm they are being sent though! Click this "Tech Info" tab and scroll down a bit.
When Mailer doesn't know how to convert tags and metadata, it adds them as these generic
custom headers: `X-Tag` and `X-Metadata`.

Sure enough, `X-Tag` is `booking_reminder`. Awesome, that's what we expect too!

Ok, new feature? Check! Test for the new feature? Let's do that next!
