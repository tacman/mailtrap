# Global Desde (y Diversión) con Eventos de Correo Electrónico

Apuesto a que la mayoría, si no todos, los correos electrónicos que envíe tu aplicación tendrán la misma dirección de correo electrónico, algo ingenioso como`hal9000@universal-travel.com` o el probado pero más soporífero`info@universal-travel.com`.

Como todos los correos tendrán la misma dirección de origen, no tiene sentido establecerla en todos los correos. Curiosamente, no hay ninguna opción de configuración minúscula para esto. Pero eso es genial para nosotros: ¡nos da la oportunidad de aprender sobre eventos! Muy potente, muy friki.

## El `MessageEvent`

Antes de enviar un correo electrónico, Mailer envía un mensaje `MessageEvent`.

Para escucharlo, busca tu terminal y ejecuta:

```terminal
symfony console make:listener
```

Llámalo `GlobalFromEmailListener`. El nos da una lista de eventos que podemos escuchar. Queremos el primero: `MessageEvent`. Empieza a escribir `Symfony` y se autocompletará por nosotros. Pulsa intro.

¡Escucha creada!

Para ser más guays, pongamos nuestra dirección global de origen como parámetro. En `config/services.yaml`, debajo de `parameters`, añade una nueva: `global_from_email`.

## Cadena especial de dirección de correo electrónico

Esto será una cadena, pero fíjate en esto: ponlo en `Universal Travel `, luego entre paréntesis angulares, pon el correo electrónico:`<info@universal-travel.com>`:

[[[ code('1ab2c11bb8') ]]]

Cuando Symfony Mailer vea una cadena con este aspecto como dirección de correo electrónico, creará el objeto `Address` adecuado con un nombre y un correo electrónico establecidos. ¡Genial!

## `MessageEvent` Receptor

Abre la nueva clase `src/EventListener/GlobalFromEmailListener.php`. Añade un constructor con un argumento `private string $fromEmail` y un atributo `#[Autowire]`con el nombre de nuestro parámetro: `%global_from_email%`:

[[[ code('bc4e150613') ]]]

Aquí abajo, el atributo `#[AsEventListener]` es lo que marca este método como un oyente de eventos. En realidad, podemos eliminar este argumento `event` - se deducirá de la sugerencia de tipo del argumento del método: `MessageEvent`:

[[[ code('eed01fe852') ]]]

Dentro, primero coge el mensaje del evento: `$message = $event->getMessage()`:

[[[ code('63ae11e65b') ]]]

Salta al método `getMessage()` para ver lo que devuelve. `RawMessage`... salta a esto y mira qué clases lo extienden. `TemplatedEmail` ¡! ¡Perfecto!

De vuelta a nuestro oyente, escribe `if (!$message instanceof TemplatedEmail)`, y dentro, `return;`:

[[[ code('a22f86e267') ]]]

Es probable que esto no ocurra nunca, pero es una buena práctica volver a comprobarlo. Además, ayuda a nuestro IDE a saber que `$message` es ahora un `TemplatedEmail`.

Es posible que un correo electrónico aún establezca su propia dirección `from`. En este caso, no queremos anularla. Así que añade una cláusula de protección `if ($message->getFrom())`, `return;`:

[[[ code('ac13586a11') ]]]

Ahora, podemos establecer la global `from`: `$message->from($this->fromEmail)`:

[[[ code('18eea9c01f') ]]]

¡Perfecto!

De vuelta en `TripController::show()`, elimina el `->from()` para el correo electrónico.

¡Es hora de probarlo! En nuestra aplicación, reserva un viaje y comprueba Mailtrap para el correo electrónico. Redoble de tambores... ¡el `from` está configurado correctamente! ¡Nuestro oyente funciona! Nunca dudé de nosotros.

## `Reply-To`

Un detalle más para que esto sea completamente hermético (como la mayoría de nuestros barcos).

Imagina un formulario de contacto en el que el usuario rellena su nombre, correo electrónico y un mensaje. Esto lanza un correo electrónico con estos datos a tu equipo de soporte. En sus clientes de correo electrónico, estaría bien que, cuando pulsen responder, vaya al correo del formulario, no a tu "global de".

Podrías pensar que deberías establecer la dirección `from` en el correo electrónico del usuario, pero eso no funcionará, ya que no estamos autorizados a enviar correos electrónicos en nombre de ese usuario. Pronto hablaremos más sobre la seguridad del correo electrónico.

Afortunadamente, existe una cabecera de correo electrónico especial llamada `Reply-To` precisamente para este escenario. Cuando construyas tu correo electrónico, configúrala con `->replyTo()` y pasa la dirección de correo electrónico del usuario.

Abróchate el cinturón porque los tanques de refuerzo están llenos y listos para el lanzamiento! Es hora de enviar correos electrónicos reales en producción! Eso a continuación.
