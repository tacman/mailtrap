# Generating URLs in the CLI Environment 

When we switched to asynchronous email sending, we broke our email links! It's using
`localhost` as our domain, but that's not right!

Back in our app, we can get a hint as to what's going on by looking at the
profiler for the request that sent the email. Remember, our email is now marked as
"queued". Go to the "Messages" tab and find the message: `SendEmailMessage`. Inside
is our `TemplatedEmail` object. Open this up. Notice the `htmlTemplate` is our Twig
template but `html` is `null`. The little detail is important: the email template is *not*
when our controller sends the message to the queue. Nope! the template isn't rendered until we run
`messenger:consume` command.

This is the problem. `messenger:consume` is a CLI command, and when generating absolute
URLs in these, Symfony doesn't know what the domain should be (or if it should
be http or https). So why does it when in a controller? In a controller, Symfony
can access the current request to get this information. A CLI command has no request
available so it defaults to `http://localhost`.

Changing this default is the solution!

Back in our IDE, open up `config/packages/routing.yaml`. Under `framework`, `routing`,
these comments are explaining this exact issue. Uncomment `default_uri` and set it to
`https://universal-travel.com` - our lawyers are close to a deal!

In development though, we need to use our local dev server's URL. For me, this is
`127.0.0.1:8000` but this could change or be customized by other developers on your
team.

Here's a trick: the Symfony CLI server sets a special environment variable with the
correct value that we can leverage.

Back in our routing config, add a new section: `when@dev:`, `framework:`, `router:`,
`default_uri:` and set this to `%env(SYMFONY_PROJECT_DEFAULT_ROUTE_URL)%`. This
environment variable will *only* be available if the Symfony CLI server is running
*and* you're running commands via `symfony console` (not `bin/console`). To ensure
we don't get an exception if this environment variable isn't set, let's set a default
value. Also under `when@dev`, add `parameters:` with `env(SYMFONY_PROJECT_DEFAULT_ROUTE_URL):`
set to `http://localhost` - the original default.

Let's try this out, but first, jump back to your terminal. Because we made some changes
to our config, we need to restart the `messenger:consume` command. Stop it with
`CTRL+C` and run it again:

```terminal
symfony console messenger:consume async -vv
```

Cool, those changes are now picked up. Back to our app... and book a trip! Quickly go
back to the terminal and we can see the message was processed.

Pop over to Mailtrap and... here it is! Moment of truth: click a link... Sweet, it's
working again!

If you're like me, you probably find having to keep this `messenger:consume` command
running in a terminal during development a drag. Plus, having to restart it every time
you make a code or config change is super annoying.

Here's another Symfony CLI trick: in your IDE, open this `.symfony.local.yaml` file.
This is the Symfony CLI server config for this project. Check this `workers` key, this
allows you to define additional processes to run in the background when you start the server.
We already have this tailwind command configured.

Add another worker for `messenger:consume`. Call it `messenger` and set the `cmd` to
`['symfony', 'console', 'messenger:consume', 'async']`. So this solves the issue
of having to keep this running in a separate terminal window but what about restarting?
Symfony CLI has you covered! Add a `watch` key and set it to the directories where you
have files that, when changed, should trigger a restart:
`config`, `src`, `templates` and `vendor`.

Back in your terminal, restart the server with `symfony server:stop` and `symfony serve -d`.
Now, our `messenger:consume` is running in the background. To prove it, run:

```terminal
symfony server:status
```

We see 3 workers running: the first is the actual PHP webserver. The second is our existing
`tailwind:build` worker, and the third is our new `messenger:consume` worker. I think this
is so cool!

Next, I want to show you how to make assertions about sent emails in your functional tests!
