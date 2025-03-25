# Servicio de fábrica de correos electrónicos

Nuestra aplicación envía dos correos electrónicos: uno en nuestro `SendBookingRemindersCommand`, y otro en `TripController::show()`. Aquí hay... mucha duplicación, me hace daño a la vista. Quiero refactorizar esto en un servicio de fábrica de correos electrónicos y reducir la duplicación. Como tenemos pruebas que cubren ambos correos, podemos refactorizar y estar seguros de que no hemos roto nada. No me canso de decirlo: ¡me encantan las pruebas!

Empieza creando una nueva clase: `BookingEmailFactory` en el espacio de nombres `App\Email`. Añade un constructor, copia el argumento `$termsPath` de `TripController::show()`, pégalo aquí y conviértelo en una propiedad privada.

Ahora, haz un stub de nuestros dos métodos de fábrica: `public function createBookingConfirmation()` `Booking $booking` , que aceptará , y devolverá `TemplatedEmail`. Luego,`public function createBookingReminder(Booking $booking)`, que devolverá `TemplatedEmail`.

Crea un método privado para albergar la duplicación: `private function createEmail()`, con los argumentos `Booking $booking` y `string $tag`. Devuelve `TemplatedEmail`. Salta a `TripController::show()`, copia todo el código de creación del correo y pégalo aquí. Arriba, necesitamos dos variables: `$customer = $booking->getCustomer()` y`$trip = $booking->getTrip()`. Elimina `attachFromPath()`, `subject()`, y`htmlTemplate()`. En este `TagHeader`, utiliza la variable `$tag` pasada. Podemos dejar los metadatos igual. Por último, devuelve el `$email`.

Con nuestra lógica compartida, úsala en `createBookingConfirmation()`. Escribe`return $this->createEmail()`, pasando la variable `$booking` y `booking` para la etiqueta. Ahora, `->subject()`, copia esto de `TripController::show()`, cambiando la variable `$trip`por `$booking->getTrip()`. Ahora, `->htmlTemplate('email/booking_confirmation.html.twig')`.

Para `createBookingReminder()`, copia el interior de `createBookingConfirmation()` y pégalo aquí. Cambia la etiqueta a `booking_reminder`, el asunto a `Booking Reminder`, y la plantilla a `email/booking_reminder.html.twig`.

¡Ahora viene lo divertido! ¡Usar nuestra fábrica y eliminar todo un montón de código!

En `TripController::show()`, en lugar de inyectar `$termsPath`, inyecta`BookingEmailFactory $emailFactory`. Elimina todo el código de creación de correo electrónico y dentro de `$mailer->send()`, escribe `$emailFactory->createBookingConfirmation($booking)`.

Ahora, en `SendBookingRemindersCommand`, de nuevo, elimina todo el código de creación de correo electrónico. Arriba, en el constructor, inyecta `private BookingEmailFactory $emailFactory`. Aquí abajo, dentro de `$this->mailer->send()`, escribe `$this->emailFactory->createBookingReminder($booking)`.

Oh, sí, ¡qué bien! Pero, ¿hemos roto algo? Compruébalo ejecutando las pruebas:

```terminal
bin/phpunit
```

Uh oh, un fallo. Menos mal que tenemos estas pruebas, ¿eh?

El fallo se originó en nuestro `BookingTest` y el mensaje de fallo es:

> El mensaje no incluye el archivo con nombre de archivo [Condiciones del servicio.pdf].

¡Esto tiene fácil arreglo! Durante nuestra refactorización, olvidamos adjuntar las condiciones del servicio a nuestro correo electrónico de confirmación de la reserva. Vuelve a`BookingEmailFactory::createBookingConfirmation()`, y añade`->attachFromPath($this->termsPath, 'Terms of Service.pdf')`.

Vuelve a ejecutar las pruebas:

```terminal-silent
bin/phpunit
```

¡Aprobado! ¿Refactor exitoso? ¡Comprobado!

A continuación, cambiaremos un poco de marcha y utilizaremos dos nuevos componentes de Symfony para consumir los eventos de correo electrónico que Mailtrap desencadena en su extremo.
