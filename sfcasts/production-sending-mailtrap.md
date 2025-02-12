# Production Sending with Mailtrap

Alright, it's finally time send emails to a real email in production!

## Mailer Transports

If you recall, Mailer uses *transports* to send emails. If you look at the
Symfony Mailer documentation, it lists some built-in transports. This `smtp`
one is actually what we're using for our Mailtrap testing. We *could* set up
our own SMTP server to send emails... but... there's a ton of complex things
to consider. It's not the 90's where you can send an email *to* anyone, *from*
anyone, and it'll get through. There are a ton of things that need to be
configured to make sure your emails get through spam filters.

## 3rd-Party Transports

I highly, highly recommend using a 3rd-party email service. These handle all
these complexities for you. Symfony Mailer provides *bridges* to many of these
in the form of additional packages.

## Mailtrap Bridge

We're already using Mailtrap for testing but Mailtrap also has production
sending capabilities. And, there's an official bridge for it!

At your terminal, install it with:

```terminal
composer require symfony/mailtrap-mailer
```

After this is installed, check your IDE. In `.env`, the recipe for this package
added some `MAILER_DSN` stubs. We can get the real DSN values from Mailtrap
but first, we need to do some setup.

## Sending Domain

Over in Mailtrap, we need to setup a "sending domain". This configures a domain
you own to allow Mailtrap to properly send emails on its behalf. Click
"Sending Domains".

Our lawyers are still negotiating the purchase of `universal-travel.com`, so
for now, I'm using a personal domain I own: `zenstruck.com`. If following along,
you'll need to add your own domain here.

Once added, you'll be on this "Domain Verification" page. This is super important
but Mailtrap makes it easy. Just follow the instructions until you get this green checkmark.
Basically, you'll need to add a bunch of specific DNS records to your domain. DKIM, which
verifies emails sent from your domain, and SPF, which authorizes Mailtrap to send emails
on your domain's behalf are the most important. Mailtrap provides great documentation
on these if you want to dig deeper on how exactly these work.

If not, just get green checkmarks here and you'll be good to go!

Once you do, click "Integration", then "Integrate" under the "Transaction Stream" section.

You can now decide between using SMTP or API. I'm going to use the API. This should look
familiar. Like with Mailtrap testing, choose PHP, then Symfony. This is the `MAILER_DSN`
we need! Copy this and jump over to your IDE.

Again, this is a sensitive environment variable, so we'll add to `.env.local` so it
isn't commited to our repository. First, comment out our Mailtrap testing DSN and paste
below. Remove this comment above.

Almost ready! Remember, we can only send emails in production *from* the domain we
configured. In my case, `zenstruck.com`. Open `config/services.yaml` and update the
`global_from_email` to your domain.

Let's see if this works! In your app, book a trip. Remember, you need to use a *real*
email address here. I'll set the name to `Kevin` and I'll use my personal email:
`kevin@symfonycasts.com`, but you'll need to put your own email here so you don't
spam me! Choose a date and book!

We're on the booking confirmation page, that's a good sign! Now, check your personal
email. I'll go to mine and wait... refresh... here it is! If I click it, this is
exactly what we expect! The image, attachment, everything is here! Sweet!

Next, let's see how we can track sent emails with Mailtrap plus add tags and metadata
to improve the tracking!
