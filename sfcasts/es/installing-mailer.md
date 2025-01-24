# Instalar el Mailer

¡Hola amigos! ¡Bienvenidos a "Symfony Mailer con Mailtrap"! Soy Kevin, y seré tu postmaster para este curso, que trata sobre el envío de correos electrónicos bonitos con el componente Mailer de Symfony, incluyendo la adición de HTML, CSS - y la configuración para producción. En ese sentido, hay muchos servicios que puedes utilizar en producción para enviar tus correos electrónicos. Este curso se centrará en uno llamado Mailtrap: (1) porque es genial y (2) porque ofrece una forma fantástica de previsualizar tus correos electrónicos. Pero no te preocupes, los conceptos que trataremos son universales y pueden aplicarse a cualquier servicio de correo electrónico. ¡Y además! También veremos cómo rastrear eventos de correo electrónico como rebotes, aperturas y clics en enlaces aprovechando algunos componentes relativamente nuevos de Symfony: Webhook y RemoteEvent.

## Correos electrónicos transaccionales vs masivos

Antes de empezar a enviar información importante por correo electrónico, tenemos que aclarar algo: Symfony Mailer es sólo para lo que se llama correos electrónicos transaccionales. Son correos específicos de usuario que se producen cuando ocurre algo concreto en tu aplicación. Cosas como: un correo electrónico de bienvenida después de que un usuario se registre, un correo electrónico de confirmación de pedido cuando realizan un pedido, o incluso correos electrónicos como "tu post ha sido votado" son ejemplos de correos electrónicos transaccionales. Symfony Mailer no es para emails masivos o de marketing. Por ello, no tenemos que preocuparnos de ningún tipo de funcionalidad para darse de baja. Existen servicios específicos para enviar correos masivos o boletines informativos, Mailtrap incluso puede hacerlo a través de su sitio web.

## Nuestro proyecto

Como siempre, para sacar el máximo partido a tu dinero en screencast, ¡deberías codificar conmigo! Descarga el código del curso en esta página. Cuando descomprimas el archivo, encontrarás un directorio `start/` con el código con el que empezaremos. Sigue el archivo `README.md` para poner en marcha la aplicación. Yo ya lo he hecho y he ejecutado`symfony serve -d` para iniciar el servidor web. 

Bienvenido a "Viajes Universales": una agencia de viajes donde los usuarios pueden reservar viajes a diferentes lugares galácticos. Aquí tienes los viajes disponibles actualmente. Los usuarios ya pueden reservarlos, pero no se envían correos electrónicos de confirmación cuando lo hacen. ¡Vamos a arreglar eso! Si voy a gastar miles de créditos en un viaje a Naboo, ¡quiero saber que mi reserva se ha realizado correctamente!

## Instalar el componente Mailer

Paso 1: ¡instalemos el Mailer de Symfony! Abre tu terminal y ejecuta:

```terminal
composer require mailer
```

La receta de Symfony Flex para el mailer nos pide que instalemos alguna configuración de Docker. Esto es para un servidor SMTP local que nos ayude con la previsualización de los correos electrónicos. Vamos a utilizar Mailtrap para esto, así que di "no". ¡Instalado! Ejecuta::

```terminal
git status
```

para ver lo que tenemos. Parece que la receta añadió algunas variables de entorno en `.env` y añadió la configuración del mailer en `config/packages/mailer.yaml`.

## `MAILER_DSN`

En tu IDE, abre `.env`. La receta del Mailer añadió esta variable de entorno `MAILER_DSN`. Se trata de una cadena especial con aspecto de URL que configura el transporte de tu mailer: cómo se envían realmente tus correos electrónicos, por ejemplo a través de SMTP, Mailtrap, etc. La receta utiliza por defecto `null://null` y es perfecta para el desarrollo local y las pruebas. Este transporte no hace nada cuando se envía un correo electrónico Finge entregar el correo electrónico, pero en realidad lo envía por una esclusa de aire. Previsualizaremos nuestros correos electrónicos de otra forma.

¡Vale! ¡Estamos listos para enviar nuestro primer correo electrónico! ¡Hagámoslo a continuación!
