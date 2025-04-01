# Webhook para Eventos de Email

En MailTrap, cuando estamos enviando emails en producción, recordemos que podemos auditar los emails enviados y ver los diferentes eventos: Enviado, Entregado, Abierto, etc. Mailtrap nos permite configurar un webhook para enviar estos eventos a nuestra app.

Para ello, necesitamos instalar dos nuevos componentes de Symfony:

```terminal
composer require webhook remote-event
```

El componente webhook proporciona una única ruta a la que enviar todos los webhooks. Analiza la carga útil sin procesar y envía un evento remoto a un consumidor. Puedes pensar que los eventos remotos son similares a los eventos Symfony. En lugar de que tu aplicación envíe un evento, lo hace un servicio de terceros, de ahí lo de evento remoto. En lugar de escuchadores de eventos, tenemos consumidores de eventos remotos.

Ejecuta:

```terminal
git status
```

Para ver qué ha configurado la receta. `config/routes/webhook.yaml` - que añade el controlador webhook. Comprueba la ruta con:

```terminal
symfony console debug:route webhook
```

Elige la primera. La ruta es `/webhook/{type}`. Ahora tenemos que configurar un tipo.

Como los webhooks de terceros pueden tener cargas útiles muy diferentes, normalmente necesitamos crear nuestros propios analizadores y eventos remotos. Dado que los eventos de correo electrónico son bastante estándar, Symfony proporciona algunos eventos remotos listos para usar. Algunos puentes de correo, incluido el puente Mailtrap que estamos utilizando, proporcionan los analizadores adecuados para los webhooks de cada servicio. Sólo tenemos que configurarlo.

En `config/packages`, crea un archivo `webhook.yaml`. Añade `framework`,`webhook`, `routing`, `mailtrap` (este es el tipo utilizado en la URL), y luego `service`. Para encontrar el identificador del analizador específico de Mailtrap, ve a la [documentación de Symfony Webhook](https://symfony.com/doc/current/webhook.html). Busca el identificador de servicio del analizador de Mailtrap. Cópialo y pégalo aquí.

Ahora necesitamos un consumidor. Crea una nueva clase llamada `EmailEventConsumer`en el espacio de nombres `App\Webhook`. Tiene que implementar`ConsumerInterface` desde `RemoteEvent`. Implementa el método `consume()`. Para que Symfony sepa qué tipo de webhook queremos que consuma, añade el atributo `#[AsRemoteEventConsumer]` con `mailtrap` como tipo.

Sobre `consume()`, añade un docblock para ayudar a nuestro IDE:`@param MailerDeliveryEvent|MailerEngagementEvent $event`. Estos son los eventos remotos de correo genéricos que proporciona Symfony. Dentro del método, escribe `$event->` para ver los métodos disponibles.

En una aplicación real, aquí sería donde harías algo con estos eventos, como guardarlos en la base de datos o notificar a un administrador si un correo electrónico rebota. Para nosotros, basta con escribir `dump($event)`.

Una última cosa: el controlador del webhook envía el evento remoto al consumidor a través de Symfony Messenger, por lo que podemos hacerlo asíncrono. En`config/packages/messenger.yaml`, bajo `routing`, añade`Symfony\Component\RemoteEvent\Messenger\ConsumeRemoteEventMessage` y envía a nuestro transporte `async`.

Ya está Estamos listos para hacer una demostración de este webhook. ¡Eso a continuación!
