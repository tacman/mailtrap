# Better Email

I think you, me, anyone that's ever received an email, can agree that our first email
stinks. It doesn't provide any value. Let's improve it!

First, we can add a name to the email. This will show up in most email
clients instead of just the email address: it just looks smoother. Wrap the `from` with
`new Address()`, the one from `Symfony\Component\Mime`. The first argument is the
email, and the second is the name - how about `Universal Travel`. We can also wrap the `to` with
`new Address()`. and pass `$customer->getName()` for the name.

For the `subject`, add the trip name: `'Booking Confirmation for ' . $trip->getName()`.

For the `text` body. We could inline all the text right here. That would get ugly,
so let's use Twig! We need a template. In `templates/`, add a new `email` directory and inside,
to be multi-line, this would be a bit of a mess. Instead, let's use Twig!
In `templates/`, add a new `email/` directory and inside, create a new file:
`booking_confirmation.txt.twig`. Twig can be used for any text format, not just `html`.
A good practice is to include the format - `.html` or `.txt` - in the filename.
But Twig doesn't care about the that - it's just to satisfy our human brains.
We'll return to this file in a second.

Back in `TripController::show()`, instead of `new Email()`, use `new TemplatedEmail()`
(the one from `Symfony\Bridge\Twig`)
`->text()` with `->textTemplate('email/booking_confirmation.txt.twig')`.
To pass variables to the template, use `->context()` with
`'customer' => $customer, 'trip' => $trip, 'booking' => $booking`.
Note, we aren't technically *rendering* the Twig template here: Mailer will do that for us
when it sends the email.

This is normal, boring Twig code. Let's render the user's first name using a cheap trick,
the trip name, the departure date, and a link to manage the booking. We need to use
absolute URLs in emails - like https://univeral-travel.com/booking - so we'll leverage
the `url()` Twig function instead of `path()`:

`{{ url('booking_show', {'uid': booking.uid}) }}`.
You might be used to using the `path()` Twig function for generating urls but this generates
End politely with, `Regards, the Universal Travel team`.

Email body done! Test it out. Back in your browser, choose a trip, name: `Steve`, email:
`steve@minecraft.com`, any date in the future and book the trip. Open the profiler for the
last request and click the `Emails` tab to see the email.

Much better! Notice the `From` and `To` addresses now have names. And our text content is
definitely more valuable! Copy the booking URL and paste it into your browser to make sure it goes to the
right place. Looks like it, nice!

Next, we'll use [Mailtrap](https://mailtrap.io/)'s testing tool for a more robust email preview.
