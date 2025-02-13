# Production Sending with Mailtrap

Alrighty, it's finally time send *real* emails in production!

## Mailer Transports

Mailer comes with various ways to send emails, called "transports".
This `smtp` one is what we're using for our Mailtrap testing. We *could* set up
our own SMTP server to send emails... but... that's complex, and you need to
do a lot of things to make sure your emails don't get marked as spam. Boo.

## 3rd-Party Transports

I highly, highly recommend using a 3rd-party email service. These handle all
these complexities for you and Mailer provides *bridges* to many of these
to make setup a breeze.

## Mailtrap Bridge

We're using Mailtrap for testing but Mailtrap *also* has production
sending capabilities! Fantabulous! It even has an official bridge!

At your terminal, install it with:

```terminal
composer require symfony/mailtrap-mailer
```

After this is installed, check your IDE. In `.env`, the recipe
added some `MAILER_DSN` stubs. We can get the real DSN values from Mailtrap,
but first, we need to do some setup.

## Sending Domain

Over in Mailtrap, we need to set up a "sending domain". This configures a domain
you own to allow Mailtrap to properly send emails on its behalf.

Our lawyers are still negotiating the purchase of `universal-travel.com`, so
for now, I'm using a personal domain I own: `zenstruck.com`.
Add your domain here.

Once added, you'll be on this "Domain Verification" page. This is super important
but Mailtrap makes it easy. Just follow the instructions until you get this green checkmark.
Basically, you'll need to add a bunch of specific DNS records to your domain. DKIM, which
verifies emails sent from your domain, and SPF, which authorizes Mailtrap to send emails
on your domain's behalf are the most important. Mailtrap provides great documentation
on these if you want to dig deeper on how exactly these work.
But basically, we're telling the world that Mailtrap is allowed to send emails on our
behalf.

Once you have the green checkmark, click "Integrations" then "Integrate" under
the "Transaction Stream" section.

We can now decide between using SMTP or API. I'll use the API, but either
works. And hey! This looks
familiar: like with Mailtrap testing, choose PHP, then Symfony. This is the `MAILER_DSN`
we need! Copy it and jump over to your editor.

This is a sensitive environment variable, so add it to `.env.local` to avoid
committing it to git. Comment out the Mailtrap testing DSN and paste
below. I'll remove this comment because we like to keep life tidy.

Almost ready! Remember, we can only send emails in production *from* the domain we
configured. In my case, `zenstruck.com`. Open `config/services.yaml` and update the
`global_from_email` to your domain.

Let's see if this works! In your app, book a trip. This time use a *real*
email address. I'll set the name to `Kevin` and I'll use my personal email:
`kevin@symfonycasts.com`. As much as I love you and space travel,
put your own email here to avoid spamming me. Choose a date and book!

We're on the booking confirmation page, that's a good sign! Now, check your personal
email. I'll go to mine and wait... refresh... here it is! If I click it, this is
exactly what we expect! The image, attachment, everything is here!

Next, let's see how we can track sent emails with Mailtrap plus add tags and metadata
to improve that tracking!
