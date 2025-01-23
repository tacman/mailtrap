# HTML-powered Emails

Emails should always have a plain-text version, but they can also have an HTML version.
And that's where the fun is! 
Time to make this email more presentable by adding an HTML version!

In `templates/email/`, copy `booking_confirmation.txt.twig` and name it `booking_confirmation.html.twig`.
In the new file
The email acts a bit like a full HTML page.
Wrap everything in an `<html>` tag, add an empty `<head>` and wrap
the content in a `<body>`. I'll also add some `<p>` tags to get some spacing...
and a `<br>` tag after "Regards," to add a line break.

This URL can now live in a proper `<a>` tag. Give yourself some room and copy the URL. Add an
`<a>` tag with `href` and paste inside.

Finally, we need to tell Mailer to use this HTML template. In `TripController::show()`,
above `->textTemplate()`, add `->htmlTemplate()` with `email/booking_confirmation.html.twig`.

Test it out by booking a trip: `Steve`, `steve@minecraft.com`, any date in the future,
book... then check Mailtrap. The email looks the same, but now we have an HTML tab!

Oh and the  "HTML Check" is really neat. It gives you a gauge of what percentage of email
clients support the HTML in this email. If you didn't know, email clients are a pain
in the butt: it's like the 90s all over again with different browsers. This tool helps
with that.
Back in the HTML tab, click the link to make
sure it works. It does!

So our email now has both a text and HTML version but... it's kind of a drag to maintain both.
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
