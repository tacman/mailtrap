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
