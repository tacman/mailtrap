# CSS in Email

CSS in email requires some special care. Let's start naively and work from there.

In `email/booking_confirmation.html.twig`, add a `<style>` tag in the `<head>` and
add a `.text-red` class that sets the `color` to `red`. Now, add this class to the first
`<p>` tag.

In our app, book another trip for our good friend Steve. In Mailtrap, check the email.
Ok, this text is red like we expect - so what's the problem? Check the HTML Source for
a hint. Hover over this first error: the `style` tag is not supported in all email
clients. The more important problem is the `class` attribute: it's also not supported
in all email clients. The solution?

We need to inline our styles. For every tag that has a `class`, we need to find all
the styles applied from the class and add them to a `style` attribute. Manually,
this would suuuuck... Luckily, Symfony Mailer has you covered!

At the top of this file, add a Twig `apply` tag with the `inline_css` filter. If you're
unfamiliar, the `apply` tag allows you to apply any Twig filter to a block of content. At
the end of the file, add an `endapply` tag.

Book another trip for Steve. Oops, an error! The `inline_css` filter is part of a package
we don't have installed but the error message gives us the `composer require` command we
need to install it! Copy the command, jump over to your terminal and paste:

```terminal
composer require twig/cssinliner-extra
```

Back in the app, rebook Steve's trip and check the email in Mailtrap.

The HTML looks the same but check the HTML Source. This `style` attribute was automatically
added to the `<p>` tag! Phew, waaay better than doing this manually!

If your app sends multiple emails, you'll likely want them to have a consistent style from
an *external* CSS file. We can't simply link to a CSS file like we can in a normal HTML
webpage.

First, create a new `email.css` file in `assets/styles/`. Copy the CSS from the email template
and paste into this file. Back in the template, remove the `<style>` tag - we don't need
it anymore.

How can we get our email to use our external CSS file? The `inline_css` filter can take
raw CSS as an argument. To do this, we need to make the `assets/styles/` directory a Twig path
so we can access its files in Twig.

Open `config/packages/twig.yaml` and add a `paths` key. Inside, add
`%kernel.project_dir%/assets/styles: styles`. This makes the `assets/styles/` directory
available in our Twig templates (with the `@styles` prefix). Wait a minute, our `email.css`
file isn't a twig template! That's ok, we just want to access it, not parse it as Twig.

Back in `booking_confirmation.html.twig`, for `inline_css`'s argument, use
`source('@styles/email.css')`. The `source()` function just grabs the raw content of
a *template*.

Jump to our app, book another trip and check the email in Mailtrap. Looks the same, the
text here is red. If we check the HTML Source, the classes are no longer in the `<head>`
but the styles are still inlined. They're now being loaded from our external style sheet,
perfect!

Next, let's improve the HTML and CSS to make this email pop out a bit more.
