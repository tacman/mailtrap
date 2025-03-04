# Aserciones de correos electrónicos en pruebas funcionales

Bien, ¡hora de hacer pruebas! Si has explorado un poco el código base, te habrás dado cuenta de que ya hay algunas pruebas en nuestro directorio `tests/Functional`. Vamos a ejecutarlas. Ve a tu terminal y ejecuta:

```terminal
bin/phpunit
```

Uh-oh, 1 fallo. ¡Ya sé que estas pasaban al principio del curso! Podemos ver que el fallo está en `BookingTest`, concretamente, en `testCreateBooking`. El fallo es "Esperaba un código de estado de redirección pero obtuvo 500" y se originó en la línea 38 de `BookingTest`. Al crear una reserva es donde ahora estamos enviando un correo electrónico - ¡eso debe tener algo que ver con este fallo!

Abre `BookingTest.php`. Si has escrito pruebas funcionales con Symfony antes, esto puede parecer un poco diferente. Estoy utilizando algunas bibliotecas de ayuda. `zenstruck/foundry` nos proporciona este rasgo `ResetDatabase` que limpia la base de datos antes de cada prueba. Además, nos proporciona este rasgo `Factories` que nos permite crear fijaciones de base de datos en nuestras pruebas. Este `HasBrowser` es de otro paquete llamado `zenstruck/browser` - es esencialmente una envoltura fácil de usar alrededor del cliente de pruebas de Symfony.

`testCreateBooking` es nuestra prueba real. En primer lugar, vamos a crear un `Trip` en la base de datos con estos valores conocidos. A continuación, algunas preaserciones para asegurarnos de que no hay reservas ni clientes en la base de datos. Ahora, utilizamos `->browser()` para ir a la página de un viaje, rellenar el formulario de reserva y enviarlo. A continuación, afirmamos que se nos redirige a una URL de reserva específica y comprobamos que la página contiene el HTML esperado. Por último, estamos utilizando Foundry para hacer algunas afirmaciones sobre los datos de nuestra base de datos.

La línea 38 causó el fallo... estamos obteniendo un código de respuesta 500 al redirigir a esta página de reservas. los códigos de estado 500 en las pruebas pueden ser frustrantes porque puede ser difícil localizar la excepción real. Por suerte, Browser nos permite lanzar la excepción real. Al principio de esta cadena, añade `->throwExceptions()`.

De vuelta al terminal, vuelve a ejecutar las pruebas:

```terminal-silent
bin/phpunit
```

Ahora vemos una excepción No se puede encontrar la plantilla "@images/mars.png". Si recuerdas, esto se parece a cómo estamos incrustando las imágenes del viaje en nuestro correo electrónico. Está fallando porque`mars.png` no existe en `public/imgs`. Para simplificar, vamos a ajustar nuestra prueba para utilizar una imagen existente. Para nuestra fijación aquí, cambia `mars` por `iss`, y abajo, para`->visit()`: `/trip/iss`.

¡Ejecuta de nuevo las pruebas!

```terminal-silent
bin/phpunit
```

¡Pasa!

Parece que nuestro correo electrónico se está enviando, ¡pero confirmémoslo! Al final de esta prueba, quiero hacer algunas afirmaciones sobre el correo electrónico. Symfony tiene algunas aserciones de prueba de correo electrónico fuera de la caja, pero me gusta usar una biblioteca que sea más fácil de usar.

En tu terminal, ejecuta:

```terminal
composer require --dev zenstruck/mailer-test
```

Instalado y configurado... de nuevo en nuestra prueba, habilítalo añadiendo el rasgo `InteractsWithMailer`.

Empecemos de forma sencilla, al final de nuestra prueba, escribe `$this->mailer()->assertSentEmailCount(1);`.

Algo rápido a tener en cuenta: `.env.local`, donde ponemos nuestras credenciales Mailtrap reales, no se mira en el entorno de pruebas. Sólo `.env` y este `.env.test`, y, si recuerdas, nuestro `MAILER_DSN` es `null://null` en `.env`. ¡Estupendo! Queremos que nuestras pruebas sean rápidas, y no que envíen realmente correos electrónicos.

¡Vuelve a ejecutar nuestras pruebas!

```terminal-silent
bin/phpunit
```

Pasa: ¡se envía 1 correo electrónico! Vuelve atrás y añade otra aserción: `->assertEmailSentTo()`. ¿Qué correo esperamos? El que rellenamos en el formulario: `bruce@wayne-enterprises.com`. Copia y pega eso. El segundo argumento es el asunto: `Booking Confirmation for Visit Mars`.

¡Ejecuta las pruebas!

```terminal-silent
bin/phpunit
```

¡Sigue pasando! Y fíjate que ahora tenemos 20 aserciones en lugar de 19.

¡Podemos ir más lejos! En lugar de una cadena para el asunto en esta aserción, utiliza un cierre con `TestEmail $email` como argumento. Dentro, ahora podemos hacer muchas más afirmaciones sobre este correo electrónico. Como ya no estamos comprobando el asunto, añade primero ésta:`$email->assertSubject('Booking Confirmation for Visit Mars')`. Podemos encadenar más aserciones! Escribe `->assert` para ver qué nos sugiere nuestro IDE. Míralas todas... Fíjate en `assertTextContains`y `assertHtmlContains`. Puedes hacer una aserción sobre cada una de ellas por separado, pero, como es una buena práctica que ambas contengan los detalles importantes, utiliza `assertContains()` para comprobar las dos a la vez. Comprueba `Visit Mars`.

Es importante comprobar los enlaces, así que asegúrate de que está la URL de reserva:`->assertContains('/booking/'.`. Ahora, `BookingFactory::first()->getUid()` - esto busca la primera entidad `Booking` en la base de datos (que sabemos por lo anterior que sólo hay una), y obtiene su `uid`.

Por último, comprueba el archivo adjunto: `->assertHasFile('Terms of Service.pdf')`. Si profundizas en esta aserción, puedes comprobar opcionalmente el tipo de contenido y el contenido real del archivo mediante argumentos adicionales. Por ahora me basta con comprobar que el archivo adjunto existe.

¡Ejecuta de nuevo nuestras pruebas!

```terminal-silent
bin/phpunit
```

Genial, ¡ya tenemos 25 aserciones!

Una última cosa: si alguna vez tienes problemas para averiguar por qué no pasa una de estas aserciones de correo electrónico, encadena un `->dd()` y ejecuta tus pruebas. Cuando llegue a ese `dd()`, vuelca el correo electrónico para ayudarte a depurar. ¡No olvides eliminarlo cuando hayas terminado!

A continuación, quiero añadir un segundo correo electrónico a nuestra aplicación. Para evitar la duplicación, crearemos un diseño de correo electrónico Twig que ambos compartan.
