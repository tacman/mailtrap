# Attachments and Images

Time to add an attachment and image to our email! Doing this manually is a super
complex and delicate process. Luckily, the Symfony Mailer makes it a cinch.

First, we'll add a PDF as an attachment. In our `tutorial/` directory, you'll see
a `terms-of-service.pdf` file. Move this into our `assets/` directory.

In `TripController::show()`, we need to get the path to this file. Add a new
`string $termsPath` argument and add the `#[Autowire]` attribute with
`%kernel.project_dir%/assets/terms-of-service.pdf'`.

Down where we create the email, after `->subject()`, first, write `->attach` and
see what your IDE suggests. There are two methods: `attach()` and `attachFromPath()`.
`attach()` is for adding the raw content of a file (as a string or stream). Since
our attachment is a real file on our filesystem, use `attachFromPath()` and pass
`$termsPath` as the first argument and a friendly name like `'Terms of Service.pdf'`
as the second. If the second argument isn't passed, it defaults to the file's name.

Attachment done. That was easy!

Now, I want to add the trip image to the booking confirmation email. We don't want it
as an attachment though. We want it embedded in the HTML. There are two ways we can
do this: First, the standard web way: use an `<img>` tag with an absolute URL to the
image hosted on your site. But, we're going to be clever and embed the image
directly into the email. This is *like* an attachment, but isn't available to download
in email clients. Instead, you reference it in the HTML of your email.

First, like we did with our external CSS files, we need to make our images
available in Twig. `public/imgs` contains our trip images. They're all named as
`<trip-slug.png>`.

In `config/packages/twig.yaml`, add another `paths` entry:
`%kernel.project_dir%/public/imgs: images`. Now we can access this directory in Twig with
`@images/`. We can close this file.

## The `email` Variable

When you use Twig to render your emails, of course you have access to the variables
passed to `->context()` but there's also a secret variable available called `email`.
This is an instance of `WrappedTemplatedEmail` and gives you access to all the
email-related things like the subject, return path, from, to, etc. The thing we're
interested in is this `image()` method. This is what handles embedding images!

Let's use it!

In `booking_confirmation.html.twig`, below this `<h1>`, add an `<img>` tag with
some classes: `trip-image` from our custom CSS file and `float-center` from Foundation.

For the `src`, write `{{ email.image() }}`, this is the method on that
`WrappedTemplatedEmail` object. Inside, write `'@images/%s.png'|format(trip.slug)`.
Add an `alt="{{ trip.name }}"` and close the tag.

Image embedded! Let's make sure it works!

Back in the app, book a trip... and check Mailtrap. Here's our email and here's our
image. It looks great! Fits perfectly and even has some nice rounded corners.

Up here, in the top right, we see "Attachment (1)" - just like we expect. Click this and
choose "Terms of Service.pdf" to download it. Open it up and... there's our PDF!

Next, we're going to remove the need to manually set a `from` to each email by using
events to add it globally.
