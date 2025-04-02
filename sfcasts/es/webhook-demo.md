# Demostración de nuestro webhook a través de un agujero de gusano

¡Es hora de probar el webhook Mailtrap!

En primer lugar, tenemos que volver a cambiar nuestro entorno de desarrollo para enviar en producción. En `.env.local`, cambia a tu Mailtrap de producción `MAILER_DSN` y en`config/services.yaml`, asegúrate de que el dominio `global_from_email`'s es el que configuraste con Mailtrap.

En Mailtrap, ve a "Configuración" > "Webhooks" y haz clic en "Crear nuevo Webhook". Lo primero que necesitamos es una URL de Webhook. Hmm, esto tiene que ser `/webhook/mailtrap`pero tiene que ser una URL absoluta. En producción, esto no sería un problema: sería tu dominio de producción. En desarrollo, es un poco más complicado. No podemos usar simplemente la URL que nos da el servidor CLI de Symfony...

De alguna manera tenemos que exponer nuestro servidor Symfony local al público. Y hay una herramienta muy útil que hace exactamente esto: [ngrok](https://ngrok.com/). Crea una cuenta gratuita, inicia sesión y sigue las instrucciones para configurar el cliente CLI ngrok.

En el terminal, reinicia el servidor web Symfony:

```terminal
symfony server:stop
```

No se está ejecutando. Inícialo con:

```terminal
symfony serve -d
```

Esta es la URL que necesitamos exponer, cópiala y ejecútala:

```terminal
ngrok http <paste-url>
```

Pega la URL y pulsa intro. ¡Agujero de gusano abierto!

Esta URL de "Reenvío" de aspecto loco es la URL pública. Cópiala y pégala en tu navegador. Esta advertencia sólo te permite saber que estás atravesando un túnel. Haz clic en "Visitar sitio" para ver tu aplicación. ¡Genial!

De vuelta en Mailtrap, pega esta URL y añade `/webhook/mailtrap` al final. En "Seleccionar flujo", elige "Transaccional". En "Seleccionar dominio", elige tu dominio Mailtrap configurado. Selecciona todos los eventos y luego "Guardar".

Vuelve al nuevo webhook y haz clic en "Ejecuta la prueba".

> La prueba de la URL del webhook se ha completado correctamente

¡Buena señal!

¿Recuerdas que en nuestro `EmailEventConsumer`, sólo estamos volcando el evento? Como el acceso al webhook se produce entre bastidores, no podemos ver el volcado... ¿o sí? Ejecuta en un nuevo terminal:

```terminal
symfony console server:dump
```

Esto se conecta a nuestra aplicación y cualquier volcado se mostrará aquí en directo. ¡Inteligente!

En tu navegador, reserva un viaje, recuerda utilizar una dirección de correo electrónico real (¡pero no la mía!)

¡Momento de la verdad! De nuevo en el terminal ejecutando el servidor de volcado, espera un poco... ¡Bien! ¡Tenemos un volcado! Desplázate un poco hacia arriba... Se trata de un `MailerDeliveryEvent` para`delivered`. Vemos el ID interno que Mailtrap asignó, la carga útil sin procesar, la fecha, el correo electrónico del destinatario, incluso nuestros metadatos y etiqueta personalizados.

¡Probemos con un evento de compromiso! En tu cliente de correo electrónico, abre el correo.

De vuelta en el terminal del servidor de volcado, espera un poco... ¡y boom! ¡Otro evento! Esta vez, es un `MailerEngagementEvent` para `open`. ¡Qué guay!

Muy bien, cadetes espaciales, ¡esto es todo por este curso! Hemos conseguido cubrir casi todas las funciones de Symfony Mailer sin hacer SPAM a nuestros usuarios. ¡Ganamos!

hasta la próxima, ¡feliz programación!
