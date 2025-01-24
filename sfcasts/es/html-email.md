# Correos electrónicos en HTML

Los correos electrónicos siempre deben tener una versión en texto plano, pero también pueden tener una versión en HTML. ¡Y ahí es donde está la diversión! 
¡Es hora de hacer este correo electrónico más presentable añadiendo una versión HTML!

En `templates/email/`, copia `booking_confirmation.txt.twig` y nómbralo `booking_confirmation.html.twig`. En el nuevo archivo, el correo electrónico actúa un poco como una página HTML completa. Envuélvelo todo en una etiqueta `<html>`, añade una `<head>` vacía y envuelve el contenido en una `<body>`. También añadiré algunas etiquetas `<p>` para conseguir algo de espaciado... y una etiqueta `<br>` después de "Saludos", para añadir un salto de línea.

Ahora esta URL puede vivir en una etiqueta `<a>` adecuada. Date un poco de espacio y copia la URL. Añade una etiqueta`<a>` con `href` y pégala dentro.

Por último, tenemos que decirle a Mailer que utilice esta plantilla HTML. En `TripController::show()`, encima de `->textTemplate()`, añade `->htmlTemplate()` con `email/booking_confirmation.html.twig`.

Pruébalo reservando un viaje: `Steve`, `steve@minecraft.com`, cualquier fecha en el futuro, reserva... y luego comprueba Mailtrap. El correo electrónico tiene el mismo aspecto, ¡pero ahora tenemos una pestaña HTML!

Ah, y la "Comprobación de HTML" está muy bien. Te da un indicador de qué porcentaje de clientes de correo electrónico admiten el HTML de este correo. Por si no lo sabías, los clientes de correo electrónico son un coñazo: es como volver a los 90 con distintos navegadores. De vuelta a la pestaña HTML, haz clic en el enlace para asegurarte de que funciona. ¡Funciona!

Así que ahora nuestro correo electrónico tiene una versión en texto y otra en HTML, pero... es un poco pesado mantener ambas. De todas formas, ¿quién utiliza un cliente de correo electrónico sólo de texto? Probablemente nadie o un porcentaje muy bajo de tus usuarios.

Probemos algo: en `TripController::show()`, elimina la línea `->textTemplate()`. Ahora nuestro correo sólo tiene versión HTML.

Reserva otro viaje y comprueba el correo electrónico en Mailtrap. ¿Todavía tenemos una versión de texto? Se parece casi a nuestra plantilla de texto, pero con algún espaciado extra. Si envías un correo electrónico sólo con una versión HTML, Symfony Mailer crea automáticamente una versión de texto pero elimina las etiquetas. Es una buena alternativa, pero no es perfecta. ¿Ves lo que falta? El enlace Eso es... algo crítico... El enlace ha desaparecido porque estaba en el atributo `href` de la etiqueta de anclaje. Lo perdimos al eliminar las etiquetas.

Entonces, ¿necesitamos mantener siempre manualmente una versión de texto? No necesariamente. He aquí un pequeño truco.

En tu terminal, ejecuta:

```terminal
composer require league/html-to-markdown
```

Este es un paquete que convierte HTML a markdown. Espera, ¿qué? ¿No solemos convertir markdown a HTML? Sí, pero para los correos HTML, ¡esto es perfecto! ¿Y adivina qué? ¡No tenemos que hacer nada más! ¡Symfony Mailer utiliza automáticamente este paquete en lugar de limitarse a eliminar las etiquetas si están disponibles!

Reserva otro viaje y comprueba el correo electrónico en Mailtrap. El HTML parece el mismo, pero comprueba la versión de texto. ¡Nuestra etiqueta de anclaje se ha convertido en un enlace markdown! No es perfecto, ¡pero al menos está ahí! Si necesitas un control total, necesitarás esa plantilla de texto aparte, pero creo que esto es suficiente. De vuelta en tu IDE, borra `booking_confirmation.txt.twig`.

A continuación, ¡avivaremos este HTML con CSS!
