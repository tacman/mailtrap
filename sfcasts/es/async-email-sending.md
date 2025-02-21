# Envío asíncrono y reintentable con Messenger

Cuando enviamos este correo electrónico, se envía inmediatamente, de forma sincrónica. Esto significa que nuestro usuario ve un retraso mientras nos conectamos al transporte de correo para enviar el correo electrónico. Y si hay un problema de red por el que el correo falla, el usuario verá un error 500: no inspira precisamente confianza en una empresa que va a atarte a un cohete.

En lugar de eso, enviemos nuestros correos electrónicos de forma asíncrona. Esto significa que, durante la petición, el correo electrónico se enviará a una cola para ser procesado más tarde. ¡Symfony Messenger es perfecto para esto! Y obtenemos las siguientes ventajas: respuestas más rápidas para el usuario, reintentos automáticos si el correo electrónico falla, y la posibilidad de marcar los correos electrónicos para su revisión manual si fallan demasiadas veces.

## Instalación de Messenger y Doctrine Transport

¡Vamos a instalar Messenger! En tu terminal, ejecuta:

```terminal
composer require messenger
```

Al igual que Mailer, Messenger tiene el concepto de transporte: aquí es donde se envían los mensajes para ponerlos en cola. Utilizaremos el transporte Doctrine, ya que es el más fácil de configurar.

```terminal
composer require symfony/doctrine-messenger
```

En nuestro IDE, la receta añadía este `MESSENGER_TRANSPORT_DSN` a nuestro `.env`y por defecto era Doctrine: ¡perfecto! Este transporte añade una tabla a nuestra base de datos, así que técnicamente deberíamos crear una migración para ello. Pero... vamos a hacer un poco de trampa y hacer que cree automáticamente la tabla si no existe. Para permitirlo, configura`auto_setup` en `1`:

[[[ code('1ca3d7d6a9') ]]]

## Configurar los transportes de Messenger

La receta también ha creado este archivo `config/packages/messenger.yaml`. Descomenta la línea `failure_transport`:

[[[ code('5edbc5cfae') ]]]

Esto activa el sistema de revisión manual de fallos que he mencionado antes. A continuación, descomenta la línea `async` debajo de `transports`:

[[[ code('be54a9dce1') ]]]

Esto habilita el transporte configurado con `MESSENGER_TRANSPORT_DSN` y lo nombra `async`. No es obvio aquí, pero los mensajes fallidos se vuelven a intentar 3 veces, con un retraso creciente entre cada intento. Si un mensaje sigue fallando después de 3 intentos, se envía a`failure_transport`, llamado `failed`, así que descomenta también este transporte:

[[[ code('8b4b25e435') ]]]

## Configurar el enrutamiento de Messenger

La sección `routing` es donde le decimos a Symfony qué mensajes deben enviarse a qué transporte. Mailer utiliza una clase de mensaje específica para enviar correos electrónicos. Así que envía`Symfony\Component\Mailer\Messenger\SendEmailMessage` al transporte `async`:

[[[ code('d21f198106') ]]]

¡Ya está! Symfony Messenger y Mailer se acoplan perfectamente, así que no tenemos que cambiar nada en nuestro código.

¡Vamos a probarlo! De vuelta en nuestra aplicación... reserva un viaje. Volvemos a utilizar el transporte de pruebas de Mailtrap, así que podemos utilizar cualquier correo electrónico. Ahora observa cuánto más rápido se procesa.

¡Bum!

## Estado: En cola

Abre el perfil de la última petición y comprueba la sección "Correos electrónicos". Parece normal, pero fíjate en que el Estado es "En cola". Se envió a nuestro transporte Messenger, no a nuestro transporte Mailer. Tenemos esta nueva sección "Mensajes". Aquí podemos ver el`SendEmailMessage` que contiene nuestro objeto `TemplatedEmail`.

Salta a Mailtrap y actualiza... todavía nada. ¡Por supuesto! Tenemos que procesar nuestra cola.

## Procesar la cola

Vuelve a tu terminal y ejecuta:

```terminal
symfony console messenger:consume async -vv
```

Esto procesa nuestro transporte `async` (el `-vv` sólo añade más salida para que podamos ver lo que ocurre). ¡Muy bien! El mensaje se ha recibido y gestionado correctamente. Es decir: esto debería haber enviado realmente el correo electrónico.

Comprueba Mailtrap... ¡ya está aquí! Parece correcto... pero... haz clic en uno de nuestros enlaces.

¿Pero qué? Comprueba la URL: ¡es el dominio equivocado! Averigüemos qué parte de nuestro cohete de correo electrónico ha causado esto y arreglémoslo a continuación
