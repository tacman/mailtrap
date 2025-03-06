# Correo electrónico desde el comando CLI

Ya hemos hecho el trabajo previo para nuestra función de correo electrónico recordatorio. Ahora, ¡vamos a crear y enviar los correos!

En primer lugar, en `templates/email`, la nueva plantilla de correo electrónico será muy similar a`booking_confirmation.html.twig`. Así que, copia ese archivo y nómbralo `booking_reminder.html.twig`. Dentro, no quiero perder demasiado tiempo en esto, así que simplemente cambia el título del acento para que diga "¡Próximamente!". Ya está bien.

La lógica para enviar los correos electrónicos tiene que ser algo que podamos programar para que se ejecute cada hora o cada día. ¡El trabajo perfecto para un comando CLI! En tu terminal, ejecuta:

```terminal
symfony make:command
```

¡Bah!

```terminal
symfony console make:command
```

Llámalo: `app:send-booking-reminders`.

¡Vamos a comprobarlo! Abre `src/Command/SendBookingRemindersCommand.php`. Primero, cambia la descripción del comando por "Enviar correos electrónicos de recordatorio de reserva". Después, inyecta los siguientes servicios en el constructor: necesitamos encontrar las reservas para las que hay que enviar recordatorios, así que `private BookingRepository $bookingRepo`. Tendremos que actualizar el indicador de recordatorio en las reservas, así que `private EntityManagerInterface $em`. Y, por supuesto, necesitamos enviar el correo electrónico, así que `private MailerInterface $mailer`.

No necesitamos argumentos ni opciones para este comando, así que elimina por completo el método `configure()`.

Limpia las tripas de `execute()`. Empieza por añadir un bonito título a la salida:`$io->title('Sending booking reminders')`. Después, coge las reservas que necesitan que se envíen recordatorios, con `$bookings = $this->bookingRepo->findBookingsToRemind()`.

Quiero mostrar una barra de progreso para iterar sobre estas reservas. `$io` tiene un truco bajo la manga. Escribe `foreach ($io->progressIterate($bookings) as $booking)`. Esto se encarga de toda la aburrida lógica de salida de la barra de progreso por nosotros Dentro, tenemos que crear un nuevo correo electrónico. Será muy similar al que creamos en `TripController`, así que cópialo, incluyendo estas cabeceras, y pégalo aquí.

Tenemos que ajustarlo un poco. Elimina el archivo adjunto. El asunto: sustituye "Confirmación" por "Recordatorio". Arriba, añade estas variables por comodidad:`$customer = $booking->getCustomer()` y `$trip = $booking->getTrip()`. Aquí abajo, podemos mantener los mismos metadatos, pero cambiar la etiqueta a `booking_reminder`. Esto nos ayudará a distinguir mejor estos correos en Mailtrap.

Ah, y por supuesto, tenemos que cambiar la plantilla a `booking_reminder.html.twig`.

Siguiendo con el bucle, envía el correo electrónico con `$this->mailer->send($email)` y marca la reserva como recordatorio enviado con`$booking->setReminderSentAt(new \DateTimeImmutable('now'))`.

¡Perfecto! Fuera del bucle, llama a `$this->em->flush()` para guardar los cambios en la base de datos. Por último, añade un mensaje de éxito:`$io->success(sprintf('Sent %d booking reminders', count($bookings)))`.

Veamos si funciona, pasa a tu terminal. Para asegurarte de que tenemos una reserva que necesita que se le envíe un recordatorio, vuelve a cargar los accesorios con:

```terminal
symfony console doctrine:fixture:load
```

Ahora, ¡ejecuta nuestro nuevo comando!

```terminal
symfony console app:send-booking-reminders
```

Bien, ¡se ha enviado 1 recordatorio! Aquí tenemos nuestro título, una genial barra de progreso y el mensaje de éxito. Antes de comprobar Mailtrap, vuelve a ejecutar el comando:

```terminal-silent
symfony console app:send-booking-reminders
```

"Enviados 0 recordatorios de reserva". ¡Perfecto! Nuestra lógica para marcar las reservas como recordatorios enviados ¡funciona!

Ahora comprueba Mailtrap... ¡aquí está nuestro correo electrónico recordatorio! Como era de esperar, tiene un aspecto muy similar a nuestro correo de confirmación, pero aquí dice "Próximamente": está utilizando la nueva plantilla.

Cuando se utiliza "Prueba de Mailtrap", las etiquetas y metadatos de Mailer no se convierten en categorías y variables personalizadas de Mailtrap, como ocurre cuando se envían en producción. No obstante, ¡puedes confirmar que se envían! Haz clic en la pestaña "Información técnica" y desplázate un poco hacia abajo. Cuando Mailer no sabe cómo convertir las etiquetas y los metadatos, los añade como estas cabeceras genéricas personalizadas: `X-Tag` y `X-Metadata`.

Efectivamente, `X-Tag` es `booking_reminder`. Genial, ¡también es lo que esperamos!

Vale, ¿nueva función? ¡Comprobado! ¿Probar la nueva función? ¡Eso a continuación!
