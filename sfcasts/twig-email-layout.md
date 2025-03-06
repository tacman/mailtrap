# Email Twig Layout

New feature time! I want to send a reminder email to customers 1 week before their booked
trip.

First though, we have a little problem with our Symfony CLI worker. Open
`.symfony.local.yaml`. Our `messenger` worker is watching the `vendor`
directory for changes. At least on some systems, there's just too many
files in here to monitor. Some weird things seem to happen. To fix, just
remove `vendor`. Since we changed the config, jump to your terminal and
restart the webserver:

```terminal
symfony server:stop
```

And:

```terminal
symfony serve -d
```

Our new booking reminder email will have a template very similar to the booking
confirmation one. To reduce duplication, and to ensure our emails have a consistent
look, in `templates/email/`, create a new `layout.html.twig` template for all
our emails to extend.

Copy the contents of `booking_confirmation.html.twig` and paste it here. Now, remove
the booking-confirmation-specific content and create an empty `content` block. I think
it's fine to keep our signature here.

In `booking_confirmation.html.twig`, up top here, extend this new layout and add the
`content` block. Down below, copy the email-specific content and paste it inside the
block. Remove everything else from the template.

Let's make sure the booking confirmation email still works - and we have tests for that!
Back in the terminal, run the tests with:

```terminal
bin/phpunit
```

Green! That's a good sign. Let's be doubly sure by checking it in Mailtrap. In the app,
book a trip... and check Mailtrap. Yep, that looks good.

We've made it easy to create new email templates. Now, we need to prepare for the new
feature a bit. Once an email reminder is sent, we need to somehow mark the booking so
that we don't send multiple reminders.

The `Booking` entity represents a single booking so this is the perfect place. We'll
add a new column to track whether a reminder has been sent or not.

In your terminal, run:

```
symfony make:entity Booking
```

Oops!

```terminal
symfony console make:entity Booking
```

Add a new field called `reminderSentAt`, type `datetime_immutable`, nullable? Yes.
This is a common pattern I use for these type of *flag* fields instead of a simple `boolean`.
`null` means `false` and a date means `true`. This allows you to audit when the field was
set.

Hit enter to exit the command.

In the `Booking` entity... here's our new property, and down here, the getter and setter.

Next, we need a way to find all bookings that need a reminder sent. Perfect job for the
`BookingRepository`! Add a new method called `findBookingsToRemind()`, return type: `array`.
Add a docblock to show it returns an array of Bookings.

Inside, `return $this->createQueryBuilder()`, alias `b`. Chain
`->andWhere('b.reminderSentAt IS NULL')`. We only want bookings where the reminder hasn't
been sent yet. Add `->andWhere('b.date <= :future')`, `->andWhere('b.date > :now')`
`->setParameter('future', new \DateTimeImmutable('+7 days'))` and
`->setParameter('now', new \DateTimeImmutable('now'))`. This will find bookings between now
and 7 days from now. Finally, `->getQuery()->getResult()`. Done!

We have some fixtures for development in `AppFixtures`. Down here, we create some
fake bookings. Add one that we know will trigger a reminder email to be sent:
`BookingFactory::createOne()`, inside, `'trip' => $arrakis, 'customer' => $clark` and,
this is the important part, `'date' => new \DateTimeImmutable('+6 days')`. Clearly between
now and 7 days from now.

We made changes to the structure of our database. Normally, we should be creating
a migration... but, we aren't using migrations. So, we'll just force update the schema.
In your terminal, run:

```terminal
symfony console doctrine:schema:update --force
```

Since we've updated our fixtures, reload them:

```terminal
symfony console doctrine:fixture:load
```

That all worked, great!

Next, we'll create a new reminder email and a CLI command to send them!
