# Real Email Styling with Inky & Foundation CSS

To get this email looking really sharp, we need to improve the HTML and
CSS.

Let's start with CSS. With standard website CSS, you've likely used
a CSS framework like Tailwind (which our app uses), Bootstrap, or Foundation.
Does something like this exist for emails? Yes! And it's even more important
to use one for emails because there are so many email clients that render
differently.

## Foundation CSS for Emails

For emails, we recommend using Foundation as it has a specific framework
for emails. Google "Foundation CSS" and you should find this page.

Download the starter kit for the "CSS Version". This zip file
includes a `foundation-emails.css` file that's
the actual "framework".

I already included this in the `tutorials/` directory. Copy it to
`assets/styles/`.

In our `booking_confirmation.html.twig`, the `inline_css` filter can take
multiple arguments. Make the first argument `source('@styles/foundation-emails.css')`
and use `email.css` for the second argument:

[[[ code('c203a20a6d') ]]]

This will contain custom styles and overrides.

I'll open `email.css` and paste in some custom CSS for our email:

[[[ code('ea52aa1065') ]]]

## Tables!

Now we need to improve our HTML. But weird news! Most of the things we use for
styling websites don't work in emails. For example, we can't use Flexbox or Grid.
Instead, we need to use tables for layout. Tables! Tables, inside tables, inside tables.
Gross!

## Inky Templating Language

Luckily, there's a templating language we can use to make this easier. Search for
"inky templating language" to find this page. Inky is developed by this
Zurb Foundation. Zurb, Inky, Foundation... these names fit in perfectly with our
space theme! And they all work together!

You can get an idea of how it works on the overview. This is the HTML needed for a
simple email. It's table-hell! Click the "Switch to Inky" tab. Wow! This is much
cleaner! We write in a more readable format and Inky converts it to the table-hell
needed for emails.

There are even "inky components": buttons, callouts, grids, etc.

In your terminal, install an Inky Twig filter that will convert our Inky markup to HTML.

```terminal
composer require twig/inky-extra
```

## `inky_to_html` Twig Filter

In `booking_confirmation.html.twig`, add the `inky_to_html`
filter to `apply`, piping `inline_css` after:

[[[ code('73775f33bd') ]]]

First, we apply the Inky filter, then inline the CSS.

I'll copy in some inky markup for our email.

[[[ code('fcbd92a5fb') ]]]

We have a `<container>`, with `<rows>` and
`<columns>`. This will be a single column email, but you can have as many columns as
you need. This `<spacer>` adds vertical space for breathing room.

Let's see this email in action! Book a new trip for Steve, oops, must be a date in the
future, and book!

Check Mailtrap and find the email. Wow! This looks much better! We can use this little
widget Mailtrap provides to see how it'll look on mobile and tablets. 

Looking at the "HTML Check", seems like we have some issues, but, I think as long
we're using Foundation and Inky as intended, we should be good.

Check the buttons. "Manage Booking", yep, that works. "My Account", yep, that works too.
That was a lot of quick success thanks to Foundation and Inky!

Next, let's improve our email further by embedding the trip image and
making the lawyers happy by adding a "terms of service" PDF attachment.
