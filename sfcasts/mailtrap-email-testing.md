# Previewing Emails with Mailtrap "Email Testing"

Previewing emails in the Symfony profiler is okay for basic emails, but as we start
adding HTML, we need a more robust tool. We're going to use
[Mailtrap](https://mailtrap.io/)'s *email testing tool*. This gives us a real SMTP
server that we can connect to, but all emails go into a fake inbox that we can audit
and preview. It's so awesome!

Start by going to `mailtrap.io` and sign up for a free account. Their free tier plan
has some limits but is perfect for getting started. Once you're in, you'll
be on their app homepage. What we're interested in right now is *email testing*,
so click that. You should see something like this. If you don't have an inbox yet,
you can add one here.

Open the inbox. This is where our emails with show up. We need to configure the
SMTP server in our app. This is all the details we need, but check this out: down here
in "Code Samples", choose "PHP" and then "Symfony". This is the exact `MAILER_DSN`
we need to integrate this test inbox into our app. Beautiful! Copy it.

Because this is a sensitive value, and may vary between different developers, I don't
want to add it to `.env` as that's commited to git. Instead, create a new `.env.local`
file at the root of your project. This file is ignored by git and is just for your
local environment variable overrides. Paste the `MAILER_DSN` here.

We are setup for Mailtrap testing! That was easy! Test'r out!

Back in our app, book a new trip: Name: `Steve`, Email: `steve@minecraft.com`, any
date in the future, and book. This request takes a bit longer than before because
it's actually connecting to the external Mailtrap SMTP server.

Back in Mailtrap, the email's already in our inbox! Click it to see the email. Here's
a "Text" preview and a "Raw" view. There's also a "Spam Analysis" - cool! "Tech Info"
shows all the "email headers" in an easy-to-read format.

These "HTML" tabs are greyed out because we don't have an HTML version of our email...
yet... Let's change that next!
