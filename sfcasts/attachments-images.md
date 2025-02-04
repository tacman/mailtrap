# Attachments and Images

Can we add an attachment to our email? Of course! Doing this manually is a
complex and delicate process. Luckily, the Symfony Mailer makes it a cinch.

In the `tutorial/` directory, you'll see
a `terms-of-service.pdf` file. Move this into `assets/`, though it could live anywhere.

In `TripController::show()`, we need to get the path to this file. Add a new
`string $termsPath` argument and with the `#[Autowire]` attribute and
`%kernel.project_dir%/assets/terms-of-service.pdf'`.

Cool, right?

Down where we create the email, write `->attach` and
see what your IDE suggests. There are two methods: `attach()` and `attachFromPath()`.
`attach()` is for adding the raw content of a file (as a string or stream). Since
our attachment is a real file on our filesystem, use `attachFromPath()` and pass
`$termsPath` then a friendly name like `Terms of Service.pdf`. This will be the
name of the file when it's downloaded.
If the second argument *isn't* passed, it defaults to the file's name.

Attachment done. That was easy!

Next, let's add the trip image to the booking confirmation email. But we don't want it
as an attachment. We want it embedded in the HTML. There are two ways to
do this: First, the standard web way: use an `<img>` tag with an absolute URL to the
image hosted on your site. But, we're going to be clever and embed the image
directly into the email. This is *like* an attachment, but isn't available for download
Instead, you reference it in the HTML of your email.

First, like we did with our external CSS files, we need to make our images
available in Twig. `public/imgs/` contains our trip images and they're all named as
`<trip-slug.png>`.

In `config/packages/twig.yaml`, add another `paths` entry:
`%kernel.project_dir%/public/imgs: images`. Now we can access this directory in Twig with
`@images/`. Close this file.

## The `email` Variable

When you use Twig to render your emails, of course you have access to the variables
passed to `->context()` but there's also a secret variable available called `email`.
This is an instance of `WrappedTemplatedEmail` and it gives you access to
email-related things like the subject, return path, from, to, etc. The thing we're
interested in is this `image()` method. This is what handles embedding images!

Let's use it!

In `booking_confirmation.html.twig`, below this `<h1>`, add an `<img>` tag with
some classes: `trip-image` from our custom CSS file and `float-center` from Foundation.

For the `src`, write `{{ email.image() }}`, this is the method on that
`WrappedTemplatedEmail` object. Inside, write `'@images/%s.png'|format(trip.slug)`.
Add an `alt="{{ trip.name }}"` and close the tag.

Image embedded! Let's check it!

Back in the app, book a trip... and check Mailtrap. Here's our email and... here's our
image! We rock! It fits perfectly and even has some nice rounded corners.

Up here, in the top right, we see "Attachment (1)" - just like we expect. Click this and
choose "Terms of Service.pdf" to download it. Open it up and... there's our PDF!
Our space lawyers actually made this document fun - and it only cost us 500 credits/hour!
Investor credits well spent!

Next, we're going to remove the need to manually set a `from` to each email by using
events to add it globally.
