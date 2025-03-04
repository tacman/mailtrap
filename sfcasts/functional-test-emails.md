# Emails Assertions in Functional Tests

Okay, testing time! If you've explored the codebase a bit, you may have noticed that
there are some tests already in our `tests/Functional` directory. Let's run these
now. Jump over to your terminal and run:

```terminal
bin/phpunit
```

Uh-oh, 1 failure. I know these were passing at the beginning of the course! We can
see the failure is in `BookingTest`, specifically, `testCreateBooking`.
The failure is "Expected redirect status code but got 500" and it originated on
line 38 of `BookingTest`. Creating a booking is where we are now sending an email
- that must have something to do with this failure!

Open `BookingTest.php`. If you've written functional tests with Symfony before, this
may look a tad different. I'm using some helper libraries. `zenstruck/foundry` gives
us this `ResetDatabase` trait which wipes the database before each test. Also, it
gives us this `Factories` trait which lets us create database fixtures
in our tests. This `HasBrowser` is from another package called `zenstruck/browser` -
it's essentially a user-friendly wrapper around Symfony's test client.

`testCreateBooking` is our actual test. First, we're creating a `Trip` in the database with
these known values. Next, some pre-assertions to ensure there are no bookings or
customers in the database. Now, we're using `->browser()` to navigate to a trip page,
fill in the booking form, and submit. We're then asserting we're redirected to a
specific booking URL and checking the page contains some expected HTML. Finally, we
are using Foundry to make some assertions about the data in our database.

Line 38 caused the failure... we're getting a 500 response code when redirecting
to this booking page. 500 status codes in tests can be frustrating because it can
be hard to track down the actual exception. Luckily, Browser allows us to *throw*
the actual exception. At the beginning of this chain, add `->throwExceptions()`.

Back in the terminal, run the tests again:

```terminal-silent
bin/phpunit
```

Now we see an exception: *Unable to find template "@images/mars.png"*. If you recall,
this looks like how we're embedding the trip images into our email. It's failing because
`mars.png` doesn't exist in `public/imgs`. For simplicity, let's adjust our test to use
an existing image. For our fixture here, change `mars` to `iss`, and down below, for
`->visit()`: `/trip/iss`.

Run the tests again!

```terminal-silent
bin/phpunit
```

Passing!

It looks like our email is being sent but let's confirm! At the end of this test,
I want to make some email assertions. Symfony has some email testing assertions
out of the box, but I like to use a library that's more user-friendly.

At your terminal, run:

```terminal
composer require --dev zenstruck/mailer-test
```

Installed and configured... back in our test, enable it by adding the `InteractsWithMailer`
trait.

Let's start simple, at the end of our test, write `$this->mailer()->assertSentEmailCount(1);`.

Quick thing to note: `.env.local`, where we put our *real* Mailtrap credentials, is *not*
looked at in the testing environment. Only `.env` and this `.env.test`, and, if you
remember, our `MAILER_DSN` is `null://null` in `.env`. That's great! We want our tests
to be fast, and not actually sending emails.

Re-run our tests!

```terminal-silent
bin/phpunit
```

Passing - 1 email is being sent! Go back and add another assertion: `->assertEmailSentTo()`.
What email are we expecting? The one we filled in the form: `bruce@wayne-enterprises.com`.
Copy and paste that. The second argument is the subject: `Booking Confirmation for Visit Mars`.

Run the tests!

```terminal-silent
bin/phpunit
```

Still passing! And notice we have 20 assertions now instead of 19.

We can go further! Instead of a string for the subject in this assertion, use a closure
with `TestEmail $email` as the argument. Inside, we can now make *loads* more assertions
on this email. Since we aren't checking the subject above anymore, add this one first:
`$email->assertSubject('Booking Confirmation for Visit Mars')`. We can chain more assertions!
Write `->assert` to see what our IDE suggests. Look at them all... Note the `assertTextContains`
and `assertHtmlContains`. You can assert on each of these separately, but, because it's
a best practice for both to contain the important details, use `assertContains()` to check
both at once. Check for `Visit Mars`.

Links are important to check so make sure the booking URL is there:
`->assertContains('/booking/'.`. Now, `BookingFactory::first()->getUid()` - this fetches
the first `Booking` entity in the database (which we know from above there is only the one),
and gets its `uid`.

Finally, check the attachment: `->assertHasFile('Terms of Service.pdf')`. If you dig into
this assertion, you can optionally check the content-type and actual file contents via
extra arguments. I'm fine just checking that the attachment exists for now.

Run our tests again!

```terminal-silent
bin/phpunit
```

Awesome, 25 assertions now!

One last thing: if you're ever having trouble figuring out why one of these email
assertions isn't passing, chain a `->dd()` and run your tests. When it hits that `dd()`,
it dumps the email to help you debug. Don't forget to remove it when you're done!

Next, I want to add a second email to our app. To avoid duplication, we'll create
a Twig email layout that both share.
