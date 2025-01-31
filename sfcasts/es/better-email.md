# Un correo electrónico mejor

Creo que tú, yo, cualquiera que haya recibido alguna vez un correo electrónico, podemos estar de acuerdo en que nuestro primer correo electrónico apesta. No aporta ningún valor. ¡Mejorémoslo!

## `Address` Objeto

En primer lugar, podemos añadir un nombre al correo electrónico. Esto aparecerá en la mayoría de los clientes de correo electrónico en lugar de sólo la dirección de correo electrónico: tiene un aspecto más fluido. Envuelve el `from` con`new Address()`, el de `Symfony\Component\Mime`. El primer argumento es el correo electrónico, y el segundo es el nombre: ¿qué tal `Universal Travel`:

[[[ code('5cd55f5238') ]]]

También podemos envolver el `to` con `new Address()`. y pasar `$customer->getName()` para el nombre:

[[[ code('f882ff10c6') ]]]

Para el `subject`, añade el nombre del viaje: `'Booking Confirmation for ' . $trip->getName()`:

[[[ code('0cbcddf670') ]]]

Para el cuerpo `text`. Podríamos alinear todo el texto aquí. Eso se pondría feo, así que ¡utilicemos Twig! Necesitamos una plantilla. 
En `templates/`, añade un nuevo directorio `email/` y, dentro, crea un nuevo archivo:`booking_confirmation.txt.twig`. Twig puede utilizarse para cualquier formato de texto, no sólo para `html`. Una buena práctica es incluir el formato - `.html` o `.txt` - en el nombre del archivo. Pero a Twig no le importa eso: es sólo para satisfacer nuestro cerebro humano. Volveremos a este archivo en un segundo.

## Plantilla de correo Twig

Vuelve a `TripController::show()`, en lugar de `new Email()`, utiliza `new TemplatedEmail()`(el de `Symfony\Bridge\Twig`):

[[[ code('6514c7bf48') ]]]

Sustituye `->text()` por `->textTemplate('email/booking_confirmation.txt.twig')`:

[[[ code('cc24ae05ee') ]]]

Para pasar variables a la plantilla, utiliza `->context()` con`'customer' => $customer, 'trip' => $trip, 'booking' => $booking`:

[[[ code('2a75581051') ]]]

Ten en cuenta que aquí técnicamente no estamos renderizando la plantilla Twig: Mailer lo hará por nosotros antes de enviar el correo electrónico.

Esto es código Twig normal y aburrido. Vamos a mostrar el nombre del usuario utilizando un truco barato, el nombre del viaje, la fecha de salida y un enlace para gestionar la reserva. Necesitamos utilizar URLs absolutas en los correos electrónicos -como https://univeral-travel.com/booking-, así que aprovecharemos la función Twig `url()` en lugar de `path()`: `{{ url('booking_show', {'uid': booking.uid}) }}`. Terminaremos educadamente con, `Regards, the Universal Travel team`:

[[[ code('a2f32b8263') ]]]

¡Cuerpo del correo electrónico listo! Pruébalo. De vuelta en tu navegador, elige un viaje, nombre: `Steve`, correo electrónico:`steve@minecraft.com`, cualquier fecha en el futuro, y reserva el viaje. Abre el perfil de la última petición y haz clic en la pestaña `Emails` para ver el correo electrónico.

¡Mucho mejor! Observa que las direcciones `From` y `To` ahora tienen nombre. ¡Y nuestro contenido de texto es definitivamente más valioso! Copia la URL de la reserva y pégala en tu navegador para asegurarte de que va al lugar correcto. Parece que sí, ¡bien!

A continuación, utilizaremos la herramienta de pruebas de [Mailtrap](https://mailtrap.io/) para obtener una vista previa más robusta del correo electrónico.
