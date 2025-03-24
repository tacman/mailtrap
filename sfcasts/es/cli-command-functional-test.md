# Prueba del comando CLI

Muy bien, ya tenemos nuestro comando de envío de recordatorios de reserva, y sabemos que funciona, así que escribamos una prueba para asegurarnos de que sigue funcionando. "Nueva función, nueva prueba", ¡ese es mi lema!

Salta a tu terminal y ejecuta:

```terminal
symfony console make:test
```

Teclea? `KernelTestCase`. ¿Nombre? `SendBookingRemindersCommandTest`.

En nuestro IDE, la nueva clase se añadió a `tests/`. Ábrelo y mueve la clase a un nuevo espacio de nombres: `App\Tests\Functional\Command`, para mantener las cosas organizadas.

Perfecto. Primero, limpia las tripas y añade algunos rasgos de comportamiento:`use ResetDatabase, Factories, InteractsWithMailer`. Escribiremos dos pruebas, así que hazlas: `public function testNoRemindersSent()` dentro de:`$this->markTestIncomplete()`. Ahora el stub de la segunda prueba:`public function testRemindersSent()` y márcalo también como incompleto.

De nuevo en el terminal, ejecuta las pruebas con:

```terminal
bin/phpunit
```

Compruébalo, nuestras dos pruebas originales pasan, los dos puntos y estas íes son nuestras nuevas pruebas incompletas. Me encanta este patrón: escribir stubs de pruebas para una nueva funcionalidad, y luego jugar a eliminar los incompletos uno a uno hasta que desaparezcan todos, ¡entonces mi funcionalidad está terminada!

Symfony tiene algunas herramientas para probar comandos, pero me gusta usar un paquete que las envuelve en una experiencia más agradable. Instálalo con:

```terminal
composer require --dev zenstruck/console-test
```

Para activar los ayudantes de este paquete, añade un nuevo rasgo de comportamiento a nuestra prueba:`InteractsWithConsole`.

¡Ya estamos listos para derribar esos yoes!

La primera prueba es fácil: queremos asegurarnos de que cuando no haya reservas que recordar, el comando no envíe ningún correo electrónico. Escribe`$this->executeConsoleCommand()` y sólo el nombre del comando: `app:send-booking-reminders`. Asegúrate de que el comando se ejecuta correctamente con `->assertSuccessful()` y`->assertOutputContains('Sent 0 booking reminders')`.

Pasamos a la siguiente prueba Ésta será un poco más complicada: tenemos que crear una reserva a la que enviar un recordatorio. Crea el accesorio de reserva con`$booking = BookingFactory::createOne()`. Dentro, un array con`'trip' => TripFactory::new()`, y dentro de éste, otro array con`'name' => 'Visit Mars'`, `'slug' => 'iss'` (para evitar el problema de la imagen). La reserva también necesita un cliente: `'customer' => CustomerFactory::new()`. Lo único que nos importa es el correo electrónico del cliente: `'email' => 'steve@minecraft.com'` por último, la fecha de la reserva: `'date' => new \DateTimeImmutable('+4 days')`.

¡Uf! Tenemos una reserva en la base de datos que necesita que se le envíe un recordatorio. El paso de configuración, u ordenación, de esta prueba está hecho.

Añade una preafirmación para asegurarte de que no se ha enviado un recordatorio a esta reserva:`$this->assertNull($booking->getReminderSentAt())`.

Ahora pasamos a la fase de actuación:`$this->executeConsoleCommand('app:send-booking-reminders')``->assertSuccessful()->assertOutputContains('Sent 1 booking reminders')` .

Pasa a la fase de aserción para asegurarte de que se ha enviado el correo electrónico. En `BookingTest`, copia la aserción del correo electrónico y pégala aquí. Haz algunos ajustes: el correo electrónico es `steve@minecraft.com`, el asunto es `Booking Reminder for Visit Mars`y este correo no tiene ningún adjunto, así que elimina esa aserción por completo.

Por último, escribe una aserción para el opuesto de nuestra preaserción:`$this->assertNotNull($booking->getReminderSentAt())`. Esto garantiza que el comando actualizó la reserva en la base de datos.

¡Momento de la verdad! Ejecuta las pruebas:

```terminal-silent
bin/phpunit
```

¡Sí! ¡Todo en verde!

Considero que este tipo de pruebas "outside-in" son muy divertidas y fáciles de escribir, porque no tienes que preocuparte demasiado de probar la lógica interna. Imitan más de cerca cómo interactuaría un usuario con tu aplicación. Las afirmaciones de estas pruebas se centran en lo que el usuario debería ver y en el estado de alto nivel posterior a la interacción.

Ahora que tenemos pruebas para nuestras dos rutas de envío de correo electrónico, vamos a refactorizar con confianza para eliminar la duplicación.
