# Prueba del comando CLI

¡El capitán está harto de que la gente corra detrás del cohete porque llegan tarde! ¡Por eso hemos creado un comando para enviar correos electrónicos recordatorios! Problema resuelto! Ahora escribamos una prueba para asegurarnos de que sigue funcionando. "Nueva función, nueva prueba", ¡ese es mi lema!

Salta a tu terminal y ejecuta:

```terminal
symfony console make:test
```

Teclea? `KernelTestCase`. ¿Nombre? `SendBookingRemindersCommandTest`.

En nuestro IDE, la nueva clase se añadió a `tests/`. Ábrelo y mueve la clase a un nuevo espacio de nombres: `App\Tests\Functional\Command`, para mantener las cosas organizadas.

Perfecto. Primero, limpia las tripas y añade algunos rasgos de comportamiento:`use ResetDatabase, Factories, InteractsWithMailer`. Elimina dos pruebas:`public function testNoRemindersSent()` con`$this->markTestIncomplete()` y`public function testRemindersSent()`. Márcalo también como incompleto.

De nuevo en el terminal, ejecuta las pruebas con:

```terminal
bin/phpunit
```

Compruébalo, nuestras dos pruebas originales pasan, los dos puntos, y estas íes son las nuevas pruebas incompletas. Me encanta esta pauta: escribe stubs de prueba para una nueva función, y luego juega a eliminar los incompletos uno a uno hasta que desaparezcan todos. Entonces, ¡la funcionalidad está terminada!

Symfony tiene algunas herramientas para probar comandos, pero me gusta usar un paquete que las envuelve en una experiencia más agradable. Instálalo con:

```terminal
composer require --dev zenstruck/console-test
```

Para activar los ayudantes de este paquete, añade un nuevo rasgo de comportamiento a nuestra prueba:`InteractsWithConsole`.

¡Ya estamos listos para derribar esos yoes!

La primera prueba es fácil: queremos asegurarnos de que, cuando no haya reservas que recordar, el comando no envíe ningún correo electrónico. Escribe`$this->executeConsoleCommand()` y sólo el nombre del comando: `app:send-booking-reminders`. Asegúrate de que el comando se ejecuta correctamente con `->assertSuccessful()` y`->assertOutputContains('Sent 0 booking reminders')`.

Pasamos a la siguiente prueba Ésta es más complicada: tenemos que crear una reserva que pueda recibir un recordatorio. Crea la reserva con`$booking = BookingFactory::createOne()`. Pasa un array con`'trip' => TripFactory::new()`, y dentro de éste, otro array con`'name' => 'Visit Mars'`, `'slug' => 'iss'` (para evitar el problema de la imagen). La reserva también necesita un cliente: `'customer' => CustomerFactory::new()`. Lo único que nos importa es el correo electrónico del cliente: `'email' => 'steve@minecraft.com'` por último, la fecha de la reserva: `'date' => new \DateTimeImmutable('+4 days')`.

¡Uf! Tenemos una reserva en la base de datos que necesita que se le envíe un recordatorio. El paso de configuración, u ordenación, de esta prueba está hecho.

Añade una preafirmación para asegurarte de que no se ha enviado un recordatorio a esta reserva:`$this->assertNull($booking->getReminderSentAt())`.

Ahora pasamos a la fase de actuación:`$this->executeConsoleCommand('app:send-booking-reminders')``->assertSuccessful()->assertOutputContains('Sent 1 booking reminders')` .

Pasa a la fase de aserción para asegurarte de que se ha enviado el correo electrónico. En `BookingTest`, copia la aserción del correo electrónico y pégala aquí. Haz algunos ajustes: el correo electrónico es `steve@minecraft.com`, el asunto es `Booking Reminder for Visit Mars`y este correo no tiene ningún adjunto, así que elimina esa aserción por completo.

Por último, escribe una aserción que diga que el comando actualizó la reserva en la base de datos.`$this->assertNotNull($booking->getReminderSentAt())`.

¡El momento de la verdad! Ejecuta las pruebas:

```terminal-silent
bin/phpunit
```

¡Todo en verde!

Considero que este tipo de pruebas "outside-in" son muy divertidas y fáciles de escribir, porque no tienes que preocuparte demasiado de probar la lógica interna e imitan la forma en que un usuario interactúa con tu aplicación. No es casualidad que las afirmaciones se centren en lo que el usuario debería ver y en algunas comprobaciones de alto nivel posteriores a la interacción, como comprobar algo en la base de datos.

Ahora que tenemos pruebas para nuestras dos rutas de envío de correo electrónico, demos una vuelta de la victoria y refactoricemos con confianza para eliminar la duplicación.
