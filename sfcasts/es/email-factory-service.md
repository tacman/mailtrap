# Servicio de fábrica de correos electrónicos

Nuestra aplicación envía dos correos electrónicos: en `SendBookingRemindersCommand`, y en`TripController::show()`. Aquí hay... mucha duplicación. ¡Me duele la vista! ¡Pero no te preocupes! Podemos reorganizar esto en un servicio de fábrica de correos electrónicos. Y como tenemos pruebas que cubren ambos correos, podemos refactorizar y estar seguros de que no hemos roto nada. No me canso de decirlo: ¡me encantan las pruebas!

Empieza creando una nueva clase: `BookingEmailFactory` en el espacio de nombres `App\Email`. Añade un constructor, copia el argumento `$termsPath` de `TripController::show()`, pégalo aquí y conviértelo en una propiedad privada.

Ahora, crea dos métodos de fábrica: `public function createBookingConfirmation()` `Booking $booking` , que aceptará , y devolverá `TemplatedEmail`. Luego,`public function createBookingReminder(Booking $booking)` también devolverá un `TemplatedEmail`.

Crea un método para albergar esa maldita duplicación: `private function createEmail()`, con argumentos `Booking $booking` y `string $tag` que devuelva un `TemplatedEmail`. Salta a `TripController::show()`, copia todo el código de creación de correos y pégalo aquí. Arriba, necesitamos dos variables: `$customer = $booking->getCustomer()` y`$trip = $booking->getTrip()`. Elimina `attachFromPath()`, `subject()`, y`htmlTemplate()`. En este `TagHeader`, utiliza la variable `$tag` pasada. Podemos dejar los metadatos igual. Por último, devuelve el `$email`.

Con nuestra lógica compartida, úsala en `createBookingConfirmation()`. Escribe`return $this->createEmail()`, pasando la variable `$booking` y `booking` para la etiqueta. Ahora, `->subject()`, copia esto de `TripController::show()`, cambiando la variable `$trip`por `$booking->getTrip()`. Por último, `->htmlTemplate('email/booking_confirmation.html.twig')`.

Para `createBookingReminder()`, copia el interior de `createBookingConfirmation()` y pégalo aquí. Cambia la etiqueta a `booking_reminder`, el asunto a `Booking Reminder`, y la plantilla a `email/booking_reminder.html.twig`.

¡Ahora viene lo divertido! ¡Usar nuestra fábrica y eliminar todo un montón de código!

En `TripController::show()`, en lugar de inyectar `$termsPath`, inyecta`BookingEmailFactory $emailFactory`. Elimina todo el código de creación de correo electrónico y dentro de `$mailer->send()`, escribe `$emailFactory->createBookingConfirmation($booking)`.

En `SendBookingRemindersCommand`, de nuevo, elimina todo el código de creación de correo electrónico. Arriba, en el constructor, autoconecta `private BookingEmailFactory $emailFactory`. Aquí abajo, dentro de `$this->mailer->send()`, escribe `$this->emailFactory->createBookingReminder($booking)`.

Oh sí, ¡qué bien me ha sentado! Pero, ¿hemos roto algo? Los canadienses tenemos fama de ser un poco salvajes. Compruébalo ejecutando las pruebas:

```terminal
bin/phpunit
```

¡Uh oh, un fallo! Menos mal que tenemos estas pruebas, ¿eh?

El fallo viene de `BookingTest`:

> El mensaje no incluye un archivo con nombre de archivo [Condiciones del servicio.pdf].

¡Fácil de arreglar! Durante nuestra refactorización, me olvidé de adjuntar el thriling terms PDF service al correo electrónico de confirmación de la reserva. Y nuestros clientes dependen de ello. Busca`BookingEmailFactory::createBookingConfirmation()`, y añade`->attachFromPath($this->termsPath, 'Terms of Service.pdf')`.

Vuelve a ejecutar las pruebas:

```terminal-silent
bin/phpunit
```

¡Pasadas! ¿Refactor exitoso? ¡Comprobado!

A continuación, cambiemos un poco de marcha y sumerjámonos en dos nuevos componentes Symfony para consumir los eventos webhook de correo electrónico de Mailtrap
