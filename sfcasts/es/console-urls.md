# URLs en la Consola

Cuando cambiamos al envío de correo electrónico asíncrono, rompimos nuestros enlaces de correo electrónico. Está utilizando`localhost` como nuestro dominio, pero esto no es correcto. 

De vuelta a nuestra aplicación en funcionamiento, podemos obtener una pista de lo que está pasando mirando el perfil de la petición que envió el correo electrónico. Recuerda que ahora nuestro correo electrónico está marcado como "en cola". Ve a la pestaña "Mensajes" y busca el mensaje: `SendEmailMessage`. Dentro está nuestro objeto `TemplatedEmail`. Ábrelo. Observa que `htmlTemplate` es nuestra plantilla Twig, pero `html` es `null`. El correo electrónico no se está procesando antes de ser enviado a la cola. Esto significa que el correo electrónico se está renderizando cuando es procesado por el comando `messenger:consume`. Este es el problema.

Este es el problema. `messenger:consume` es un comando CLI, y al generar URLs absolutas en estos, Symfony no sabe cuál debe ser el dominio (o si debe ser http o https). Entonces, ¿por qué lo hace cuando está en un controlador? En un controlador, Symfony puede acceder a la petición actual para obtener esta información. Un comando CLI no tiene ninguna petición disponible, así que por defecto es `http://localhost`.

¡Cambiar este valor por defecto es la solución!

De vuelta a nuestro IDE, abre `config/packages/routing.yaml`. En `framework`, `routing`, estos comentarios explican exactamente este problema. Descomenta `default_uri` y ponlo en`https://universal-travel.com` - ¡nuestros abogados están a punto de llegar a un acuerdo!

En desarrollo, sin embargo, tenemos que utilizar la URL de nuestro servidor local de desarrollo. Para mí, es`127.0.0.1:8000`, pero esto podría cambiar o ser personalizado por otros desarrolladores de tu equipo.

He aquí un truco: el servidor Symfony CLI establece una variable de entorno especial con el valor correcto que podemos aprovechar.

De vuelta en nuestra configuración de enrutamiento, añade una nueva sección: `when@dev:`, `framework:`, `router:`,`default_uri:` y establécela en `%env(SYMFONY_PROJECT_DEFAULT_ROUTE_URL)%`. Esta variable de entorno sólo estará disponible si el servidor CLI de Symfony se está ejecutando y estás ejecutando comandos a través de `symfony console` (no `bin/console`). Para asegurarnos de que no obtenemos una excepción si esta variable de entorno no está establecida, vamos a establecer un valor por defecto. También en `when@dev`, añade `parameters:` con `env(SYMFONY_PROJECT_DEFAULT_ROUTE_URL):`fijado en `http://localhost` - el valor por defecto original.

Vamos a probarlo, pero antes, vuelve a tu terminal. Como hemos hecho algunos cambios en nuestra configuración, tenemos que reiniciar el comando `messenger:consume`. Detenlo con`CTRL+C` y ejecútalo de nuevo:

```terminal
symfony console messenger:consume async -vv
```

Genial, ahora se han recogido esos cambios. Volvamos a nuestra aplicación... ¡y reservemos un viaje! Vuelve rápidamente al terminal y veremos que el mensaje se ha procesado.

Pasa a Mailtrap y... ¡aquí está! Momento de la verdad: pulsa un enlace... Genial, ¡vuelve a funcionar!

Si eres como yo, probablemente te parezca un rollo tener que mantener este comando `messenger:consume` ejecutándose en un terminal durante el desarrollo. Además, tener que reiniciarlo cada vez que haces un cambio en el código o en la configuración es súper molesto.

Aquí tienes otro truco Symfony CLI: en tu IDE, abre este archivo `.symfony.local.yaml`. Es la configuración del servidor Symfony CLI para este proyecto. Comprueba esta clave `workers`, que te permite definir procesos adicionales para que se ejecuten en segundo plano cuando inicies el servidor. Nosotros ya tenemos configurado este comando tailwind.

Añade otro trabajador para `messenger:consume`. Llámalo `messenger` y configura el `cmd` como`['symfony', 'console', 'messenger:consume', 'async']`. Esto resuelve el problema de tener que mantenerlo en ejecución en una ventana de terminal separada, pero ¿qué pasa con el reinicio? Symfony CLI te lo soluciona! Añade una clave `watch` y establécela en los directorios donde tengas archivos que, al cambiar, deberían provocar un reinicio:`config`, `src`, `templates` y `vendor`.

De vuelta a tu terminal, reinicia el servidor con `symfony server:stop` y `symfony serve -d`. Ahora, nuestro `messenger:consume` se está ejecutando en segundo plano. Para comprobarlo, ejecuta:

```terminal
symfony server:status
```

Vemos 3 trabajadores ejecutándose: el primero es el servidor web PHP real. El segundo es nuestro trabajador`tailwind:build` existente, y el tercero es nuestro nuevo trabajador `messenger:consume`. ¡Me parece genial!

A continuación, ¡quiero enseñarte cómo hacer afirmaciones sobre los correos electrónicos enviados en tus pruebas funcionales!
