# CSS in Email

CSS in email requires... some special care. But, pffff, we're Symfony developers!
Let's recklessly go forward and see what happens!

## Add a CSS Class

In `email/booking_confirmation.html.twig`, add a `<style>` tag in the `<head>` and
add a `.text-red` class that sets the `color` to `red`:

[[[ code('46cf9f4ee1') ]]]

Now, add this class to the first `<p>` tag:

[[[ code('c04f312da2') ]]]

In our app, book another trip for our good friend Steve. He's really
racking up the parsecs! Do you think he'd be interested in the 
platinum Universal Travel credit card?

In Mailtrap, check the email. Ok, this text is red like we expect... so what's the problem?
Check the HTML Source for a hint. Hover over the first error:

> The `style` tag is not supported in all email clients.

The more important problem is the `class` attribute: it's also not supported
in all email clients. We can travel to space but can't use CSS classes in emails?
Yup! It's a strange world.

## Inline CSS

The solution? Pretend like it's 1999 and inline all the styles. That's right,
for every tag that has a `class`, we need to find all
the styles applied from the class and add them as a `style` attribute. Manually,
this would suuuuck... Luckily, Symfony Mailer has you covered!

## `inline_css` Twig Filter

At the top of this file, add a Twig `apply` tag with the `inline_css` filter. If you're
unfamiliar, the `apply` tag allows you to apply any Twig filter to a block of content. At
the end of the file, write `endapply`:

[[[ code('04b6a30406') ]]]

Book another trip for Steve. Oops, an error! The `inline_css` filter is part of a package
we don't have installed but the error message gives us the `composer require` command
to install it! Copy that, jump over to your terminal and paste:

```terminal
composer require twig/cssinliner-extra
```

Back in the app, rebook Steve's trip and check the email in Mailtrap.

The HTML looks the same but check the HTML Source. This `style` attribute was automatically
added to the `<p>` tag! That's amazing and *way* better than doing it manually.

If your app sends multiple emails, you'll want them to have a consistent style from
a real CSS file, instead of defining everything in a `<style>` tag in each template.
Unfortunately, it's not as simple as linking to a CSS file in the `<head>`. That's
something else that email clients don't like.

No problem!

## External CSS File

Create a new `email.css` file in `assets/styles/`. Copy the CSS from the email template
and paste it here:

[[[ code('499a01fe89') ]]]

Back in the template, celebrate by removing the `<style>` tag.

So how can we get our email to use the external CSS file? With trickery of course!

## Twig "styles" Namespace

Open `config/packages/twig.yaml` and create a `paths` key. Inside, add
`%kernel.project_dir%/assets/styles: styles`:

[[[ code('36096b9757') ]]]

I know, this looks weird, but it
creates a custom Twig namespace. Thanks to this we can now render templates inside
this directory with the `@styles/` prefix. But wait a minute! `email.css`
file isn't a twig template that we want to render! That's ok, we just need to *access*
it, not parse it as Twig.

## `inline_css()` with `source()`

Back in `booking_confirmation.html.twig`, for `inline_css`'s argument, use
`source('@styles/email.css')`:

[[[ code('1bdadb199f') ]]]

The `source()` function grabs the raw content of a file.

Jump to our app, book another trip and check the email in Mailtrap. Looks the same! The
text here is red. If we check the HTML Source, the classes are no longer in the `<head>`
but the styles *are* still inlined: they're being loaded from our external style sheet,
it's brilliant!

Up next, let's improve the HTML and CSS to make this email worthy of Steve's inbox
and the expensive trip he just booked.
