# Installing the Mailer

Hey friends! Welcome to the "Symfony Mailer with Mailtrap" course! I'm Kevin,
and I'll be your *postmaster* for this course. We're going to cover installing and
using the Symfony Mailer component to send beautify emails using CSS, HTML and Twig,
*previewing* emails with a slick service called Mailtrap, configuring production
email sending with Mailtrap, and using some relatively new Symfony components:
Webhook and RemoteEvent. We'll use these to setup a webhook in our app to track
email events like bounces, opens, and link clicks.

First thing we need to clarify: Symfony Mailer is for what's called *transactional*
emails *only*. These are user-specific emails that occur when a specific event
happens in your app. Things like: a welcome email after a user signs up,
an order confirmation email when they place an order, or even emails like a
"your post was upvoted" are all examples of *transactional* emails. Symfony Mailer
is *not* for bulk or marketing emails. And because of this, we don't need to worry
about any kind of *unsubscribe* functionality.

Let's get started! To follow along, download the course code on this page and
follow the `README` to get our app setup. I've already done this and ran
`symfony serve -d` to start the web server. Check it out!

Welcome to "Univeral Travel"! This is a travel agency where users can book trips
to different galactic locations. Here's the currently available trips. User's can
already book these trips, but we need to add some emails!

Let's install the Symfony Mailer! Open your terminal and run:

```terminal
composer require mailer
```

The Symfony Flex recipe for mailer is asking us to install some Docker configuration.
This is for a local SMTP server to help with previewing emails. We're going to use
a service called Mailtrap for this so say "no". Installed! Run:

```terminal
git status
```

to see what we got. Looks like the recipe added some environment variables
in `.env` and added the mailer configuration in `config/packages/mailer.yaml`.

In your IDE, open `.env`. The Mailer recipe added this `MAILER_DSN` environment variable.
This is a special URL-looking string that configures the *mailer transport* you want to use.
The transport is *how* your emails are sent and Mailer provides many different options. The
default, `null://null` is perfect for local development and testing. This transport does
nothing when an email is sent. When running your test suite that sends 1000 emails, you don't
want those *actually* being sent!

We're ready to send our first email! Let's do that next!
