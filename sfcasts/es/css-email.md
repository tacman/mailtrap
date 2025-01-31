# CSS en el correo electrónico

El CSS en el correo electrónico requiere... cierto cuidado especial. Pero, ¡pffff, somos desarrolladores de Symfony! ¡Avancemos temerariamente y veamos qué pasa!

## Añade una clase CSS

En `email/booking_confirmation.html.twig`, añade una etiqueta `<style>` en `<head>` y añade una clase `.text-red` que establezca `color` en `red`:

[[[ code('46cf9f4ee1') ]]]

Ahora, añade esta clase a la primera etiqueta `<p>`:

[[[ code('c04f312da2') ]]]

En nuestra aplicación, reserva otro viaje para nuestro buen amigo Steve. ¡Realmente está acumulando parsecs! ¿Crees que le interesaría la tarjeta de crédito platino Universal Travel?

En Mailtrap, comprueba el correo electrónico. Vale, este texto está en rojo como esperábamos... entonces, ¿cuál es el problema? Comprueba el código HTML para obtener una pista. Pasa el ratón por encima del primer error:

> La etiqueta `style` no es compatible con todos los clientes de correo electrónico.

El problema más importante es el atributo `class`: tampoco es compatible con todos los clientes de correo electrónico. ¿Podemos viajar al espacio pero no podemos utilizar clases CSS en los correos electrónicos? Sí, es un mundo extraño.

## CSS en línea

¿La solución? Haz como si estuviéramos en 1999 e inlinea todos los estilos. Así es, por cada etiqueta que tenga un `class`, tenemos que encontrar todos los estilos aplicados de la clase y añadirlos como atributo `style`. Manualmente, esto sería suuuuuck... Por suerte, ¡Symfony Mailer te tiene cubierto!

## `inline_css` Filtro Twig

En la parte superior de este archivo, añade una etiqueta Twig `apply` con el filtro `inline_css`. Si no estás familiarizado, la etiqueta `apply` te permite aplicar cualquier filtro Twig a un bloque de contenido. Al final del archivo, escribe `endapply`:

[[[ code('04b6a30406') ]]]

Reserva otro viaje para Steve. Uy, ¡un error! El filtro `inline_css` forma parte de un paquete que no tenemos instalado, ¡pero el mensaje de error nos da el comando `composer require` para instalarlo! Cópialo, salta a tu terminal y pégalo:

```terminal
composer require twig/cssinliner-extra
```

De vuelta en la aplicación, vuelve a reservar el viaje de Steve y comprueba el correo electrónico en Mailtrap.

El HTML parece el mismo, pero comprueba la Fuente HTML. ¡Este atributo `style` se añadió automáticamente a la etiqueta `<p>`! Es increíble y mucho mejor que hacerlo manualmente.

Si tu aplicación envía varios correos electrónicos, querrás que tengan un estilo coherente a partir de un archivo CSS real, en lugar de definirlo todo en una etiqueta `<style>` en cada plantilla. Por desgracia, no es tan sencillo como enlazar a un archivo CSS en la etiqueta `<head>`. Eso es algo que tampoco gusta a los clientes de correo electrónico.

¡No hay problema!

## Archivo CSS externo

Crea un nuevo archivo `email.css` en `assets/styles/`. Copia el CSS de la plantilla de correo electrónico y pégalo aquí:

[[[ code('499a01fe89') ]]]

De vuelta en la plantilla, celébralo eliminando la etiqueta `<style>`.

Entonces, ¿cómo podemos hacer que nuestro correo electrónico utilice el archivo CSS externo? ¡Con trucos, por supuesto!

## Espacio de nombres "styles" de Twig

Abre `config/packages/twig.yaml` y crea una clave `paths`. Dentro, añade`%kernel.project_dir%/assets/styles: styles`:

[[[ code('36096b9757') ]]]

Lo sé, esto parece raro, pero crea un espacio de nombres Twig personalizado. Gracias a esto ahora podemos renderizar plantillas dentro de este directorio con el prefijo `@styles/`. Pero, ¡espera un momento! `email.css`
¡el archivo no es una plantilla Twig que queramos renderizar! No pasa nada, sólo necesitamos acceder a ella, no parsearla como Twig.

## `inline_css()` con `source()`

De vuelta en `booking_confirmation.html.twig`, para el argumento de `inline_css`, utiliza`source('@styles/email.css')`:

[[[ code('1bdadb199f') ]]]

La función `source()` toma el contenido en bruto de un archivo.

Salta a nuestra aplicación, reserva otro viaje y comprueba el correo electrónico en Mailtrap. ¡Parece el mismo! Aquí el texto es rojo. Si comprobamos el código fuente HTML, las clases ya no están en `<head>`, pero los estilos siguen alineados: se están cargando desde nuestra hoja de estilos externa, ¡es genial!

A continuación, vamos a mejorar el HTML y el CSS para que este correo electrónico sea digno de la bandeja de entrada de Steve y del costoso viaje que acaba de reservar.
