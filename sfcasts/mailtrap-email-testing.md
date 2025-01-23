# Previewing Emails with Mailtrap (Email Testing)

Previewing emails in the profiler is okay for basic emails, but soon we'll
add HTML styles and images of space cats. To properly see how our emails look
like, we need a more robust tool. We're going to use [Mailtrap](https://mailtrap.io/)'s
[Mailtrap](https://mailtrap.io/)'s *email testing tool*. This gives us a real SMTP
server that we can connect to, but instead of delivering emails to real inboxes,
they go into a fake inbox that we can check out! It's like we send an email for real,
then hack that person's account to see it... but without the hassle
or all that illegal stuff!

Go to https://mailtrap.io and sign up for a free account. Their free tier plan
has some limits but is perfect for getting started. Once you're in, you'll
be on their app homepage. What we're interested in right now is *email testing*,
so click that. You should see something like this. If you don't have an inbox yet,
add one here.

Open that shiny new inbox. Next, we need to configure our app to send emails via
the Mailtrap SMTP server. This is easy! Click "SMTP Settings", "PHP" then "Symfony".
Copy the `MAILER_DSN`.

Because this is a sensitive value, and may vary between developers, don't
add it to `.env` as that's commited to git. Instead, create a new `.env.local`
file at the root of your project. Paste the `MAILER_DSN` here to override the fake
value in `.env`.

We are set up for Mailtrap testing! That was easy! Test'r out!

Back in the app, book a new trip: Name: `Steve`, Email: `steve@minecraft.com`, any
date in the future, and... book! This request takes a bit longer because
it's connecting to the external Mailtrap SMTP server.

Back in Mailtrap, bam! The email's already in our inbox! Click to check it out. Here's
a "Text" preview and a "Raw" view. There's also a "Spam Analysis" - cool! "Tech Info"
shows all the nerdy "email headers" in an easy-to-read format.

These "HTML" tabs are greyed out because we don't have an HTML version of our email...
yet... Let's change that next!
