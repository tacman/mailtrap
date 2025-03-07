# Correo electrónico desde el comando CLI

Ya hemos hecho el trabajo previo para nuestra función de correo electrónico recordatorio. Ahora, ¡vamos a crear y enviar los correos!

En `templates/email`, la nueva plantilla de correo electrónico será muy similar a`booking_confirmation.html.twig`. Copia ese archivo y nómbralo `booking_reminder.html.twig`. En el interior, no quiero perder demasiado tiempo en esto, así que simplemente cambia el título del acento para que diga "¡Próximamente!". ¡Envíalo! ¡Juego de palabras espacial accidental!

La lógica para enviar los correos electrónicos tiene que ser algo que podamos programar para que se ejecute cada hora o cada día. ¡El trabajo perfecto para un comando CLI! En tu terminal, ejecuta:

```terminal
symfony make:command
```

¡Bah!

```terminal
symfony console make:command
```

Llámalo: `app:send-booking-reminders`.

¡Ve a comprobarlo! `src/Command/SendBookingRemindersCommand.php`. Cambia la descripción a "Enviar correos electrónicos de recordatorio de reserva". 

En el constructor, autocablea y establece propiedades para `BookingRepository`, `EntityManagerInterface`y `MailerInterface`.

Este comando no necesita argumentos ni opciones, así que elimina por completo el método `configure()`.

Limpia las tripas de `execute()`. Empieza añadiendo un bonito:`$io->title('Sending booking reminders')`. Luego, coge las reservas que necesitan que se envíen recordatorios, con `$bookings = $this->bookingRepo->findBookingsToRemind()`.

Para ser los mejores, mostremos una barra de progreso mientras hacemos un bucle sobre las reservas. El objeto `$io` tiene un truco para esto en la manga. Escribe `foreach ($io->progressIterate($bookings) as $booking)`. Esto se encarga de toda la aburrida lógica de la barra de progreso Dentro, tenemos que crear un nuevo correo electrónico. En `TripController`, copia ese correo electrónico -incluyendo estas cabeceras- y pégalo aquí.

Pero tenemos que ajustarlo un poco: elimina el archivo adjunto. Y para el asunto: sustituye "Confirmación" por "Recordatorio". Arriba, añade algunas variables por comodidad:`$customer = $booking->getCustomer()` y `$trip = $booking->getTrip()`. Aquí abajo, mantén los mismos metadatos, pero cambia la etiqueta a `booking_reminder`. Esto nos ayudará a distinguir mejor estos correos en Mailtrap.

Ah, y por supuesto, cambia la plantilla a `booking_reminder.html.twig`.

Siguiendo con el bucle, envía el correo electrónico con `$this->mailer->send($email)` y marca la reserva como recordatorio enviado con`$booking->setReminderSentAt(new \DateTimeImmutable('now'))`.

¡Perfecto! Fuera del bucle, llama a `$this->em->flush()` para guardar los cambios en la base de datos. Por último, celébralo con`$io->success(sprintf('Sent %d booking reminders', count($bookings)))`.

¡Hora de probar! Ve a tu terminal. Para asegurarte de que tenemos una reserva que necesita que se le envíe un recordatorio, vuelve a cargar los accesorios con:

```terminal
symfony console doctrine:fixture:load
```

Ahora, ¡ejecuta nuestro nuevo comando!

```terminal
symfony console app:send-booking-reminders
```

Bien, ¡se ha enviado 1 recordatorio! Y el resultado impresionará a nuestros colegas! Antes de comprobar Mailtrap, vuelve a ejecutar el comando:

```terminal-silent
symfony console app:send-booking-reminders
```

"Enviados 0 recordatorios de reserva". ¡Perfecto! Nuestra lógica para marcar las reservas como recordatorios enviados ¡funciona!

Ahora comprueba Mailtrap... ¡aquí está! Como era de esperar, tiene un aspecto muy similar a nuestro correo de confirmación, pero aquí dice "¡Próximamente!": está utilizando la nueva plantilla.

Cuando se utiliza "Prueba de Mailtrap", las etiquetas y metadatos de Mailer no se convierten en categorías y variables personalizadas de Mailtrap, como ocurre cuando se envían en producción. Pero puedes asegurarte de que se envían Haz clic en esta pestaña "Información técnica" y desplázate un poco hacia abajo. Cuando Mailer no sabe cómo convertir las etiquetas y los metadatos, los añade como estas cabeceras genéricas personalizadas: `X-Tag` y `X-Metadata`.

Efectivamente, `X-Tag` es `booking_reminder`. Genial, ¡eso es lo que esperamos también!

Vale, ¿nueva función? ¡Comprobado! ¿Pruebas para la nueva función? ¡Eso a continuación!
