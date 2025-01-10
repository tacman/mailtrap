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
