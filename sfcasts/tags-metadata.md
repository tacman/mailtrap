# Email Tracking with Tags and Metadata

We're now sending emails for *real*. Let's just double-check
our links are working... All good!

Mailtrap can do more than just deliver & debug emails: we can also track emails and
email *events*. Jump over to Mailtrap and click "Email API/SMTP". This dashboard
shows us an overview of each email we've sent. Click "Email Logs" to see the full
list. Here's our email! Click it to see the details.

Hey! This look familiar... it's similar to the Mailtrap testing interface. We
can see general details, a spam analysis and more. But this is really cool: click
"Event History". This shows all the *events* that happened during the *flow* of this
email. We can see when it was sent, delivered, even opened by the recipient! Each
event has extra details, like the IP address that opened the email. Super useful
for diagnosing email issues. Mailtrap also has a link tracking feature that, if
enabled, would show which links were clicked in the email.

Back on the "Email Info" tab, scroll down a bit. Notice that the "Category" is "missing".
This isn't actually a problem, but a "category" is a string that helps organize
the different emails your app sends. This makes searching easier and can give us interesting
stats like "how many user signup emails did we send last month?".

Symfony Mailer calls this a "tag" that you can add to emails. The Mailtrap bridge
takes this tag and converts it to their "category". Let's add one!

In `TripController::show()`, after the email creation, write:
`$email->getHeaders()->add(new TagHeader());` - use `booking` as the name.

Mailer also has a special *metadata* header that you can add to emails. This is a
free-form key-value store for adding additional. The Mailtrap bridge
converts these to what they call "custom variables".

Let's add a couple:

`$email->getHeaders()->add(new MetadataHeader('booking_uid', $booking->getUuid()));`

And:

`$email->getHeaders()->add(new MetadataHeader('customer_uid', $customer->getUuid()));`

Attached to every *booking* email is now a customer and booking reference. Awesome!

To see how these'll look in Mailtrap, jump over to our app and book a trip (remember,
we're still using *production sending* so use your personal email). Check our inbox...
here it is. Back in Mailtrap, go back to the email logs... and refresh... there it is!
Click it. Now, on this "Email Info" tab, we see our "booking" category! Down a bit
further, here's our metadata or "custom variables".

To filter on the "category", go to the email logs. In this search box, choose
"Categories". This filter lists all the categories we've used. Select "booking" and
"Search". This is already more organized than the Jeffries tubes down in engineering!

So that's production email sending with Mailtrap! To make things easier for the next chapters,
let's switch back to using Mailtrap testing. In `.env.local`, uncomment the Mailtrap
testing `MAILER_DSN` and comment out the production sending `MAILER_DSN`.

Next, let's use Symfony Messenger to send our emails *asynchronously*. Ooo!
