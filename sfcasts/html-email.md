# HTML in Email

Time to make this email more presentable by adding an HTML version!

First, we need to make an HTML version of our email template. In `templates/email/`,
copy `booking_confirmation.txt.twig` and name it `booking_confirmation.html.twig`.
In this new file, wrap everything in an `<html>` tag, add an empty `<head>` and wrap
the content in a `<body>` tag. Now, wrap each line of content in `<p>` tags. For the
last lines, wrap it all in a `<p>` tag and add a `<br>` after "Regards,". This URL
needs to be a proper anchor tag, give ourselves some room and copy the text. Add an
`<a>` tag with the URL as the `href` and paste the text inside.

We need to tell our email to use this HTML template. In `TripController::show()`,
above `->textTemplate()`, add `->htmlTemplate()` with `email/booking_confirmation.html.twig`.

Test it out by booking a trip: `Steve`, `steve@minecraft.com`, any date in the future,
and book.

Check Mailtrap to see the email. The HTML tab is now available. This looks like I expect.
The text version is still there and we can see the "HTML Source".
This "HTML Check" is really neat. It gives you a gauge of what percentage of email
clients support the HTML in this email. Back in the HTML tab, click the link to make
sure it works - and it does!

So our email now has text and HTML versions but... it's kind of a drag to maintain both.
Who uses a text-only email client anyway? Probably nobody or a very low percentage of your
users.

Let's try something: in `TripController::show()`, remove the `->textTemplate()` line.
Our email now only has an HTML version.

Book another trip and check the email in Mailtrap. We still have a text version? It
looks almost like our text template but with some extra spacing. If you send an email
with just an HTML version, Symfony Mailer automatically creates a text version but strips the
tags. This is a nice fallback, but it's not perfect. See what's missing? The link! That's...
kind of critical... The link is gone because it was in the `href` attribute of the
anchor tag. We lost it when the tags were stripped.

So, do we need to always manually maintain a text version? Not necessarily. Here's a
little trick.

Over in your terminal, run:

```terminal
composer require league/html-to-markdown
```

This is a package that converts HTML to markdown. Wait, what? Don't we usually convert
markdown to HTML? Yes, but for HTML emails, this is perfect! And guess what? There's
nothing else we need to do! Symfony Mailer automatically uses this package instead of
just stripping tags if available!

Book yet another trip and check the email in Mailtrap. The HTML looks the same, but check
the text version. Our anchor tag has been converted to a markdown link! It's not perfect,
but at least it's there! If you need full control, you'll need that separate text template,
but, I think this is good enough. Back in your IDE, delete `booking_confirmation.txt.twig`.

Next, we'll spice up this HTML with CSS!
