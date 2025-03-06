# Diseño Twig de correo electrónico

¡Hora de una nueva función! Quiero enviar un correo electrónico recordatorio a los clientes 1 semana antes de su viaje reservado.

Sin embargo, primero tenemos un pequeño problema con nuestro trabajador Symfony CLI. Abre`.symfony.local.yaml`. Nuestro trabajador `messenger` está vigilando el directorio `vendor`en busca de cambios. Al menos en algunos sistemas, hay demasiados archivos aquí para monitorizar. Parece que ocurren cosas raras. Para solucionarlo, elimina `vendor`. Como hemos cambiado la configuración, salta a tu terminal y reinicia el servidor web:

```terminal
symfony server:stop
```

Y:

```terminal
symfony serve -d
```

Nuestro nuevo correo electrónico de recordatorio de reserva tendrá una plantilla muy similar a la de confirmación de reserva. Para reducir la duplicación y asegurarnos de que nuestros correos electrónicos tienen un aspecto coherente, en `templates/email/`, crea una nueva plantilla `layout.html.twig` para que todos nuestros correos electrónicos se extiendan.

Copia el contenido de `booking_confirmation.html.twig` y pégalo aquí. Ahora, elimina el contenido específico de confirmación de reserva y crea un bloque `content` vacío. Creo que está bien mantener nuestra firma aquí.

En `booking_confirmation.html.twig`, aquí arriba, amplía este nuevo diseño y añade el bloque`content`. Abajo, copia el contenido específico del correo electrónico y pégalo dentro del bloque. Elimina todo lo demás de la plantilla.

Asegurémonos de que el correo electrónico de confirmación de la reserva sigue funcionando, ¡y tenemos pruebas para ello! De vuelta en el terminal, ejecuta las pruebas con:

```terminal
bin/phpunit
```

¡Verde! Es una buena señal. Asegurémonos doblemente comprobándolo en Mailtrap. En la aplicación, reserva un viaje... y comprueba Mailtrap. Sí, tiene buena pinta.

Hemos facilitado la creación de nuevas plantillas de correo electrónico. Ahora, tenemos que preparar un poco la nueva función. Una vez enviado un recordatorio por correo electrónico, tenemos que marcar de algún modo la reserva para no enviar varios recordatorios.

La entidad `Booking` representa una única reserva, así que es el lugar perfecto. Añadiremos una nueva columna para controlar si se ha enviado o no un recordatorio.

En tu terminal, ejecuta:

```terminal
symfony console make:entity Booking
```

Añade un nuevo campo llamado `reminderSentAt`, tipo `datetime_immutable`, ¿anulable? Sí. Se trata de un patrón habitual que utilizo para este tipo de campos bandera en lugar de un simple `boolean`.`null` significa `false` y una fecha significa `true`. Esto te permite auditar cuándo se estableció el campo.

Pulsa intro para salir del comando.

En la entidad `Booking`... aquí está nuestra nueva propiedad, y aquí abajo, el getter y el setter.

A continuación, necesitamos una forma de encontrar todas las reservas que necesitan que se les envíe un recordatorio. ¡Un trabajo perfecto para el`BookingRepository`! Añade un nuevo método llamado `findBookingsToRemind()`, tipo de retorno: `array`. Añade un docblock para mostrar que devuelve un array de Reservas.

Dentro, `return $this->createQueryBuilder()`, alias `b`. Cadena`->andWhere('b.reminderSentAt IS NULL')`. Sólo queremos las reservas en las que aún no se ha enviado el recordatorio. Añade `->andWhere('b.date <= :future')`, `->andWhere('b.date > :now')``->setParameter('future', new \DateTimeImmutable('+7 days'))` y`->setParameter('now', new \DateTimeImmutable('now'))`. Esto encontrará las reservas entre hoy y dentro de 7 días. Por último, `->getQuery()->getResult()`. ¡Listo!

Tenemos algunas fijaciones para desarrollar en `AppFixtures`. Aquí abajo, creamos algunas reservas falsas. Añadimos una que sabemos que activará el envío de un correo electrónico recordatorio:`BookingFactory::createOne()`, dentro, `'trip' => $arrakis, 'customer' => $clark` y, ésta es la parte importante, `'date' => new \DateTimeImmutable('+6 days')`. Claramente entre ahora y dentro de 7 días.

Hemos realizado cambios en la estructura de nuestra base de datos. Normalmente, deberíamos crear una migración... pero, no estamos utilizando migraciones. Así que nos limitaremos a forzar la actualización del esquema. En tu terminal, ejecuta:

```terminal
symfony console doctrine:schema:update --force
```

Ya que hemos actualizado nuestros esquemas, recárgalos:

```terminal
symfony console doctrine:fixture:load
```

Todo ha funcionado, ¡genial!

A continuación, ¡crearemos un nuevo correo electrónico recordatorio y un comando CLI para enviarlo!
