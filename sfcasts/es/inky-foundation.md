# Estilo de correo electrónico real con Inky y Foundation CSS

Para que este correo electrónico tenga un aspecto realmente elegante, tenemos que mejorar el HTML y el CSS.

Empecemos por el CSS. Con el CSS estándar de un sitio web, es probable que hayas utilizado un framework CSS como Tailwind (que utiliza nuestra aplicación), Bootstrap o Foundation. ¿Existe algo así para los correos electrónicos? Sí Y es aún más importante utilizar uno para los correos electrónicos porque hay muchos clientes de correo electrónico que los renderizan de forma diferente.

Para los correos electrónicos, recomendamos utilizar Foundation, ya que tiene un marco específico para correos electrónicos. Busca en Google "Foundation CSS" y encontrarás esta página. Foundation

Descarga el kit de inicio para la "Versión CSS". Este archivo zip incluye un archivo `foundation-emails.css` que es el "framework" real.

Ya lo he incluido en el directorio `tutorials/`. Cópialo en`assets/styles/`.

En nuestro `booking_confirmation.html.twig`, el filtro `inline_css` puede tomar varios argumentos. Haz que el primer argumento sea `source('@styles/foundation-emails.css')`y utiliza `email.css` para el segundo argumento. Esto contendrá estilos personalizados y anulaciones.

Abriré `email.css` y pegaré algo de CSS personalizado para nuestro correo electrónico.

Ahora tenemos que mejorar nuestro HTML. Pero ¡qué noticia más rara! La mayoría de las cosas que utilizamos para dar estilo a los sitios web no funcionan en los correos electrónicos. Por ejemplo, no podemos utilizar Flexbox ni Grid. En su lugar, tenemos que utilizar tablas para la maquetación. Tablas Tablas, dentro de tablas, dentro de tablas. ¡Qué asco!

Por suerte, hay un lenguaje de plantillas que podemos utilizar para hacer esto más fácil. Busca "inky templating language" para encontrar esta página. Inky está desarrollado por la Fundación Zurb. Zurb, Inky, Foundation... ¡estos nombres encajan perfectamente con nuestro tema espacial! ¡Y todos funcionan juntos!

Puedes hacerte una idea de cómo funciona en la vista general. Este es el HTML necesario para un simple correo electrónico. ¡Es un infierno de tabla! Haz clic en la pestaña "Cambiar a Inky". ¡Guau! ¡Esto es mucho más limpio! Escribimos en un formato más legible e Inky lo convierte en la tabla-horror necesaria para los correos electrónicos.

Incluso hay "componentes Inky": botones, llamadas, cuadrículas, etc.

En tu terminal, instala un filtro Twig de Inky que convierta nuestro marcado Inky en HTML.

```terminal
composer require twig/inky-extra
```

En `booking_confirmation.html.twig`, añade el filtro `inky_to_html`a `apply`, canalizando `inline_css` a continuación. En primer lugar, aplicamos el filtro Inky y, a continuación, alineamos el CSS.

Copiaré algunas marcas Inky para nuestro correo electrónico. Tenemos un `<container>`, con `<rows>` y`<columns>`. Este será un correo electrónico de una sola columna, pero puedes tener tantas columnas como necesites. Este `<spacer>` añade espacio vertical para respirar.

¡Veamos este correo electrónico en acción! Reserva un nuevo viaje para Steve, ¡ups, debe ser una fecha en el futuro, y reserva!

Comprueba Mailtrap y encuentra el correo electrónico. ¡Vaya! ¡Esto tiene mucho mejor aspecto! Podemos utilizar este pequeño widget que Mailtrap proporciona para ver cómo se verá en móviles y tabletas. 

Mirando el "HTML Check", parece que tenemos algunos problemas, pero, creo que mientras estemos usando Foundation e Inky como es debido, deberíamos estar bien.

Comprueba los botones. "Gestionar reserva", sí, funciona. "Mi cuenta", sí, también funciona. ¡Eso ha sido un éxito rápido gracias a Foundation e Inky!

A continuación, vamos a mejorar aún más nuestro correo electrónico incrustando la imagen del viaje y haciendo felices a los abogados añadiendo un archivo adjunto en PDF con las "condiciones del servicio".
