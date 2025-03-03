# Generating URLs in the CLI Environment

When we switched to asynchronous email sending, we broke our email links! It's using
`localhost` as our domain, weird and wrong.

Back in our app, we can get a hint as to what's going on by looking at the
profiler for the request that sent the email. Remember, our email is now marked as
"queued". Go to the "Messages" tab and find the message: `SendEmailMessage`. Inside
is the `TemplatedEmail` object. Open this up. Interesting! `htmlTemplate` is our Twig
template but `html` is `null`! Shouldn't that be set to the rendered HTML from that
template?
This little detail is important: the email template is *not* rendered when our
controller sends the message to the queue. Nope! the template isn't rendered until
later, when we run `messenger:consume`.

## Link Generation in the CLI

Why does this matter? Well `messenger:consume` is a CLI command, and when generating absolute
URLs in the CLI, Symfony doesn't know what the domain should be (or if it should
be http or https). So why does it when in a controller? In a controller, Symfony
uses the current request to figure this out. In a CLI command, there is no request
so it gives up and uses `http://localhost`.

## Configure the Default URL

Let's just tell it what the domain should be.

Back in our IDE, open up `config/packages/routing.yaml`. Under `framework`, `routing`,
these comments explain this exact issue. Uncomment `default_uri` and set it to
`https://universal-travel.com` - our lawyers are close to a deal!

[[[ code('96515c92b8') ]]]

In development though, we need to use our local dev server's URL. For me, this is
`127.0.0.1:8000` but this might be different for other team members. I know
that Bob uses `bob.is.awesome:8000` and he kinda is.

## Development Environment Default URL

To make this configurable, there's a trick: the Symfony CLI server sets a special
environment variable with the domain called `SYMFONY_PROJECT_DEFAULT_ROUTE_URL`.

Back in our routing config, add a new section: `when@dev:`, `framework:`, `router:`,
`default_uri:` and set it to `%env(SYMFONY_PROJECT_DEFAULT_ROUTE_URL)%`:

[[[ code('227c9b313b') ]]]

This environment variable will *only* be available if the Symfony CLI server is running
*and* you're running commands via `symfony console` (not `bin/console`). To avoid
an error if the variable is missing, set a default. Still under `when@dev`, add
`parameters:` with `env(SYMFONY_PROJECT_DEFAULT_ROUTE_URL):`
set to `http://localhost`.

[[[ code('21cc59fd3f') ]]]

This is Symfony's standard way to set a default value for an environment variable.

## Restart `messenger:consume`

Testing time! But first, jump back to your terminal. Because we made some changes
to our config, we need to restart the `messenger:consume` command to, sort of, reload
our app:

```terminal-silent
symfony console messenger:consume async -vv
```

Cool! The command is running again and using our sweet new Symfony config.
Head back to our app... and book a trip! Quickly go
back to the terminal... and we can see the message was processed.

Pop over to Mailtrap and... here it is! Moment of truth: click a link... Sweet, it's
working again! Bob will be so happy!

## Running `messenger:consume` in the Background

If you're like me, you probably find having to keep this `messenger:consume` command
running in a terminal during development a drag. Plus, having to restart it every time
you make a code or config change is annoying. I'm annoyed! Time to add the fun back
to our functions with another Symfony CLI trick!

In your IDE, open this `.symfony.local.yaml` file.
This is the Symfony CLI server config for our app. See this `workers` key? It lets
us define processes to run in the background when we start the server.
We already have the tailwind command set.

Add another worker. Call it `messenger` - though that could be anything - and set
`cmd` to `['symfony', 'console', 'messenger:consume', 'async']`:

[[[ code('1bcb5bb279') ]]]

This solves the issue
of needing to keep this running in a separate terminal window.
But what about restarting the command when we make changes? No problemo!
Add a `watch` key and set it to `config`, `src`, `templates` and `vendor`:

[[[ code('f26e46ee9b') ]]]

If any files in these directories change, the worker will restart itself.
Smart!

Back in your terminal, restart the server with `symfony server:stop` and `symfony serve -d`
`messenger:consume` *should* be running in the background! To prove it, run:

```terminal
symfony server:status
```

3 workers running! The actual PHP webserver, the existing
`tailwind:build` worker, and our new `messenger:consume`.
So cool!

Next, let's explore how to make assertions about emails in our functional tests!
