# Better Email

I think you, me, anyone that's ever received an email, can agree that our first email
stinks. It doesn't provide any value. Let's improve it!

First, we can add a name to the email addresses. This will show up in most email
clients instead of just the email address - so it looks nicer. Wrap the `from` with
`new Address()`, the one from `Symfony\Component\Mime`. The first argument is the
email, and the second is the name - use `Universal Travel`. Now wrap the `to` with
`new Address()`. For the name, use `$customer->getName()`.

For the `subject`, add the trip name: `'Booking Confirmation for ' . $trip->getName()`.

Now for the `text` body. We could inline all the text we want here, but since it's going
to be multi-line, this would be a bit of a mess. Instead, let's use Twig! We need a template,
in `templates/`, add a new `email` directory and inside, create a new file:
`booking_confirmation.txt.twig`. Twig can be used for any text format, not just `html`.
A good practice is to prefix the `.twig` extension with the text format - `.txt.twig`
in this case. We'll come back to this file in a second.

Back in `TripController::show()`, instead of `new Email()`, replace with `new TemplatedEmail()`
(the one from `Symfony\Bridge\Twig`). Now we can use a Twig template for the body! Replace
`->text()` with `->textTemplate()` and pass our template: `email/booking_confirmation.txt.twig`.
Now, our template will need *context*, the variables we want to pass to Twig. Write `->context()` 
and pass an array: `['customer' => $customer, 'trip' => $trip, 'booking' => $booking]`.

Note, we aren't actually *rendering* the Twig template here - Symfony Mailer's Twig integration
does that for us.

Jump back to our template and add some content! Start with `Hey {{ customer.name|split(' ')|first }},` -
this is a quick way to get the customer's first name so the email's a bit more personable. Now,
`Get ready for your trip to {{ trip.name }}!` and then, `Departure date: {{ booking.date|date('Y-m-d') }}`. 
Next, I want a URL so they can come back to the site and manage their booking.
Write, `Manage your booking: {{ url('booking_show', {'uid': booking.uid}) }}`.
You might be used to using the `path()` Twig function for generating urls but this generates
an *absolute path* - like `/booking`. We need an *absolute url* - like `https://univeral-travel.com/booking`
so be sure to always use the `url()` function in emails. Finally, end with, `Regards, the Universal Travel team`.

Email body done! Test it out. Back in your browser, choose a trip, name: `Steve`, email:
`steve@minecraft.com`, any date in the future and book the trip. Open the profiler for the
last request and click the `Emails` tab to see the email.

Much better! Notice the `From` and `To` addresses now have names. Our text content is
definitely more valuable: it includes the trip name, departure date and an absolute
booking URL. Copy the URL and paste it into your browser to make sure it goes to the
right place. Looks like it, nice!

Next, we'll use [Mailtrap](https://mailtrap.io/)'s testing tool for a more robust email preview.
