# Generar URLs en el entorno CLI

Cuando cambiamos al envío de correo electrónico asíncrono, ¡rompimos nuestros enlaces de correo electrónico! Está utilizando`localhost` como nuestro dominio, raro e incorrecto.

De vuelta en nuestra aplicación, podemos obtener una pista de lo que está pasando mirando el perfil de la petición que envió el correo electrónico. Recuerda que ahora nuestro correo electrónico está marcado como "en cola". Ve a la pestaña "Mensajes" y busca el mensaje: `SendEmailMessage`. Dentro está el objeto `TemplatedEmail`. Ábrelo. Interesante! `htmlTemplate` es nuestra plantilla Twig pero `html` es `null`! ¿No debería ser el HTML renderizado de esa plantilla? Este pequeño detalle es importante: la plantilla de correo electrónico no se renderiza cuando nuestro controlador envía el mensaje a la cola. No! la plantilla no se renderiza hasta más tarde, cuando ejecutamos `messenger:consume`.

¿Qué importancia tiene esto? Bueno `messenger:consume` es un comando CLI, y cuando se generan URLs absolutas en la CLI, Symfony no sabe cuál debe ser el dominio (o si debe ser http o https). Entonces, ¿por qué lo hace cuando está en un controlador? En un controlador, Symfony utiliza la petición actual para averiguarlo. En un comando CLI, no hay petición, así que se rinde y utiliza `http://localhost`.

Vamos a decirle cuál debe ser el dominio.

De vuelta a nuestro IDE, abre `config/packages/routing.yaml`. En `framework`, `routing`, estos comentarios explican exactamente esta cuestión. Descomenta `default_uri` y ajústalo a`https://universal-travel.com` - ¡nuestros abogados están a punto de llegar a un acuerdo!

En desarrollo, sin embargo, tenemos que utilizar la URL de nuestro servidor local de desarrollo. Para mí, es`127.0.0.1:8000`, pero puede ser diferente para otros miembros del equipo. Sé que Bob utiliza `bob.is.awesome:8000` y más o menos es así.

Para que esto sea configurable, hay un truco: el servidor Symfony CLI establece una variable de entorno especial con el dominio llamado `SYMFONY_PROJECT_DEFAULT_ROUTE_URL`.

De vuelta en nuestra configuración de enrutamiento, añade una nueva sección: `when@dev:`, `framework:`, `router:`,`default_uri:` y establécela en `%env(SYMFONY_PROJECT_DEFAULT_ROUTE_URL)%`. Esta variable de entorno sólo estará disponible si el servidor CLI de Symfony se está ejecutando y estás ejecutando comandos a través de `symfony console` (no `bin/console`). Para evitar un error si falta la variable, establece una por defecto. Todavía en `when@dev`, añade`parameters:` con `env(SYMFONY_PROJECT_DEFAULT_ROUTE_URL):`ajustado a `http://localhost`. Esta es la forma estándar de Symfony de establecer un valor por defecto para una variable de entorno.

¡Hora de probar! Pero primero, vuelve a tu terminal. Como hemos hecho algunos cambios en nuestra configuración, tenemos que reiniciar el comando `messenger:consume` para, más o menos, recargar nuestra aplicación:

```terminal-silent
symfony console messenger:consume async -vv
```

¡Genial! El comando se ejecuta de nuevo y utiliza nuestra nueva configuración de Symfony. Vuelve a nuestra aplicación... ¡y reserva un viaje! Vuelve rápidamente al terminal... y veremos que el mensaje se ha procesado.

Ve a Mailtrap y... ¡aquí está! Momento de la verdad: haz clic en un enlace... Genial, ¡vuelve a funcionar! ¡Bob estará tan contento!

Si eres como yo, probablemente te parezca un rollo tener que mantener este comando `messenger:consume` ejecutándose en un terminal durante el desarrollo. Además, tener que reiniciarlo cada vez que haces un cambio de código o de configuración es molesto. ¡Estoy harto! ¡Es hora de devolver la diversión a nuestras funciones con otro truco de la CLI de Symfony!

En tu IDE, abre este archivo `.symfony.local.yaml`. Es la configuración del servidor Symfony CLI para nuestra aplicación. ¿Ves esta clave `workers`? Nos permite definir los procesos que se ejecutarán en segundo plano cuando iniciemos el servidor. Ya tenemos configurado el comando tailwind.

Añade otro trabajador. Llámalo `messenger` -aunque podría ser cualquier cosa- y establece`cmd` en `['symfony', 'console', 'messenger:consume', 'async']`. Esto resuelve el problema de tener que mantenerlo en ejecución en una ventana de terminal independiente. Pero, ¿qué pasa con el reinicio del comando cuando hacemos cambios? No hay problema! Añade una clave `watch` y establécela en `config`, `src`, `templates` y `vendor`. Si cambia algún archivo de estos directorios, el trabajador se reiniciará solo ¡Inteligente!

De vuelta a tu terminal, reinicia el servidor con `symfony server:stop` y `symfony serve -d``messenger:consume` ¡debería estar ejecutándose en segundo plano! Para comprobarlo, ejecuta

```terminal
symfony server:status
```

¡3 trabajadores funcionando! El servidor web PHP real, el trabajador`tailwind:build` existente y nuestro nuevo `messenger:consume`. ¡Genial!

A continuación, ¡exploremos cómo hacer afirmaciones sobre correos electrónicos en nuestras pruebas funcionales!
