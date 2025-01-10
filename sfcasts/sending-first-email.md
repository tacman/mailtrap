# Sending our First Email

In our application, choose a trip: "Visit Krypton", hopefully it hasn't
been destroyed yet! This is the trip details page and here is the booking form. Fill
this out: I'll use name: "Kevin", email: "kevin@example.com" and just any date in the
future. Hit "Book Trip".

We're now on the "booking details" pages. Note the URL: it has a unique token that's
specific to this booking. If a user needs to come back here later, currently, they
need to remember to bookmark this page - lame! Let's send them an email with a link to this
page instead!

I want this to happen after the booking is created. Open `src/Controller/TripController.php`
and find the `show()` controller method. Here's where we handle creating the booking.
If the booking form is valid, create or fetch a customer and create the booking
using this customer and the trip. After that, we redirect to the booking details page.

I want to send an email after the booking is created. First, give ourselves some
room in the method arguments by adding them on separate lines. Then, inject
`MailerInterface $mailer` - this is the service that's used to send emails.

After this `flush()`, create a new email object: `$email = new Email()` (the one
from `Symfony\Component\Mime`). Wrap it in brackets so we can chain methods. So what
does every email need? A `from` email address: `->from()` and use `info@univeral-travel.com`.
A `to` email address: `->to()` and we can get this from the `$customer` object: `$customer->getEmail()`.
Now, the `subject`: `->subject()` and for now "Booking Confirmation". Finally, the email
needs a body: `->text()` and let's just use "Your booking has been confirmed".

Finally, send the email: `$mailer->send($email)`. Let's test this out!

Back in our app, go back to the homepage and choose a trip. For the name, use "Steve",
email, "steve@minecraft.com", choose any date in the future and book the trip.

Ok... this page looks exactly the same as before. Was an email sent? Nothing in the
web debug toolbar seems to indicate this...

The email was actually sent on the previous request - the form submit. That controller then
redirected us to this page. The web debug toolbar gives us a shortcut to access the profiler
for the previous request: hover over `200` and click the profiler link for the previous request.

Check out the sidebar - we have a new "Emails" tab that shows 1 email was sent. Sweet!
Click it, and here's our email! The from, to, subject, and body are all what we expect.

Remember, we're using the `null` mailer transport, so this email wasn't actually sent, but it's
super cool we can still preview it in the profiler!

This email... is... pretty crappy - it doesn't give any of the useful information about the booking.
Let's improve it next!
