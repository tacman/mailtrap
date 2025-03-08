# Diseño Twig de correo electrónico

¡Hora de una nueva función! Quiero enviar un correo electrónico recordatorio a los clientes 1 semana antes de su viaje reservado. ¡T menos 1 semana para despegar gente!

Pero antes, tenemos un pequeño problema con nuestro trabajador CLI Symfony. Abre`.symfony.local.yaml`. Nuestro trabajador `messenger` está vigilando el directorio `vendor`en busca de cambios. Al menos en algunos sistemas, hay demasiados archivos aquí para monitorizar y pasan cosas raras. No pasa nada: elimina `vendor/`. Y como hemos cambiado la configuración, salta a tu terminal y reinicia el servidor web:

```terminal
symfony server:stop
```

Y:

```terminal
symfony serve -d
```

Nuestro nuevo correo electrónico de recordatorio de reserva tendrá una plantilla muy similar a la de confirmación de reserva. Para reducir la duplicación, y mantener la coherencia de nuestros elegantes correos electrónicos, en `templates/email/`, crea una nueva plantilla `layout.html.twig` a la que se extenderán todos nuestros correos electrónicos.

Copia el contenido de `booking_confirmation.html.twig` y pégalo aquí. Ahora, elimina el contenido específico de confirmación de reserva y crea un bloque `content` vacío. Creo que está bien mantener nuestra firma aquí.

En `booking_confirmation.html.twig`, aquí arriba, amplía este nuevo diseño y añade el bloque`content`. Abajo, copia el contenido específico del correo electrónico y pégalo dentro de ese bloque. Elimina todo lo demás.

Asegurémonos de que el correo electrónico de confirmación de la reserva sigue funcionando, ¡y tenemos pruebas para ello! De vuelta en el terminal, ejecútalas con:

```terminal
bin/phpunit
```

¡Verde! Eso es buena señal. Asegurémonos doblemente comprobándolo en Mailtrap. En la aplicación, reserva un viaje... y comprueba Mailtrap. ¡Sigue estando fantástico!

Es hora de enviar el correo electrónico recordatorio! Después de enviar un correo electrónico recordatorio, tenemos que marcar la reserva para no molestar al cliente con múltiples recordatorios. Vamos a añadir una nueva bandera para esto a la entidad `Booking`.

En tu terminal, ejecuta:

```terminal
symfony make:entity Booking
```

¡Uy!

```terminal
symfony console make:entity Booking
```

¿Añadir un nuevo campo llamado `reminderSentAt`, tipo `datetime_immutable`, anulable? Sí. Se trata de un patrón habitual que utilizo para este tipo de campos bandera en lugar de un simple `boolean`.`null` significa `false` y una fecha significa `true`. Funciona igual, pero nos da un poco más de información.

Pulsa intro para salir del comando.

En la entidad `Booking`... aquí está nuestra nueva propiedad, y aquí abajo, el getter y el setter.

A continuación, necesitamos una forma de encontrar todas las reservas que necesitan que se les envíe un recordatorio. ¡Un trabajo perfecto para`BookingRepository`! Añade un nuevo método llamado `findBookingsToRemind()`, tipo de retorno: `array`. Añade un docblock para mostrar que devuelve un array de objetos Reserva.

Dentro, `return $this->createQueryBuilder()`, alias `b`. Encadena`->andWhere('b.reminderSentAt IS NULL')`, `->andWhere('b.date <= :future')`,`->andWhere('b.date > :now')` rellenando los marcadores de posición con`->setParameter('future', new \DateTimeImmutable('+7 days'))` y`->setParameter('now', new \DateTimeImmutable('now'))`. Termina con `->getQuery()->getResult()`.

En `AppFixtures`, aquí abajo, creamos algunas reservas falsas. Añade una que desencadene con seguridad el envío de un correo electrónico recordatorio:`BookingFactory::createOne()`, dentro, `'trip' => $arrakis, 'customer' => $clark` y, ésta es la parte importante, `'date' => new \DateTimeImmutable('+6 days')`. Claramente entre ahora y dentro de 7 días.

Hemos realizado cambios en la estructura de nuestra base de datos. Normalmente, deberíamos crear una migración... pero, no estamos utilizando migraciones. Así que, simplemente forzaremos la actualización del esquema. En tu terminal, ejecuta:

```terminal
symfony console doctrine:schema:update --force
```

Luego, vuelve a cargar los accesorios:

```terminal
symfony console doctrine:fixture:load
```

Todo ha funcionado, ¡genial!

A continuación, ¡crearemos un nuevo correo electrónico recordatorio y un comando CLI para enviarlo!
