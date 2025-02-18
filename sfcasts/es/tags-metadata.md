# Seguimiento de correos electrónicos con etiquetas y metadatos

Ya estamos enviando correos electrónicos de verdad. Comprobemos que nuestros enlaces funcionan... ¡Todo bien!

Mailtrap puede hacer algo más que enviar y depurar correos electrónicos: también podemos rastrear correos electrónicos y eventos de correo electrónico. Entra en Mailtrap y haz clic en "Email API/SMTP". Este panel nos muestra un resumen de cada correo electrónico que hemos enviado. Haz clic en "Registros de correo electrónico" para ver la lista completa. ¡Aquí está nuestro correo electrónico! Haz clic en él para ver los detalles.

Esto te resulta familiar... es similar a la interfaz de pruebas de Mailtrap. Podemos ver detalles generales, un análisis de spam y mucho más. Pero esto es realmente genial: haz clic en "Historial de Eventos". Esto muestra todos los eventos que ocurrieron durante el flujo de este correo electrónico. Podemos ver cuándo se envió, cuándo se entregó, ¡incluso cuándo lo abrió el destinatario! Cada evento tiene detalles adicionales, como la dirección IP que abrió el correo electrónico. Súper útil para diagnosticar problemas de correo electrónico. Mailtrap también tiene una función de seguimiento de enlaces que, si está activada, mostraría qué enlaces se pulsaron en el correo electrónico.

De vuelta a la pestaña "Información del correo electrónico", desplázate un poco hacia abajo. Observa que falta la "Categoría". En realidad, esto no es un problema, pero una "categoría" es una cadena que ayuda a organizar los distintos correos electrónicos que envía tu aplicación. Esto facilita la búsqueda y puede darnos estadísticas interesantes como "¿cuántos correos electrónicos de registro de usuarios enviamos el mes pasado?".

Symfony Mailer llama a esto una "etiqueta" que puedes añadir a los correos electrónicos. El puente Mailtrap toma esta etiqueta y la convierte en su "categoría". ¡Vamos a añadir una!

En `TripController::show()`, tras la creación del correo electrónico, escribe:`$email->getHeaders()->add(new TagHeader());` - utiliza `booking` como nombre.

Mailer también tiene una cabecera especial de metadatos que puedes añadir a los correos electrónicos. Se trata de un almacén clave-valor de forma libre para añadir datos adicionales. El puente Mailtrap los convierte en lo que ellos llaman "variables personalizadas".

Vamos a añadir un par:

`$email->getHeaders()->add(new MetadataHeader('booking_uid', $booking->getUid()));`

Y:

`$email->getHeaders()->add(new MetadataHeader('customer_uid', $customer->getUid()));`

A cada correo electrónico de reserva se adjunta ahora una referencia al cliente y a la reserva. ¡Fantástico!

Para ver cómo se verán en Mailtrap, salta a nuestra aplicación y reserva un viaje (recuerda que aún estamos utilizando el envío de producción, así que utiliza tu correo electrónico personal). Comprueba nuestra bandeja de entrada... aquí está. De vuelta en Mailtrap, vuelve a los registros de correo electrónico... y actualiza... ¡ahí está! Haz clic en él. Ahora, en esta pestaña "Información de correo electrónico", ¡vemos nuestra categoría "reserva"! Un poco más abajo, están nuestros metadatos o "variables personalizadas".

Para filtrar en la "categoría", ve a los registros de correo electrónico. En este cuadro de búsqueda, elige "Categorías". Este filtro enumera todas las categorías que hemos utilizado. Selecciona "reserva" y "Buscar". Esto ya está más organizado que los tubos Jeffries de ingeniería

¡Esto es el envío de correos electrónicos de producción con Mailtrap! Para facilitar las cosas en los próximos capítulos, volvamos a utilizar Mailtrap en pruebas. En `.env.local`, descomenta la prueba de Mailtrap `MAILER_DSN` y comenta el envío de producción `MAILER_DSN`.

A continuación, vamos a utilizar Symfony Messenger para enviar nuestros correos electrónicos de forma asíncrona. ¡Ooo!
