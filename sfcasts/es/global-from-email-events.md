# Global Desde (y Diversión) con Eventos de Correo Electrónico

Apuesto a que la mayoría, si no todos los correos electrónicos que envíe tu aplicación tendrán la misma dirección de correo electrónico, algo ingenioso como`hal9000@universal-travel.com`

O el probado pero más soporífero `info@universal-travel.com`. Como todos los correos tendrán la misma dirección de remitente, no tiene sentido establecerla en cada correo. Curiosamente, no hay ninguna opción de configuración minúscula para esto. Pero eso es genial para nosotros: ¡nos da la oportunidad de aprender sobre eventos! Muy potente, muy friki.

Evitemos la necesidad de añadir la misma dirección del remitente a cada correo electrónico configurándola globalmente. Antes de enviar un correo electrónico, Mailer envía un mensaje `MessageEvent`

Para escucharlo, busca tu terminal y ejecútalo:

```terminal
symfony console make:listener
```

Llámalo `GlobalFromEmailListener`. El nos da una lista de eventos que podemos escuchar. Queremos el primero: `MessageEvent`. Empieza a escribir `Symfony` y nos lo autocompletará. Pulsa intro.

¡Escucha creada!

Para molar más, pongamos nuestra dirección global de como parámetro. En `config/services.yaml`, debajo de `parameters`, añade una nueva: `global_from_email`. Esto será una cadena, pero fíjate en esto: ponlo en `Universal Travel `, luego entre paréntesis angulares, pon el email:`<info@universal-travel.com>`. Cuando Symfony vea una cadena con este aspecto como dirección de correo electrónico, creará el objeto `Address` adecuado con un nombre y un correo electrónico establecidos. ¡Genial!

Abre la nueva claseL `src/EventListener/GlobalFromEmailListener.php`. Añade un constructor con un argumento `private string $fromEmail` y un atributo `#[Autowire]`con el nombre de nuestro parámetro: `%global_from_email%`.

Aquí abajo, el atributo `#[AsEventListener]` es lo que marca este método como un oyente de eventos. En realidad, podemos eliminar este argumento `event` - se deducirá de la sugerencia de tipo del argumento del método: `MessageEvent`.

Dentro, coge primero el mensaje del evento: `$message = $event->getMessage()`. Salta al método `getMessage()` para ver lo que devuelve. `RawMessage`... salta a esto y mira qué clases lo extienden. `TemplatedEmail` ¡! ¡Perfecto!

De vuelta a nuestro oyente, escribe `if (!$message instanceof TemplatedEmail)`, y dentro, `return;`. Es probable que esto no ocurra nunca, pero es una buena práctica volver a comprobarlo. Además, ayuda a nuestro IDE a saber que `$message` es ahora un `TemplatedEmail`.

Es posible que un correo electrónico aún establezca su propia dirección `from`. En este caso, no queremos anularla. Así que añade una cláusula de protección `if ($message->getFrom())`, `return;`.

Ahora, podemos establecer la global `from`: `$message->from($this->fromEmail)`. Perfecto

De vuelta en `TripController::show()`, elimina el `->from()` para el correo electrónico.

¡Es hora de probarlo! En nuestra aplicación, reserva un viaje y comprueba Mailtrap para el correo electrónico. Redoble de tambores... ¡el `from` está configurado correctamente! ¡Nuestro oyente funciona! Nunca dudé de nosotros.

Un detalle más para que esto sea completamente hermético (como la mayoría de nuestros barcos).

Imagina un formulario de contacto en el que el usuario rellena su nombre, correo electrónico y un mensaje. Esto lanza un correo electrónico con estos datos a tu equipo de soporte. En sus clientes de correo electrónico, estaría bien que, cuando pulsen responder, vaya al correo del formulario, no a tu "global de".

Podrías pensar que deberías establecer la dirección `from` en el correo electrónico del usuario. Veremos Pero eso no funcionará, ya que no estamos autorizados a enviar correos electrónicos en nombre de ese usuario. Más sobre la seguridad del correo electrónico en un minuto.

Afortunadamente, existe una cabecera de correo electrónico especial llamada `Reply-To` precisamente para este escenario. Cuando construyas tu correo electrónico, configúrala con `->replyTo()` y pasa la dirección de correo electrónico del usuario.

Abróchate el cinturón porque los tanques de refuerzo están llenos y listos para el lanzamiento! Es hora de enviar correos electrónicos reales en producción! Eso a continuación.
