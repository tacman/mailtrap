# Archivos adjuntos e imágenes

¿Podemos añadir un archivo adjunto a nuestro correo electrónico? Por supuesto que sí Hacerlo manualmente es un proceso complejo y delicado. Por suerte, el Mailer de Symfony te lo pone muy fácil.

En el directorio `tutorial/`, verás un archivo `terms-of-service.pdf`. Muévelo a `assets/`, aunque podría estar en cualquier sitio.

En `TripController::show()`, necesitamos obtener la ruta a este archivo. Añade un nuevo argumento`string $termsPath` y con el atributo `#[Autowire]` y`%kernel.project_dir%/assets/terms-of-service.pdf'`.

Genial, ¿verdad?

Abajo, donde creamos el correo electrónico, escribe `->attach` y mira qué te sugiere tu IDE. Hay dos métodos: `attach()` y `attachFromPath()`.`attach()` es para añadir el contenido en bruto de un archivo (como cadena o flujo). Como nuestro adjunto es un archivo real en nuestro sistema de archivos, utiliza `attachFromPath()` y pasa a`$termsPath` un nombre amigable como `Terms of Service.pdf`. Éste será el nombre del archivo cuando se descargue. como segundo. Si no se pasa el segundo argumento, por defecto será el nombre del archivo.

Adjunto hecho. ¡Ha sido fácil!

A continuación, vamos a añadir la imagen del viaje al correo electrónico de confirmación de la reserva. Pero no la queremos como archivo adjunto. La queremos incrustada en el HTML. Hay dos formas de hacerlo: Primero, la forma estándar de la web: utilizar una etiqueta `<img>` con una URL absoluta a la imagen alojada en tu sitio. Pero vamos a ser inteligentes e incrustar la imagen directamente en el correo electrónico. Esto es como un archivo adjunto, pero no está disponible para su descarga, sino que haces referencia a ella en el HTML de tu correo electrónico.

Primero, al igual que hicimos con nuestros archivos CSS externos, tenemos que hacer que nuestras imágenes estén disponibles en Twig. `public/imgs/` contiene las imágenes de nuestro viaje y todas se llaman`<trip-slug.png>`.

En `config/packages/twig.yaml`, añade otra entrada `paths`:`%kernel.project_dir%/public/imgs: images`. Ahora podemos acceder a este directorio en Twig con`@images/`. Cierra este archivo.

## La variable `email` 

Cuando utilizas Twig para procesar tus correos electrónicos, por supuesto tienes acceso a las variables pasadas a `->context()` pero también hay una variable secreta disponible llamada `email`. Ésta es una instancia de `WrappedTemplatedEmail` y te da acceso a cosas relacionadas con el correo electrónico como el asunto, la ruta de retorno, de, a, etc. Lo que nos interesa es este método `image()`. ¡Es el que se encarga de incrustar imágenes!

¡Vamos a utilizarlo!

En `booking_confirmation.html.twig`, debajo de este `<h1>`, añade una etiqueta `<img>` con algunas clases: `trip-image` de nuestro archivo CSS personalizado y `float-center` de Foundation.

Para el `src`, escribe `{{ email.image() }}`, este es el método de ese objeto`WrappedTemplatedEmail`. Dentro, escribe `'@images/%s.png'|format(trip.slug)`. Añade un `alt="{{ trip.name }}"` y cierra la etiqueta.

¡Imagen incrustada! ¡Vamos a comprobarlo!

De vuelta en la aplicación, reserva un viaje... y comprueba Mailtrap. Aquí está nuestro correo electrónico y... ¡aquí está nuestra imagen! ¡Somos lo máximo! Encaja perfectamente e incluso tiene unas bonitas esquinas redondeadas.

Aquí arriba, en la parte superior derecha, vemos "Adjunto (1)", tal y como esperábamos. Haz clic en él y elige "Condiciones de servicio.pdf" para descargarlo. Ábrelo y... ¡ahí está nuestro PDF! Nuestros abogados espaciales hicieron que este documento fuera realmente divertido, ¡y sólo nos costó 500 $/hora! ¡Dinero del inversor bien invertido!

A continuación, vamos a eliminar la necesidad de poner manualmente un `from` a cada correo electrónico utilizando eventos para añadirlo globalmente.
