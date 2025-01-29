# HTML With Inky & Foundation for Emails

To get this email looking really sharp, we're going to need to improve the HTML and
CSS.

Let's start with CSS. With standard website CSS, you've likely used, or at least
heard of CSS frameworks. Popular ones are Tailwind (which our app used), Bootstrap, and Foundation.
These normalize the look of your site and make it easier to create a consistent
experience for desktop and mobile users. I'd argue it's even more important
to use a framework for emails as there are many more email clients.

Foundation CSS has an email-specific CSS framework.

If you Google "Foundation CSS email", you should find this page. The easiest way to
get started is to download the starter kit for the "CSS Version". This zip file
includes some examples, but most importantly, a `foundation-emails.css` file that's
the actual "framework".

I have this already downloaded in the `tutorials/` directory so copy it to your
`assets/styles` directory.

In our `booking_confirmation.html.twig` template, this `inline_css` filter can take
multiple arguments. Make the first argument `source(@styles/foundation-emails.css)`.
`email.css` will be the second argument and will contain custom styles and overrides.

Open `email.css` and paste in some custom CSS for our email.

Now, we need better HTML but... writing good-looking HTML for emails is a bit of a
nightmare. For a nice layout, you need to use tables, inside tables, inside tables.
Gross!

Luckily, there's a special templating language we can use to make this easier. Google
"inky templating language" and you should find this page. Inky is developed by this
Zurb Foundation. Zurb, Inky, Foundation... these are all really cool sci-fi names!
I love it! Inky works seamlessly with Foundation for Emails.

You can get an idea of how it works on the overview. This is the HTML needed for a
simple email. It's table-hell! If you click this "Switch to Inky" tab, you can see
how Inky simplifies this. It's a lot cleaner and easier to read.

There's a bunch of "inky components" you can use: buttons, callouts, grids, etc.

Let's use it!

First, in your terminal, install an Inky filter for Twig with:

```terminal
composer require twig/inky-extra
```

We're ready to use Inky! In `booking_confirmation.html.twig`, add the `inky_to_html`
filter to `apply`, piping `inline_css` after. First, we apply the Inky filter, then
inline the CSS.

I'll copy in some inky markup for our email. We have a `<container>`, with `<rows>` and
`<columns>`. This will be a single column email, but you can have multiple columns if
you'd like. This `<spacer>` is the inky way to add vertical space for breathing room.

We have basically the same content as before but with some custom classes from `email.css`.
The first `<button>` is our link to manage the booking. This second `<button>` is a link
to a page that lists all your bookings.

Let's see this email in action! Book a new trip for Steve, oops, must be a date in the
future, and book!

Check Mailtrap and find the email. Wow! This looks much better! We can use this little
widget Mailtrap provides to see how it'll look on mobile and tablets. 

Looking at the "HTML Check", seems like we have some issues, but, I think as long
we're using Foundation and Inky as intended, we should be good.

Check the buttons. "Manage Booking", yep, that works. "My Account", yep, that works too.

I'm happy with this look. Next, let's improve our email further by embedding the trip image and
adding a "terms of service" PDF attachment to the email.
