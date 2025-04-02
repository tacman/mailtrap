# El Componente Webhook para Eventos de Email

En Mailtrap, cuando enviamos correos electrónicos en producción, recordemos que podemos comprobar cada correo electrónico: si fue enviado, entregado, abierto, rebotado (¡lo cual es importante!) y mucho más. Mailtrap nos permite establecer una URL de webhook para que nos envíe información sobre estos eventos.

Además, ¡descubriremos dos nuevos componentes de Symfony! Busca tu terminal e instálalos:

```terminal
composer require webhook remote-event
```

El componente webhook nos proporciona una única ruta a la que enviar todos los webhooks. Analiza los datos que se nos envían, denominados carga de pago, y envía un evento remoto a un consumidor. Puedes pensar en los eventos remotos como algo similar a los eventos Symfony. En lugar de que tu aplicación envíe un evento, lo hace un servicio de terceros, de ahí lo de evento remoto. Y en lugar de oyentes de eventos, decimos que los eventos remotos tienen consumidores.

Ejecuta

```terminal
git status
```

para ver qué ha añadido la receta: `config/routes/webhook.yaml`. Eso añade el controlador webhook. Comprueba la ruta con:

```terminal
symfony console debug:route webhook
```

Comprueba la primera. La ruta es `/webhook/{type}`. Así que ahora tenemos que configurar algún tipo.

los webhooks de terceros -como los de Mailtrap o los de un procesador de pagos o un sistema de alertas de Supernova- pueden enviarnos cargas útiles muy diferentes, por lo que normalmente necesitamos crear nuestros propios analizadores y eventos remotos. Dado que los eventos de correo electrónico son bastante estándar, Symfony proporciona algunos eventos remotos out-of-the-box para ellos. Algunos puentes de correo, incluido el puente Mailtrap que estamos utilizando, proporcionan analizadores para los webhooks de cada servicio. Sólo tenemos que configurarlo.

En `config/packages/`, crea un archivo `webhook.yaml`. Añade `framework`,`webhook`, `routing`, `mailtrap` (este es el tipo utilizado en la URL), y luego `service`. Para averiguar el id de servicio del analizador Mailtrap, ve a la [documentación de Symfony Webhook](https://symfony.com/doc/current/webhook.html). Busca el id de servicio del analizador Mailtrap, cópialo... y pégalo aquí.

Ahora necesitamos un consumidor. Crea una nueva clase llamada `EmailEventConsumer`en el espacio de nombres `App\Webhook`. Es necesario que implemente`ConsumerInterface` de `RemoteEvent`. Añade el método `consume()` necesario. Para indicar a Symfony qué tipo de webhook queremos que consuma, añade el atributo `#[AsRemoteEventConsumer]` con `mailtrap`.

Sobre `consume()`, añade un docblock para ayudar a nuestro IDE:`@param MailerDeliveryEvent|MailerEngagementEvent $event`. Estos son los eventos remotos de correo genéricos que proporciona Symfony. Dentro, escribe `$event->` para ver los métodos disponibles.

En una aplicación real, aquí sería donde harías algo con estos eventos, como guardarlos en la base de datos o notificar a un administrador si un correo electrónico rebota. En realidad, si un correo electrónico rebota varias veces, puede que quieras actualizar algo para evitar que se vuelva a intentar, ya que esto puede dañar la fiabilidad de tu correo electrónico, pero para nuestros propósitos, sólo `dump($event)`.

Una última cosa: el controlador webhook envía el evento remoto al consumidor a través de Symfony Messenge dentro de una clase de mensaje llamada `ConsumeRemoteEventMessage`.

Para manejar esto de forma asíncrona y mantener tus respuestas webhook rápidas, en`config/packages/messenger.yaml`, bajo `routing`, añade`Symfony\Component\RemoteEvent\Messenger\ConsumeRemoteEventMessage` y envíalo a nuestro transporte `async`.

Ya está Estamos listos para hacer una demostración de este webhook. ¡Eso a continuación!
