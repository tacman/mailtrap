# Mailer and Webhook with Mailtrap

## Installing the Mailer

- Introduction
  - Symfony mailer
  - Beautiful emails with Twig/css
  - Mailtrap for preview and production sending
  - Testing emails in your PHPUnit tests
  - Relatively new components: Webhook + RemoteEvent
- Transactional vs Bulk/Marketing
  - symfony/mailer is for transactional emails only
  - user-specific emails
  - sent when a specific event happens in your app
  - "unsubscribe" functionality is not required
  - Examples:
    - welcome email
    - order confirmation
    - "your post was upvoted"
- Demo app
  - Download the course code to follow along
  - Follow the README.md to get the app running
  - Welcome to "Univeral Travel"!
    - A travel site where users can book trips to different galactic locations
    - User's can book trips but we need some email functionality!
- Installing `symfony/mailer`
  - `composer require mailer`
  - `no` to Docker configuration
    - this is for a local SMTP server for previewing emails
    - we're going to use Mailtrap for this!
  - `git status` to see what was installed
    - recipe added environment variable and configuration
  - Let's open these up!
    - `.env`: `MAILER_DSN` - special string to configure the "transport" - how/where to send emails
      - `=null://null`: default - transport that does nothing - great/fast for local development and your tests!
    - `config/packages/mailer.yaml` configuration - just configures the transport using the `MAILER_DSN` environment variable
- Mailer installed! Next, let's send our first email!

## Sending our first Email

- In our app, choose a trip
  - Let's book this one! (we're living in a post-scarcity society so no payment necessary!)
  - We've booked and now we're on this booking page
    - note this URL - it's unique to this booking and can't be reasonably guessed
    - I don't want to rely on the user bookmarking this page
    - I want to send an email with this link so they can easily find it later
- First email!
  - Open `src/Controller/TripController.php`
  - Check out the `show()` controller - this creates the booking when the booking form is valid
  - After we save the booking to the database, I want to send an email with the booking details
  - First, let's add some space here in the controller's arguments
  - Now, inject `MailerInterface $mailer` - this is the service that sends emails
  - Down here, after persisting the booking to the database, create an email
    - `$email = new Email()` - the one from `Symfony/Component/Mime`
    - Wrap in `()` so we can chain methods!
    - Now, what does every email need?
    - A from email: `->from('info@universal-travel.com')`
    - A to email: we can get this from the customer: `->to($customer->getEmail())`
    - A subject: `->subject('Booking Confirmation')`
    - A body: `->text('Your booking has been confirmed!')`
    - Email done! Don't worry, we'll improve this in a bit!
    - Now, to send the email:
    - `$mailer->send($email)` - that's it!
- Let's test'r our!
  - Book a trip using the name: `Steve` and email `steve@minecraft.com`
  - Choose a date in the future and submit
  - Ok, this looks exactly the same...
  - Down in the web debug toolbar, I don't see anything about an email being sent...
  - The email was actually sent in the "previous" request, the form submit, then we were redirected to this page
  - Beside this 200, we can see that we were redirected
  - Hover over and open the profiler panel for the previous request
  - Check the left side - there's a "Mailer" section with `1`! Open this:
    - Cool! There's are email! We even have a preview
    - Remember, we're using the null transport so the email isn't actually sent anywhere, but we can still get
      a basic preview in the profiler!
- This email is pretty crappy and doesn't give any useful information, let's improve it next!

## Better Email

- Our email has no value!
- First, add a name to our emails by wrapping in Address
  - `->from(new Address('info@universal-travel.com', 'Universal Travel'))` - Address from `Symfony/Component/Mime`
  - `->to(new Address($customer->getEmail(), $customer->getName()))`
- Improve the subject
  - `->subject('Booking Confirmation for '.$trip->getName())`
- For the body, let's use twig to avoid having it in our controller
  - Create `templates/email/booking_confirmation.txt.twig`
  - Back in `TripController`
    - Change `Email` to `TemplatedEmail`
    - Change `text()` to `textTemplate('email/booking_confirmation.txt.twig')`
    - Add template context:
        ```php
        ->context([
            'customer' => $customer,
            'trip' => $trip,
            'booking' => $booking,
         ])
        ```
    - In `booking_confirmation.txt.twig`, add some text:
        ```twig
        Hey {{ customer.name|split(' ')|first }},

        Get Ready for your trip to {{ trip.name }}!

        Departure: {{ booking.date|date('Y-m-d') }}

        Manage your booking: {{ url('booking_show', {uid: booking.uid}) }}

        Regards,
        The Universal Travel Team
        ```
    - Make sure to always use `url()` (not `path()`) in emails!
- Let's test this out!
  - Book a trip with `Steve`, `steve@minecraft.com` and a future date
  - Find the email in the profiler
  - Note the improvements!
    - From/To have names
    - And here's our body which includes the link
      - Copy and paste the url to ensure this works
      - All good!
- Next, let's use Mailtrap for a more robust preview and add an HTML version!

## Previewing Emails with Mailtrap "Email Testing"

- Profiler is fine for quick previews
- But for a more robust preview, we're going to use Mailtrap
  - Offers a "fake" (but real) SMTP server
  - Has a more realistic preview
  - Go to mailtrap.io Sign up for free
    - Choose "Email Testing" - that's what we'll use for previewing
    - Free plan has some restrictions (100 emails per month and 5 / 10 seconds)
    - But fine for our purposes
    - Add an inbox if one doesn't exist
    - So this is a "real" email server we can send emails to
    - It just lists them here instead of sending them to the real recipient
    - We need a MAILER_DSN
    - We could build it ourselves with these credentials but check this out!
    - Choose PHP and select Symfony from the dropdown
    - Sweet! This is our pre-filled MAILER_DSN! Copy it
    - Create a `.env.local` (this isn't committed to git)
    - Paste the MAILER_DSN
- Let's test it out
- Book a trip
- Checkout mailtrap
  - Here it is!
  - We can see the raw email
  - Some tech info to show our email headers
  - And even a spam analysis - nifty!
- These HTML tabs are grayed out - booo, let's add an HTML version next!

## HTML Email

- To make our email look more professional, let's add an HTML version
- Let's start simple: copy `booking_confirmation.txt.twig` to `booking_confirmation.html.twig`
  - Wrap in `<html>`
  - Add an empty `<head>` (not used in emails)
  - Wrap all content in `<body>`
  - Wrap paragraphs in `<p>` tags
  - Use an `<a>` for the link: `<a href="{{ url('booking_show', {uid: booking.uid}) }}">Manage your booking</a>`
- In `TripController`, on the email, add
  - `->htmlTemplate('email/booking_confirmation.html.twig')`
  - The context will be used for both the text and HTML versions
- Test this out!
  - Book a trip
  - Check out the email in Mailtrap
  - HTML working!
  - We even have a new HTML Check tab.. with.. a problem (ignore for now)
  - Looks good!
  - Click the link, yep, goes to our booking page, nice!
- Adding a text and html version is kind of a drag.
  - Who uses the text version anyway?
  - Still good to have, because, who knows!
  - Check this out, in `TripController`:
  - Remove this `->textTemplate()`
  - Book another trip and check mailtrap
  - We still have the Text version?
  - Symfony mailer sees we have an html version but not a text
  - It generates the text from the html by stripping out the html tags
  - But... we lost the link... not good!
- Let's fix this!
- `composer require league/html-to-markdown`
  - Html to Markdown? Don't we usually go the other way?
  - We want to convert our email html to a simple text version (which will include the links)
  - Mailer automatically detects this is installed and uses it!
  - Test it out!
  - Check the text tab - there's our link!
  - Not perfect, but at least it's there!
- Remove the `templates/email/booking_confirmation.txt.twig` file
- Next, let's spice up the HTML with CSS

## CSS in Emails

- CSS in emails requires some special care
- In `booking_confirmation.html.twig`, let's add some CSS
  - Add a `<style>` tag
  - Inside `.text-red { color: red; }`
  - Add `class="text-red"` to the first `<p>` tag
- Create a booking
- Find the email in Mailtrap
  - Ok, the color is red, what's the problem?
  - Check the HTML Source tab
  - This `<style>` tooltip is saying the style tag isn't supported in all emails
  - This ".text-red" tooltip is basically saying some email clients don't support css classes
- The solution is to use inline styles
  - Ugh, that could get ugly, right?
- Nope! There's a tool for that!
- Wrap the email in `{% apply inline_css %}{% endapply %}`
- Create a booking!
  - Error! The inline_css filter is not found
  - But we have a hint!
- `composer require twig/cssinliner-extra`
- Book the trip again
- Check the email in Mailtrap
- The style is now inlined in the p tag
- We still have the errors... it shouldn't be a problem but let's remove the <style> tag
- We'll use an external CSS file
- In `assets/styles` create `email.css` and add our style: `.text-red { color: red; }`
- To use this stylesheet in Twig, we need `assets/styles` to be a "twig path"
- In `config/packages/twig.yaml`, add `paths: { '%kernel.project_dir%/assets/styles': styles }`
- In `booking_confirmation.html.twig`:
  - `apply inline_css(source('@styles/email.css'))`
  - The argument to inline_css tells it to "use the passed css for inlining"
  - `source()` says "load this raw twig template" so the raw css is passed to `inline_css`
  - Remove the `<style>` tag
- Book a trip and checkout Mailtrap

## HTML With Inky & Foundation for Emails

- HTML in email is like writing HTML in 1995
  - Required for emails to look consistent in every client
  - Tables in tables in tables in tables....
- "Foundation for emails": CSS framework for emails
  - Download `foundation-emails.css` and put in `assets/styles`
- "Inky": a templating language that makes it easier to write HTML emails
  - Install with `composer require twig/inky-extra`
- In `assets/styles/email.css`, copy from tutorial
- In `booking_confirmation.html.twig`:
  - Add `source('@styles/foundation-emails.css')` to the `inline_css()` call (before custom styles)
  - Add `inky_to_html` filter
  - Copy `booking_confirmation.html.twig` content
  - Unpack...
- Book a trip and check Mailtrap
- Next: attachments and images!

## Attachments and Images

- We'll add a terms of service attachment to our email
  - Copy `terms-of-service.pdf` from `tutorials` to `assets`
  - In `TripController::show()`
    - Inject `string $termsPath`
    - `#[Autowire('%kernel.project_dir%/assets/terms-of-service.pdf')]`
    - In the email, see the autocomplete options for `->attach`
      - Just `->attach()` allows to attach from a stream or file contents (as string)
    - Use: `->attachFromPath($termsPath)` to attach a file from the filesystem
- Adding the trip image
  - We could use a URL for the image, but let's embed it!
    - Embedding images is super complex - luckily, the Mailer handles this for us!
  - First, we need to make the image path available to Twig (like we did with styles)
    - In `config/packages/twig.yaml`, add `paths: { '%kernel.project_dir%/public/imgs': images }`
  - The special `email` variable
    - When rendering an email twig template, you always have access to an `email` variable
    - Current email context
    - `WrappedTemplatedEmail` object
    - Has an `image()` method to make embedding emails a cinch!
  - In `booking_confirmation.html.twig`, under the `<h1>` tag, add
    - `<img class="trip-image float-center" src="" alt="{{ trip.name }} image">`
    - In `src="` add `{{ email.image('@images/%s.png'|format(trip.slug)) }}`
- Book a trip and check Mailtrap
  - Woo! There's our image!
  - And in the top right, there's our attachment!
    - Download and open to make sure it's correct - Nice!
  - Note the image isn't considered an attachment - it's embedded in the email
- Next, Mailer events and "Global From" address!

## Global From with Email Events

- Likely all/most emails with have the same "from" email
- Let's create an event listener to add this globally
- `symfony console make:listener`
  - `GlobalFromEmailListener`
  - `Symfony\Component\Mailer\Event\MessageEvent`
- Set a global from parameter in `config/services.yaml`
  - `global_from_email: 'Universal Travel <info@universal-travel.com>'`
  - (note the special string format - this'll be parsed by the `Address` class)
- Open `GlobalFromEmailListener`
  - Add constructor with `private string $fromEmail` parameter
  - `#[Autowire('%global_from_email%')]`
  - We can remove the `event:` argument from the attribute (implied from method argument typehint)
  - `$message = $event->getMessage()`
  - `RawMessage`: `TemplatedEmail` extends
  - `if (!$message instanceof TemplatedEmail) { return; }` (should always be the case but let's be sure)
  - `if ($message->getFrom()) { return; }` (if already set, don't override)
  - `$message->from($this->fromEmail);`
- In `TripController::show()`
  - Remove `->from()`
- Book a trip
  - from is set
- Reply-to
  - In `TripController::show()`:
  - consider a contact form that has the following fields
    - name
    - email
    - message
  - When that form is filled out, our support staff gets this email
  - When they hit reply, we want it to go to the contact form email
  - You might think to set the from email to the contact form email
  - But, as we'll see shortly, for production sending, your from email domain needs to be verified
  - Instead, use `->replyTo()`
  - Special email header for this purpose

## Production Sending with Mailtrap

- Could set all this up yourself... but it's complicated
- You can't just send emails as anyone to anyone like in the 90s
- It's best to use an email service
- Symfony Mailer has bridges for many to make integration easy!
- We're going to use the Mailtrap bridge
- `composer require symfony/mailtrap-mailer`
  - Check the new lines added to `.env`
  - We'll get the MAILER_DSN from Mailtrap
- Back over in the Mailtrap App, we need to setup a "Sending Domain"
  - You'll need to add a domain that you own to follow along
  - Since our lawyers are still securing the univeral-travel.com domain
  - I'm using my own domain, zenstruck.com for now
  - In order to send on behalf of your domain, you need to verify it
  - Basically, a bunch of DNS records you'll need to add
  - Mailtrap makes it super easy
  - This stuff is complex but Mailtrap makes it easy
    - Domain Verification: prove we own the domain
    - DKIM: verify emails are sent from our domain
    - SPF: authorize Mailtrap to send emails from our domain
    - DMARC: policy for what to do with emails that fail SPF/DKIM
  - Do what you need to do to get the "green checkmark" for each item here
    - Mailtrap walks you through each step
  - Jump over to "Tracking Settings"
    - We'll track "opens"
    - We'd need to upgrade to track "link clicks" but that's available, and can be super helpful!
  - Now over to "Integration"
    - "Integrate" using the "Transactional Stream"
    - Now we need to choose if we want to send using SMTP or API
    - I like to use API as it's more portable
    - Choose "API"
    - Just like "Mailtrap Testing", choose PHP, then Symfony
    - Copy the `MAILER_DSN`
- Back in our app
  - In `.env.local` (we don't want this sensitive value committed)
  - Comment out the existing "Mailtrap Testing" `MAILER_DSN`
  - Paste the new value and remove the comment above
- We're almost ready for production sending but...
  - We need to set our "global from" email to our verified domain
  - In `config/services.yaml`, change the `global_from_email`
    - To any email address on your verified Mailtrap domain
    - I'll use `info@zenstruck.com` but you'll need to use your own
- Let's test it out!
  - Book a trip - remember, this is now sending a real email, so
    use your personal email address - I'll use kevin@symfonycasts.com
  - Check out your "Real" email's inbox
  - Here it is!
  - Check the "to" - it's our friend Steve - this is the benefit of using recipients
    on the envelope instead of overriding "to" in the listener
  - Click the link, works!

## Email Tracking with Tags and Metadata

- Our emails are successfully being sent in production
- Mailtrap has a lot of cool tracking features
- In the Mailtrap app, choose "Email API/SMTP"
  - Here's the stats dashboard
  - Choose "Email Logs"
  - This is the history of sent emails
  - Super useful for diagnosing email issues
  - Click our sent email
  - This looks similar to Mailtrap Testing
  - Go to "Event History"
    - Here we see tracked events:
      - "send"
      - "delivered"
      - "open"
    - If we had link tracking enabled, we'd see these here!
  - Back in "Email Info", notice a "Category" is missing
  - A "category" enables filtering on the different types of emails your app sends
- Let's add a category
- In Mailtrap, this is a category, but Symfony Mailer calls them "tags"
- In `TripController::show()`
  - After creating the email
    - Add `$email->getHeaders()->add(new TagHeader());`
    - This is a string that can be anything you want, let's use `booking`
    - While we're here, we can also add some metadata to help with tracking
    - Again, this can be anything, let's add the booking and customer uid
    - `$email->getHeaders()->add(new MetadataHeader('booking_uid', $booking->getUid()));`
    - `$email->getHeaders()->add(new MetadataHeader('customer_uid', $customer->getUid()));`
- In our app, book a trip and check our email and open
- In Mailtrap, find it in our "Email Logs"
- Check it out, we now have a Category: "booking"
- And down here, Mailtrap calls them "Custom Variables", but this is our metadata
- Back in "Email Logs", we can filter on our category
  - In the filter, choose "Categories", now, all our categories will be available in this dropdown
- For the next several chapters, we don't need to send in production
  - In `.env.local`, comment out our production `MAILER_DSN`
  - And uncomment the "Mailtrap Testing" `MAILER_DSN`
- Next, let's send our emails asynchronously with symfony/messenger!

## Async Sending with Messenger

- When a controller sends an email, it has to make a network request to an email server
- This can potentially be slow
- This could potentially fail, showing the user a 500 error - booo!
- Let's send them to a message queue to be processed by a worker!
  - This improves the request time + gives us more control over retries and failures
- At your terminal, install `symfony/messenger` and the Doctrine Messenger transport
  - `composer require messenger symfony/doctrine-messenger`
- In `.env`, the recipe added the doctrine mailer dsn
  - This requires a new table in our database
  - We should create a migration for this but... we'll cheat for now
  - In the DSN, set `auto_setup=0` (this auto creates the table when it's needed)
- In `config/packages/messenger.yaml`:
  - uncomment `failure_transport` and the `failed` transport
    - When a message (in our case, email send message) fails, it's retried 3 times,
      then sent to the `failed` transport for manual review
  - uncomment `async` - this uses the DSN from `.env`
  - Under routing, add `'Symfony\Component\Mailer\Messenger\SendEmailMessage': async`
    - This is the Messenger message Symfony Mailer wraps our email in
    - We want this sent to our `async` transport
- We're ready to send our emails asynchronously - there's nothing needed in our code!
- In our app, book a trip
- Don't check mailtrap yet - it won't be there - it's Queued
- Check the profiler for the previous request
  - Check the "Emails" section - it's "queued"!
  - Check the "Messages" section - there's our `SendEmailMessage`
- In your terminal, process the queue with
  - `symfony console messenger:consume async`
- Check mailtrap
  - There it is!
- But... there's a problem!
  - Click a link
  - `localhost`? That's not right!
- There's some special consideration needed when sending emails async
- Let's fix this next!

## Links in Async Emails

- When the email is sent to the queue, it isn't yet rendered
- It's rendered when the worker processes the message
- Since the worker is a command, it doesn't have access to the request context
- When generating urls with `url()`, it uses a default base url: `localhost`
- We need to give the router the base url to use
- We can set this in `config/packages/routing.yaml`
  - Set `framework.router.default_uri`: `https://universal-travel.com`
  - (you might want to use a parameter or env variable here)
  - In development, we need this set to our local server: `https://127.0.0.1:8000/`
  - But... there's no guarantee this will always be the same
  - The Symfony CLI can use a different port
  - Under `when@dev`
    - Add a parameter: `env(SYMFONY_PROJECT_DEFAULT_ROUTE_URL): 'http://localhost`
    - Add `framework.router.default_url`: `%env(SYMFONY_PROJECT_DEFAULT_ROUTE_URL)%`
  - This environment variable is only available when the server is running
- Book a trip:
  - In your terminal, run the worker `symfony console messenger:consume async`
  - Check Mailtrap
  - Link works!
- Keeping the messenger queue running in development can be a pain
  - You have to keep a terminal open and remember to run the command
  - Symfony CLI has a cool trick!
  - Open `.symfony.local.yaml`
  - This `workers` section allows you to run things in the background
    when the server is running
  - We already have our tailwind build command running here
  - Let's add our messenger worker!
  - Under workers, add `messenger:`
    - Then: `cmd: ['symfony', 'console', 'messenger:consume', 'async']`
    - And: `watch: ['config', 'src', 'templates', 'vendor']`
    - The `watch` option will restart the worker if files in these directories change
  - We need to restart the webserver to pick up this change
  - At your terminal:
    - Run `symfony server:stop`
    - Then `symfony serve -d`
    - Ensure our messenger worker is running with: `symfony server:status`
- Book a trip and check Mailtrap: email is handled immediately!
- Next, let's functional test our emails!

## Emails Assertions in Functional Tests

- We already have some functional tests:
- Open `BookingTest` - tour:
  - `zenstruck/foundry`
    - `ResetDatabase`: resets the db before each test
    - `Factories`: enables using factories in tests
  - `zenstruck/browser`
    - `HasBrowser`: enables this ->browser() helper
    - wrapper for Symfony's test client
  - `testCreateBooking`
    - "Arrange": Creating a trip to book
    - "Pre-assertion": Ensure no trips/customers
    - "Act": Use browser to book a trip
    - "Assert":
      - Check response after booking
      - Assert booking/customer exists in the db
- `bin/phpunit` to run them
  - Fail!
  - add `->throwsExceptions()` to see the actual exception
  - because of images, change slug to existing image slug: `iss`
- We need to check that the email was sent
- Symfony has email testing helpers but I prefer a "wrapper" library
- `composer require --dev zenstruck/mailer-test`
  - Enables a bundle
- FYI, `.env.local` isn't used in tests
  - Instead, `.env` (which has our MAILER_DSN as null://null)
  - So no emails will be really sent when running tests
- In `BookingTest`, add the `InteractsWithMailer` trait
- At the end of the test add:
  - `$this->mailer()->assertSentEmailCount(1)`
  - `bin/phpunit`
  - `->assertEmailSentTo('bruce@wayne-enterprises.com', 'Booking Confirmation for Visit Mars')`
  - `bin/phpunit`
- More email detail assertions
  - `->assertEmailSentTo('bruce@wayne-enterprises.com', function(TestEmail $email) {})`
  - `$email->assertSubject('Booking Confirmation for Visit Mars')`
  - `assertHtmlContains()`/`assertTextContains()` but...
  - `->assertContains('Visit Mars')` checks that both contain this text
  - Check the link:
  - `->assertContains('/booking/'.BookingFactory::first()->getUid())`
  - Attachment:
  - `->assertHasFile('Terms of Service.pdf')` - can also check exact file contents
  - Some additional features:
  - `->assert` see IDE autocompletion
  - `->dd()` dump the email to diagnose
- We'll soon add a second email, so let's create a base template.

## Email Twig Layout

- Fix worker configuration
  - In `.symfony.local.yaml`, under `messenger`, remove `vendor` from `watch`
  - `symfony server:stop`
  - `symfony server:start -d`
- Want to create a second "booking reminder" email
- First, let's create a base layout for our emails
- Create `templates/email/layout.html.twig`
  - Copy/paste contents of `booking_confirmation.html.twig`
  - Add content block - leave signature
- In `booking_confirmation.html.twig`, remove everything but the content block
  - Add `{% extends 'email/layout.html.twig' %}`
  - Add `{% block content %}` and `{% endblock %}`
  - Copy/paste email specific content into block
  - Remove rest of file
- Test booking a trip and check mailtrap
- Our reminder email will send booking reminder 7 days before the trip
- We need to update our booking track if a reminder has been sent (to avoid sending multiple)
- `symfony console make:entity Booking`
  - `reminderSentAt`, `datetime_immutable`, `nullable`
- Update our db (we aren't using migrations)
  - `symfony console doctrine:schema:update --force`
- `BookingRepository::findBookingsToRemind()`
  - `->andWhere('b.reminderSentAt IS NULL')` (reminder not yet sent)
  - `->andWhere('b.date <= :future')`
  - `->andWhere('b.date > :now')`
  - `->setParameter('future', new \DateTimeImmutable('+7 days'))`
  - `->setParameter('now', new \DateTimeImmutable('now'))`
- Add some fixtures we know will be reminded
- In `AppFixtures`, add one we know will trigger a reminder
  ```php
  BookingFactory::createOne([
      'trip' => $arrakis,
      'customer' => $clark,
      'date' => new \DateTimeImmutable('+6 days'),
  ]);
  ```
- Reload fixtures: `symfony console doctrine:fixtures:load`
- Next, we'll create the command and reminder email

## Email from CLI Command

- Copy `booking_confirmation.html.twig` to `booking_reminder.html.twig`
  - Will be super similar
  - Change the accent to `Coming soon!`
- Create a new command
  - `symfony console make:command`
  - `app:send-booking-reminders`
- Open `SendBookingRemindersCommand`
  - Adjust description: `Send booking reminder emails`
  - Add `BookingRepository`, `EntityManagerInterface` and `MailerInterface` as arguments
  - Delete `configure()`
  - In `execute()`:
    - Delete guts
    - `$io->title('Sending booking reminders');`
    - `$bookings = $this->bookingRepo->findBookingsToRemind();`
    - `foreach ($io->progressIterate($bookings) as $booking)`
    - Super easy way to create a progress bar
    - Copy email creation from `TripController::show()` and adjust
      - Subject: `Reminder for...`
      - Template: `email/booking_reminder.html.twig`
      - Remove `->attach...`
      - Tag: `booking_reminder`
    - `$this->mailer->send($email)`
    - `$booking->setReminderSentAt(new \DateTimeImmutable());`
    - Outside if:
      - `$this->em->flush();`
      - `$io->success(sprintf('Sent %d booking reminders', count($bookings)));`
- Reload fixtures: `symfony console doctrine:fixtures:load`
- Run Command: `symfony console app:send-booking-reminder`
- Check mailtrap
  - In Mailtrap test - tags and metadata can be checked under "Tech Info"
- Run command again - 0 emails sent
- Next, we'll add a functional test for this command!

## Test for CLI Command

- `symfony console make:test`
  - `KernelTestCase`
  - `SendBookingRemindersCommandTest`
  - open test
  - move class to namespace: `App\Tests\Functional\Command`
  - delete contents
  - add `ResetDatabase`, `Factories`, `InteractsWithMailer` traits
  - add two tests:
    - `testNoRemindersToSend` - `$this->markTestIncomplete()`
    - `testSendsReminders` - `$this->markTestIncomplete()`
- Run tests: `bin/phpunit`
- Nice, here's our todo list!
- Symfony has tooling for testing commands but...
- We're going to use another testing helper library
- `composer require --dev zenstruck/console-test`
- Back in `SendBookingRemindersCommandTest`
  - Add `InteractsWithConsole` trait
  - In `testNoRemindersToSend()`
    - `$this->executeConsoleCommand('app:send-booking-reminders')`
    - `->assertSuccessful()`
    - `->assertOutputContains('Sent 0 booking reminders')`
    - `$this->mailer()->assertNoEmailSent()`
  - In `testSendsReminders()`
    - `$booking = BookingFactory::createOne()`
      - `'trip' => TripFactory::new()`
        - `'name' => 'Visit Mars'`
        - `'slug' => 'iss'`
      - `'customer' => CustomerFactory::new(['email' => 'steve@minecraft.com']),`
      - `'date' => new \DateTimeImmutable('+4 days'),`
    - Pre-assertion: `$this->assertNull($booking->getReminderSentAt())`
    - `$this->executeConsoleCommand('app:send-booking-reminder')`
    - `->assertSuccessful()`
    - `->assertOutputContains('Sent 1 booking reminders')`
    - Copy/paste and adjust email assertions from `BookingTest`
    - Assert reminder sent at is set:
      - `$this->assertNotNull($booking->getReminderSentAt())`
- In terminal: `bin/phpunit`
- Tests done!
- This style of outside-in tests make it easy to go the opposite way:
  - Write the test first, then write the code to make it pass
  - "TDD"
- Now that we have tests covering sending both our emails
  Let's refactor away the duplication into a booking email factory
  That's next!

## Email Factory Service

- Reduce email creation duplication between `TripController` and `SendBookingRemindersCommand`
- New class: `BookingEmailFactory` in `App\Email` namespace
  - Constructor - copy `$termsPath` arg from `TripController`
  - `public function createBookingConfirmation(Booking $booking): TemplatedEmail`
  - `public function createBookingReminder(Booking $booking): TemplatedEmail`
  - `private function createEmail(Booking $booking, string $tag): TemplatedEmail`
    - Copy/paste/adjust email creation code from `TripController`
  - `createBookingConfirmation`
    - set `subject` & `htmlTemplate`
  - `createBookingReminder`
    - copy from above and adjust
- Use the service!
- In `TripController::showAction`
  - Inject `BookingEmailFactory $emailFactory` into controller
  - Replace email creation with `$mailer->send($emailFactory->createBookingConfirmation($booking));`
- In `SendBookingRemindersCommand`
  - Inject `private BookingEmailFactory $emailFactory` into constructor
  - Replace email creation with `$this->mailer->send($this->emailFactory->createBookingReminder($booking));`
- Let's just use our tests to ensure this works!
  - `bin/phpunit`
  - Failure! "Message does not include file with filename..."
- Perfect, we forgot something but our tests caught it!
- In `BookingEmailFactory::createBookingEmail`
  - Add `->attachFromPath($this->termsPath, 'Terms of Service.pdf')`
- Run tests again: `bin/phpunit`
- Green!
- Next, use the Webhook component to track those Mailtrap email events!

## Webhook for Email Events

- Webhook/RemoteEvent components...
- `composer require webhook remote-event`
- Webhook: consumes incoming webhooks on a single `/webhook` route in your app
- RemoteEvent: like an *internal event* but dispatched by an external service to app's webhook
- `config/routes/webhook.yaml`
- `symfony console debug:route webhook`
- You need to create:
  - remote events for each webhook you want to consume
  - parsers to validate, parse, and convert raw webhook payloads into remote events
  - consumers to take the remote event and do something with it
- RemoteEvent component provides generic email events
  - `MailerDeliveryEvent`: for successful/failed (bounced) email deliveries
  - `MailerEngagementEvent`: for email opens/clicks
- The Mailtrap bridge provides a parser to parse/convert the above remote events
- It's up to us to create a consumer
- https://symfony.com/doc/current/webhook.html#usage-in-combination-with-the-mailer-component
  - find "mailtrap" service id
- create `config/packages/webhook.yaml`
  - `framework.webhook.routing.mailtrap.service: mailer.webhook.request_parser.mailtrap`
- Create a new `EmailEventConsumer` in `App\Webhook`
  - implements `ConsumerInterface`
  - implement the `consume()` method
  - add `#[AsRemoteEventConsumer('mailtrap')]` attribute to class
  - Above `consume()` add docblock with `@param MailerDeliveryEvent|MailerEngagementEvent $event`
  - Inside consume - this is where we'd add our application logic
    - `$event->` show all the methods
    - `dump($event)` for our purposes

## Demo our Webhook

- First, switch `MAILER_DSN` back to your production DSN
- Start/stop Symfony CLI server
- We need to expose our local server to the internet
  - I'm going to use ngrok: https://ngrok.com/
  - Create account, download/setup the client
  - `symfony server:status` to get the url to expose
  - `ngrok http <url>`
  - Visit "Forwarding URL" in your browser - Visit Site
  - Back in the terminal, we can see the requests
- On mailtrap.io, go to "Settings" -> "Webhook" -> "Create New Webhook"
  - Add the "Forwarding URL" + `/webhook/mailtrap`
  - Stream: Transactional
  - Domain: <your domain>
  - Select all events
  - Save
- In `EmailEventConsumer`, remember, we added the `dump()`?
- In your terminal, open a new tab and run:
  - `symfony console server:dump`
- In the app, create a booking using a real email address
- At the terminal, wait for/inspect the delivery event
- Open the email
- At the terminal, wait for/inspect the engagement event

## Bonus: Scheduling our Email Command

## Bonus: Monitoring Messenger

## Bonus: Signing Emails (SMIME)

- Verify the email hasn't been tampered with
- Requires a S/MIME Certificate (similar to an SSL Cert)
- Obtained from a Trusted CA (Certificate Authority) - provides the following:
  - `certificate.pem`
  - `private-key.pem`
- In Mailtrap, we need to disable open & click tracking
  - These features modify the email and will cause the signature to fail
- Associated with an email - mine is associated with `info@zenstruck.com` (our from address)
- We need to use SMTP to send signed emails (update .env.local)
- Create a new Listener
  - `symfony console make:listener`
  - `EmailSigningListener`
  - `Symfony\Component\Mailer\Event\MessageEvent`
- Needs to run after the template is rendered
- `symfony console debug:event MessageEvent`
- `MessageListener::onMessage()` is what renders the template
- So we need to run after this
- In `EmailSigningListener`
  - remove `#[AsEventListener]` `event` argument (not needed)
  - add `priority: -1000`
- Back in terminal, `symfony console debug:event MessageEvent`
- Runs after now
- Back in Email `EmailSigningListener`
  - add constructor with `private string $certificatePath` and `private string $privateKeyPath`
  - autowire each with `#[Autowire('%kernel.project_dir%/...')]`
  - In `onMessageEvent`
    - `$email = $event->getMessage()`
    - `if (!$email instanceof TemplatedEmail) { return; }`
    - `$signer = new SMimeSigner($this->certificatePath, $this->privateKeyPath)`
    - `$signedEmail = $signer->sign($email)`
    - `$event->setMessage($signedEmail)`
- Test - check Gmail
