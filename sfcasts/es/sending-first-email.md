# Enviar nuestro primer correo electrónico

¡Vamos de viaje! "Visitar Krypton", ¡Esperemos que aún no haya sido destruido! Sin molestarme en comprobarlo, ¡reservémoslo! Utilizaré el nombre: "Kevin", el correo electrónico "kevin@example.com" y una fecha cualquiera en el futuro. Pulsa "Reservar viaje".

Esta es la página de "detalles de la reserva". Fíjate en la URL: tiene un token único específico para esta reserva. Si un usuario necesita volver aquí más tarde, actualmente, tiene que marcar esta página o enviarse a sí mismo la URL si es como yo ¡Lamentable! Enviémosles un correo electrónico de confirmación que incluya un enlace a esta página.

Quiero que esto ocurra después de guardar la reserva por primera vez. Abre `TripController`y busca el método `show()`. Esto hace la reserva: si el formulario es válido, crea o recupera un cliente y crea una reserva para este cliente y viaje. Luego redirigimos a la página de detalles de la reserva. Deliciosamente aburrido hasta ahora, tal y como me gusta mi código, y los fines de semana.

## Inyecta `MailerInterface`

Quiero enviar un correo electrónico después de crear la reserva. Date un poco de espacio moviendo cada argumento del método a su propia línea. Después, añade `MailerInterface $mailer` para obtener el servicio principal de envío de correos electrónicos:

[[[ code('8148567b63') ]]]

## Crear el correo electrónico

Después de `flush()`, que inserta la reserva en la base de datos, crea un nuevo objeto de correo electrónico: `$email = new Email()` (el de `Symfony\Component\Mime`). Envuélvelo entre paréntesis para que podamos encadenar métodos. ¿Qué necesita cada correo electrónico? Una dirección de correo electrónico `from`: `->from()` qué tal `info@univeral-travel.com`. Una dirección de correo electrónico `to`: `->to($customer->getEmail())`. Ahora, el `subject`: `->subject('Booking Confirmation')`. Y por último, el correo electrónico necesita un cuerpo: `->text('Your booking has been confirmed')` - suficiente por ahora:

[[[ code('2e7e7d563a') ]]]

## Envía el correo electrónico

Termina con `$mailer->send($email)`:

[[[ code('7843d188cc') ]]]

¡Vamos a probarlo!

De nuevo en nuestra aplicación, vuelve a la página de inicio y elige un viaje. Para el nombre, utiliza "Steve", correo electrónico, "steve@minecraft.com", cualquier fecha en el futuro, y reserva el viaje.

Vale... esta página tiene exactamente el mismo aspecto que antes. ¿Se ha enviado un correo electrónico? Nada en la barra de herramientas de depuración web parece indicarlo...

En realidad, el correo electrónico se envió en la petición anterior: el envío del formulario. Ese controlador nos redirigió a esta página. Pero la barra de herramientas de depuración web nos ofrece un atajo para acceder al perfilador de la petición anterior: pasa el ratón por encima de `200` y haz clic en el enlace del perfilador para acceder a él.

Echa un vistazo a la barra lateral: ¡tenemos una nueva pestaña "Correos electrónicos"! Y muestra que se ha enviado 1 correo electrónico. ¡Lo hicimos! ¡Haz clic en él y aquí está nuestro correo electrónico! Los campos "de", "a", "asunto" y "cuerpo" son los esperados.

Recuerda que estamos utilizando el transporte de correo `null`, así que este correo no se ha enviado realmente, ¡pero es genial que podamos previsualizarlo en el perfilador!

Aunque... Creo que ambos sabemos que este correo... es... bastante cutre. ¡No da ninguna información útil! ¡Ni URL a la página de detalles de la reserva, ni destino, ni fecha, ni nada! Es tan inútil que me alegro de que el transporte `null` lo tire por la ventana espacial.

¡Eso a continuación!
