# Sending our First Email

Let's take a trip! "Visit Krypton", Hopefully it hasn't
been destroyed yet! Without bothering to check, let's book it!
I'll use name: "Kevin", email: "kevin@example.com" and just any date in the
future. Hit "Book Trip".

This is the "booking details" page. Note the URL: it has a unique token
specific to this booking. If a user needs to come back here later, currently, they
need to bookmark this page or Slack themselves the URL if they're like me.
Lame! Let's send them a confirmation email that includes a link to this page.

I want this to happen after the booking is first saved. Open `TripController`
and find the `show()` method. This makes the booking:
if the form is valid, create or fetch a customer and create a booking
for this customer and trip. Then we redirect to the booking details page.
Delightfully boring so far, just how I like my code, and weekends.

I want to send an email after the booking is created. Give yourself some room
by moving each argument to its own line. Then, add `MailerInterface $mailer` to get
the main service for sending emails.
After `flush()`, which inserts the booking into the database, create a new email object: `$email = new Email()` (the one
from `Symfony\Component\Mime`). Wrap it in parentheses so we can chain methods. So what
does every email need? A `from` email address: `->from()` how about `info@univeral-travel.com`.
A `to` email address: `->to($customer->getEmail())`.
Now, the `subject`: `->subject('Booking Confirmation')`. And finally, the email
needs a body: `->text('Your booking has been confirmed')` - good enough for now.

Finish with `$mailer->send($email)`. Let's test this out!

Back in our app, go back to the homepage and choose a trip. For the name, use "Steve",
email, "steve@minecraft.com", any date in the future, and book the trip.

Ok... this page looks exactly the same as before. Was an email sent? Nothing in the
web debug toolbar seems to indicate this...

The email was *actually* sent on the previous request - the form submit. That controller then
redirected us to this page. But the web debug toolbar gives us a shortcut to access the profiler
for the previous request: hover over `200` and click the profiler link to get there.

Check out the sidebar - we have a new "Emails" tab! And it shows 1 email was sent. We did it!
Click it, and here's our email! The from, to, subject, and body are all what we expect.

Remember, we're using the `null` mailer transport, so this email wasn't actually sent, but it's
super cool we can still preview it in the profiler!

Though ... I think we both know this email... is... pretty crappy. It doesn't give any of the useful info!
No URL to the booking details page, no destination, no date, no nothing! It's so useless,
I'm glad the `null` transport is just throwing it out the space window.

Let's fix that next!
