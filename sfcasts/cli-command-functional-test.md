# Test for CLI Command

The captain is tired of people running after the rocket because they show up late!
That's why we created a command to send reminder emails! Problem solved!
Now let's write a test to ensure it *keeps* working. "New feature,
new test", that's my motto!

Jump over to your terminal and run:

```terminal
symfony console make:test
```

Type? `KernelTestCase`. Name? `SendBookingRemindersCommandTest`.

In our IDE, the new class was added to `tests/`. Open
it up and move the class to a new namespace: `App\Tests\Functional\Command`,
to keep things organized.

Perfect. First, clear out the guts and add some behavior traits:
`use ResetDatabase, Factories, InteractsWithMailer`:

[[[ code('40ea62a717') ]]]

Stub out two tests:
`public function testNoRemindersSent()` with
`$this->markTestIncomplete()` and
`public function testRemindersSent()`. Also mark it incomplete:

[[[ code('f1d46840b5') ]]]

Back in the terminal run the tests with:

```terminal
bin/phpunit
```

Check it out, our original two tests are passing, the two *dots*, and these
*I's* are the new incomplete tests. I love this pattern: write test stubs
for a new feature, then make a game of removing the incompletes one-by-one
until they're all gone. *Then*, the feature is done!

Symfony has some out-of-the-box tooling for testing commands, but I like to
use a package that wraps these up into a nicer experience. Install it with:

```terminal
composer require --dev zenstruck/console-test
```

To enable this package's helpers, add a new *behavior* trait to our test:
`InteractsWithConsole`:

[[[ code('72ac7eaf58') ]]]

We're ready to knock down those I's!

The first test is easy: we want to ensure that, when there's no bookings to
remind, the command doesn't send any emails. Write
`$this->executeConsoleCommand()` and just the command name: `app:send-booking-reminders`.
Ensure the command ran successfully with `->assertSuccessful()` and
`->assertOutputContains('Sent 0 booking reminders')`:

[[[ code('5310e32269') ]]]

On to the next test! This one is more involved: we need to
create a booking that is eligible for a reminder. Create the booking fixture with
`$booking = BookingFactory::createOne()`. Pass an array with
`'trip' => TripFactory::new()`, and inside that, another array with
`'name' => 'Visit Mars'`, `'slug' => 'iss'` (to avoid the image issue).
The booking also needs a customer: `'customer' => CustomerFactory::new()`.
All we care about is the customer's email: `'email' => 'steve@minecraft.com'`.
Finally, the booking date: `'date' => new \DateTimeImmutable('+4 days')`:

[[[ code('d037db60b6') ]]]

Phew! We have a booking in the database that needs a reminder sent. This
test's setup, or *arrange* step, is done.

Add a pre-assertion to ensure this booking hasn't had a reminder sent:
`$this->assertNull($booking->getReminderSentAt())`:

[[[ code('44d45a0ee8') ]]]

Now for the *act* step:
`$this->executeConsoleCommand('app:send-booking-reminders')`
`->assertSuccessful()->assertOutputContains('Sent 1 booking reminders')`:

[[[ code('0ffcae7a07') ]]]

Onto the *assert* phase to ensure the email was sent. In `BookingTest`,
copy the email assertion and paste it here. Make a few adjustments:
the email is `steve@minecraft.com`, subject is `Booking Reminder for Visit Mars`
and this email doesn't have an attachment, so remove that assertion entirely:

[[[ code('e13847f867') ]]]

Finally, write an assertion that the command updated the booking in the database.
`$this->assertNotNull($booking->getReminderSentAt())`:

[[[ code('001aefbbaf') ]]]

Moment of truth! Run the tests:

```terminal-silent
bin/phpunit
```

All green!

I find these type of *outside-in* tests really fun and easy to write because you
don't have to worry too much about testing the inner logic and they mimic
how a user interacts with your app. It's no accident that the assertions
are focused on what the user should *see* and some high level post-interaction
checks, like checking something in the database.

Now that we have tests for both of our email sending paths, let's take a victory
lap & refactor *with confidence* to remove duplication.
