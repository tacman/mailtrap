# Installing the Mailer

Hey friends! Welcome to "Symfony Mailer with Mailtrap"! I'm Kevin,
and I'll be your *postmaster* for this course, which is all about sending
beautiful emails with Symfony's Mailer component, including adding HTML, CSS - and configuring
for production. On that note, there are many services you can use on production to actually send your
emails. This course will focus on one called Mailtrap: (1) because it's great and (2) because it offers a fantastic
way to preview your emails. But don't worry, the concepts we'll cover are universal
and can be applied to any email service. And bonus! We'll also cover how to track email *events* like
bounces, opens, and link clicks by leveraging some relatively new Symfony components: Webhook and RemoteEvent.

## Transactional vs Bulk Emails

Before we start spamming, ahem, delivering important info via email, we need to clarify something:
Symfony Mailer is for what's called *transactional*
emails *only*. These are user-specific emails that occur when something specific
happens in your app. Things like: a welcome email after a user signs up,
an order confirmation email when they place an order, or even emails like a
"your post was upvoted" are all examples of *transactional* emails. Symfony Mailer
is *not* for bulk or marketing emails. Because of this, we don't need to worry
about any kind of *unsubscribe* functionality.
There are specific services for sending bulk emails or newsletters, Mailtrap can even do this via their site.

## Our Project

As always, to deliver the most bang for your screencast buck, you should totally
code along with me! Download the course code on this page.
When you unzip the file, you'll find a `start/` directory with the code we'll start with.
Follow the `README.md` file to get the app running. I've already done this and ran
`symfony serve -d` to start the web server. 

Welcome to "Universal Travel": a travel agency where users can book trips
to different galactic locations. Here are the currently available trips. Users
*can* already book these, but there are no confirmation emails sent when they do.
We're going to fix that! If I'm spending thousands of credits on a trip to
Naboo, I want to know that my reservation was successful!

## Installing the Mailer Component

Step 1: let's install the Symfony Mailer! Open your terminal and run:

```terminal
composer require mailer
```

The Symfony Flex recipe for mailer is asking us to install some Docker configuration.
This is for a local SMTP server to help with previewing emails. We're going to use
Mailtrap for this so say "no". Installed! Run:

```terminal
git status
```

to see what we got. Looks like the recipe added some environment variables
in `.env` and added the mailer configuration in `config/packages/mailer.yaml`.

## `MAILER_DSN`

In your IDE, open `.env`. The Mailer recipe added this `MAILER_DSN` environment variable.
This is a special URL-looking string that configures your *mailer transport*:
*how* your emails are actually sent, like via SMTP, Mailtrap, etc. The recipe
defaults to `null://null` and is perfect for local development and testing. This transport does
nothing when an email is sent! It *pretends* to deliver the email, but
really sends it out an airlock. We'll preview our emails in a different way.

Ok! We're ready to send our first email! Let's do that next!
