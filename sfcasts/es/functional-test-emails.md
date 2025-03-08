# Aserciones de correos electrónicos en pruebas funcionales

Bien, ¡hora de hacer pruebas! Si has explorado un poco la base de código, te habrás dado cuenta de que alguien (podría haber sido cualquiera... pero probablemente un canadiense) coló algunas pruebas en nuestro directorio `tests/Functional/`. ¿Pasarán? Ni idea Averigüémoslo

Ve a tu terminal y ejecuta:

```terminal
bin/phpunit
```

Uh-oh, 1 fallo. Uh-oh, porque, la verdad, ¡soy el simpático canadiense que las añadió y sé que pasaban al principio del curso! El fallo está en `BookingTest`, concretamente, `testCreateBooking`:

> Se esperaba un código de estado de redirección pero se obtuvo 500

en la línea 38 de `BookingTest`. Ahí es donde enviamos el correo electrónico... así que si buscamos a alguien a quien culpar, creo que deberíamos empezar por el canadiense, ejem, yo y mis salvajes maneras de enviar correos electrónicos.

## Foundry y el navegador

Abre `BookingTest.php`. Si has escrito pruebas funcionales con Symfony antes, esto puede parecer un poco diferente porque estoy utilizando algunas bibliotecas de ayuda. `zenstruck/foundry` nos da este rasgo `ResetDatabase` que limpia la base de datos antes de cada prueba. También nos proporciona este rasgo `Factories` que nos permite crear fijaciones de base de datos en nuestras pruebas. Y `HasBrowser` es de otro paquete - `zenstruck/browser` - y es esencialmente una envoltura fácil de usar alrededor del cliente de pruebas de Symfony.

`testCreateBooking` es la prueba real. En primer lugar, creamos un `Trip` en la base de datos con estos valores conocidos. A continuación, algunas preaserciones para asegurarnos de que no hay reservas ni clientes en la base de datos. Ahora, utilizamos `->browser()` para navegar a la página de un viaje, rellenar el formulario de reserva y enviarlo. A continuación, afirmamos que se nos redirige a una URL de reserva específica y comprobamos que la página contiene algún HTML esperado. Por último, utilizamos Foundry para hacer algunas afirmaciones sobre los datos de nuestra base de datos.

## `->throwExceptions()`

La línea 38 causó el fallo... estamos obteniendo un código de respuesta 500 al redirigir a esta página de reservas. los códigos de estado 500 en las pruebas pueden ser frustrantes porque puede ser difícil localizar la excepción real. Por suerte, Browser nos permite lanzar la excepción real. Al principio de esta cadena, añade `->throwExceptions()`:

[[[ code('af097ae44a') ]]]

De vuelta al terminal, vuelve a ejecutar las pruebas:

```terminal-silent
bin/phpunit
```

Ahora vemos una excepción No se puede encontrar la plantilla "@images/mars.png". Si recuerdas, esto se parece a cómo estamos incrustando las imágenes del viaje en nuestro correo electrónico. Está fallando porque`mars.png` no existe en `public/imgs`. Para simplificar, vamos a ajustar nuestra prueba para utilizar una imagen existente. Para nuestra fijación aquí, cambia `mars` por `iss`, y abajo, para`->visit()`: `/trip/iss`:

[[[ code('477b2e817b') ]]]

¡Ejecuta de nuevo las pruebas!

```terminal-silent
bin/phpunit
```

¡Pasa!

Parece que nuestro correo se envía... ¡pero confirmémoslo! Al final de esta prueba, quiero hacer algunas afirmaciones sobre el correo electrónico. Symfony lo permite, pero a mí me gusta utilizar una biblioteca que devuelva la diversión a las pruebas funcionales de correo electrónico.

## `zenstruck/mailer-test`

En tu terminal, ejecuta:

```terminal
composer require --dev zenstruck/mailer-test
```

Instalado y configurado... de nuevo en nuestra prueba, habilítalo añadiendo el rasgo `InteractsWithMailer`:

[[[ code('f0c9ca029a') ]]]

Empieza de forma sencilla, al final de la prueba, escribe `$this->mailer()->assertSentEmailCount(1);`:

[[[ code('2635967f3a') ]]]

## Variables de entorno específicas de la prueba

Nota rápida: `.env.local` -donde ponemos nuestras credenciales Mailtrap reales- no se lee ni se utiliza en el entorno `test`: nuestras pruebas sólo cargan `.env` y este archivo`.env.test`. Y en `.env`, `MAILER_DSN` está configurado como `null://null`. ¡Estupendo! Queremos que nuestras pruebas sean rápidas, y que no envíen realmente correos electrónicos.

¡Vuelve a ejecutarlas!

```terminal-silent
bin/phpunit
```

### `assertEmailSentTo()`

Pasa: ¡se envía 1 correo electrónico! Vuelve atrás y añade otra aserción: `->assertEmailSentTo()`. ¿Qué dirección de correo esperamos? La que rellenamos en el formulario: `bruce@wayne-enterprises.com`. Cópiala y pégala. El segundo argumento es el asunto: `Booking Confirmation for Visit Mars`:

[[[ code('c404b47d45') ]]]

¡Ejecuta las pruebas!

```terminal-silent
bin/phpunit
```

¡Sigue pasando! Y fíjate que ahora tenemos 20 afirmaciones en lugar de 19.

### `TestEmail`

¡Pero podemos ir más allá! En lugar de una cadena para el asunto de esta afirmación, utiliza un cierre con `TestEmail $email` como argumento:

[[[ code('1a0df12e68') ]]]

Dentro, ahora podemos hacer muchas más afirmaciones sobre este correo electrónico. Como ya no estamos comprobando el asunto, añade primero ésta:`$email->assertSubject('Booking Confirmation for Visit Mars')`:

[[[ code('51bed1a083') ]]]

¡Y podemos encadenar más afirmaciones!

Escribe `->assert` para ver qué sugiere nuestro editor. Míralas todas... Fíjate en `assertTextContains`y `assertHtmlContains`. Puedes hacer una aserción sobre cada una de ellas por separado, pero, como es una buena práctica que ambas contengan los detalles importantes, utiliza `assertContains()` para comprobar las dos a la vez. Comprueba `Visit Mars`:

[[[ code('365bc31cb9') ]]]

Es importante comprobar los enlaces, así que asegúrate de que está la URL de reserva:`->assertContains('/booking/'.`. Ahora, `BookingFactory::first()->getUid()`:

[[[ code('923f543ca7') ]]]

esto busca la primera entidad `Booking` en la base de datos (que sabemos por lo anterior que sólo hay una), y obtiene su `uid`.

Incluso podemos comprobar el archivo adjunto: `->assertHasFile('Terms of Service.pdf')`:

[[[ code('db553d20bc') ]]]

Puedes comprobar el tipo de contenido y el contenido del archivo mediante argumentos adicionales, pero por ahora me basta con comprobar que el archivo adjunto existe.

¡Vamos, pruebas, vamos!

```terminal-silent
bin/phpunit
```

Impresionante, ¡25 aserciones ahora!

### `->dd()`

Una última cosa: si alguna vez tienes problemas para averiguar por qué no pasa una de estas aserciones de correo electrónico, encadena un `->dd()`:

[[[ code('1511c955bb') ]]]

y ejecuta tus pruebas. Cuando llegue a ese `dd()`, vuelca el correo electrónico para ayudarte a depurar. ¡No olvides eliminarlo cuando hayas terminado!

A continuación, quiero añadir un segundo correo electrónico a nuestra aplicación. Para evitar la duplicación y mantener la coherencia, crearemos un diseño de correo electrónico Twig que ambos compartan.
