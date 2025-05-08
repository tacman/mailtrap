# Bonificación: Messenger Monitor Bundle

Hola, ¿sigues aquí? ¡Estupendo! ¡Hagamos un último capítulo extra!

Cuando tienes un montón de mensajes y programaciones ejecutándose en segundo plano, puede ser difícil saber qué está pasando. ¿Se están ejecutando mis trabajadores? ¿Se está ejecutando mi programación? ¿Y hacia dónde se está ejecutando? ¿Y los fallos? Tenemos registros, pero... registros. En lugar de eso, vamos a explorar un bundle genial que nos proporciona una interfaz de usuario para saber qué está pasando con nuestro ejército de robots trabajadores

## Instalación

En tu terminal, ejecuta:

```terminal
composer require zenstruck/messenger-monitor-bundle
```

Te pide que instales una receta, di que sí. Vuelve a nuestro IDE y mira lo que se ha añadido.

En primer lugar, se ha añadido un `src/Schedule.php`. Esto no está relacionado con este bundle. Desde el último capítulo, en el que añadimos el `Symfony Scheduler`, ahora tiene una receta oficial que añade una programación por defecto. Como ya tenemos uno, elimina este archivo.

## `MessengerMonitorController`

Se ha añadido un nuevo controlador: `src/Controller/Admin/MessengerMonitorController.php`. Se trata de un stub para habilitar la interfaz de usuario del bundle. Extiende este `BaseMessengerMonitorController`del bundle y añade un prefijo de ruta de `/admin/messenger`. También añade este atributo `#[IsGranted('ROLE_ADMIN')]`. Esto es muy importante para tus aplicaciones reales. Sólo quieres que los administradores del sitio accedan a la IU, ya que muestra información sensible. No tenemos seguridad configurada en esta app, así que eliminaré esta línea:

[[[ code('56502c4e95') ]]]

## `ProcessedMessage`

`src/Entity/ProcessedMessage.php` es una nueva entidad añadida por la receta. También es un stub que extiende esta clase `BaseProcessedMessage` y añade una columna ID. Se utiliza para hacer un seguimiento del historial de tus mensajes de Messenger. Por cada mensaje procesado, se persiste una nueva de estas entidades. Pero no te preocupes, esto se hace en tu proceso worker, por lo que no ralentizará el frontend de tu aplicación.

Como tenemos una nueva entidad, deberíamos añadir una migración, pero no tengo migraciones configuradas para este proyecto. Así que en tu terminal, ejecuta:

```terminal
symfony console doctrine:schema:update --force
```

## Instalar dependencias opcionales

Antes de comprobar la interfaz de usuario, el bundle tiene dos dependencias opcionales que quiero instalar. La primera:

```terminal
composer require knplabs/knp-time-bundle
```

Esto hace que las marcas de tiempo de la interfaz de usuario sean legibles, como "hace 4 minutos". Siguiente:

```terminal
composer require lorisleiva/cron-translator
```

Como estamos utilizando expresiones cron para nuestras tareas programadas, este paquete las hace legibles. Así, en lugar de "11 2 * * * *", lo mostrará como "todos los días a las 2:11 AM". ¡Estupendo!

¡Ya estamos listos! Inicia el servidor con:

```terminal
symfony serve -d
```

## Panel de control

Salta al navegador y visita `/admin/messenger`. Éste es el panel de control de Messenger Monitor

Este primer widget muestra los trabajadores en ejecución y su estado. Podemos ver que tenemos 1 trabajador en ejecución para nuestro transporte `async`. Este es el que hemos configurado para que se ejecute con nuestro servidor Symfony CLI.

A continuación, vemos nuestros transportes disponibles, cuántos mensajes están en cola y cuántos trabajadores los están ejecutando. Observa que nuestro transporte `scheduler_default`no se está ejecutando. Esto es de esperar, ya que no lo hemos configurado para que se ejecute localmente.

Debajo, tenemos una instantánea de las estadísticas de las últimas 24 horas.

A la derecha, veremos los últimos 15 mensajes procesados. Por supuesto, ahora está vacío.

Todos estos widgets se actualizan automáticamente cada 5 segundos.

## Programar

¡Vamos a crear un historial! En la barra superior, haz clic en `Schedule` (observa que el icono está en rojo para indicar que la programación no se está ejecutando). Es una especie de "comando `debug:schedule` más avanzado". Vemos nuestra única tarea programada: `RunCommandMessage` para `app:send-booking-reminders`. Utiliza un`CronExpressionTrigger` para ejecutarse "todos los días a las 2:11 AM". hasta ahora se ha ejecutado 0, pero podemos ejecutarla manualmente haciendo clic en "Activar"... y seleccionando nuestro transporte `async`.

## "Detalles"

Vuelve al panel de control. Se ejecutó correctamente, tardó 58 ms y consumió 31 MB de memoria. Haz clic en "Detalles" para ver aún más información "Tiempo en cola", "Tiempo para gestionar", marcas de tiempo... un montón de cosas buenas.

Estas etiquetas son muy útiles para filtrar mensajes. Puedes añadir tus propias etiquetas, pero algunas las añade el bundle: `manual` `schedule:default:<hash>` , porque ejecutamos "manualmente" una tarea programada, `schedule`, porque era una tarea programada, `schedule:default`, porque forma parte de nuestra programación por defecto. es el identificador único de esta tarea programada.

A la derecha está el "resultado" del "manejador" del mensaje - en este caso,`RunCommandMessageHandler`. Diferentes gestores tienen diferentes resultados (algunos no tienen ninguno). Para éste, el resultado es el código de salida del comando y la salida.

> Enviados 0 recordatorios de reserva

Vamos a ejecutar de nuevo esta tarea, pero esta vez, con una reserva que necesita que se le envíe un recordatorio. De vuelta a tu terminal, vuelve a cargar nuestras instalaciones:

```terminal
symfony console doctrine:fixtures:load
```

Vuelve al navegador. El panel de control está vacío ahora, pero eso era de esperar: al recargar nuestros dispositivos también se ha borrado nuestro historial de mensajes. Haz clic en "Programar" y luego en "Activar" en nuestro transporte "asíncrono".

De vuelta en el panel de control, ahora tenemos 2 mensajes. `RunCommandMessage` de nuevo pero haz clic en sus "Detalles":

> Enviado 1 recordatorio de reserva

Ahora nuestro segundo mensaje: `SendEmailMessage`. Este fue enviado por el comando. Haz clic en sus "Detalles" para ver la información relacionada con el correo electrónico de sus resultados. Observa la etiqueta, `booking_reminder`. El bundle detectó automáticamente que estábamos enviando un correo electrónico con una etiqueta "Mailer", por lo que la añadió aquí.

## Transporta

En el menú superior, puedes hacer clic en "Transportes" para ver más detalles sobre los mensajes pendientes de cada uno (si procede). El transporte `failed` muestra los mensajes fallidos y te da la opción de reintentarlos o eliminarlos, ¡directamente desde la interfaz de usuario!

## Historial

"Historial" es donde podemos filtrar los mensajes: Periodo, limitar a un intervalo de fechas concreto. Transporte, limitar a un transporte específico. Estado, mostrar sólo éxitos o fracasos. Programación, incluir o excluir los mensajes activados por una programación. Tipo de mensaje, filtrar por clase de mensaje.

## Estadísticas

"Estadísticas" muestra un resumen de estadísticas por clase de mensaje y puede limitarse a un intervalo de fechas específico.

## Purgar el historial de mensajes

Como probablemente puedas imaginar, si tu aplicación ejecuta muchos mensajes, nuestra tabla de historial puede llegar a ser realmente grande. El bundle proporciona algunos comandos para purgar mensajes antiguos.

En la documentación del bundle, desplázate hasta "messenger:monitor:purge" y copia el comando. Necesitamos programar esto... ¿pero cómo? Con el Programador de Symfony, ¡por supuesto! Abre `src/Scheduler/MainSchedule.php` y añade una nueva tarea con `->add(RecurringMessage::cron())`. Utiliza `#midnight`para que se ejecute diariamente entre medianoche y las 3 de la madrugada. Añade `new RunCommandMessage()`y pega el comando. Añade la opción `--exclude-schedules`:

[[[ code('066bc292db') ]]]

Esto purgará los mensajes con más de 30 días de antigüedad, excepto los mensajes activados por una programación. Esto es importante porque tus tareas programadas pueden ejecutarse una vez al mes o incluso una vez al año. Esto te permite mantener un historial de ellas independientemente de su frecuencia.

## Purgar el Historial de Programaciones

Sin embargo, debemos limpiarlos. Así que, volviendo a los documentos, copia un segundo comando: `messenger:monitor:schedule:purge`. Y en la programación, añádelo con `->add(RecurringMessage::cron('#midnight', new RunCommandMessage()))`y pégalo:

[[[ code('65f53c81b7') ]]]

Esto purgará el historial de mensajes programados omitidos por el comando anterior, pero conservará las 10 últimas ejecuciones de cada uno.

Asegurémonos de que estas tareas se han añadido a nuestra programación. De vuelta en el navegador, haz clic en "Programar" y aquí están: nuestras dos nuevas tareas.

Para la tarea que ejecutamos manualmente antes, podemos ver el resumen de la última ejecución, los detalles e incluso su historial.

Muy bien, amigos Esto es un rápido repaso a `zenstruck/messenger-monitor-bundle`. Echa un vistazo a los [docs](https://github.com/zenstruck/messenger-monitor-bundle) para obtener más información sobre todas sus funciones.

hasta la próxima, ¡feliz monitorización!
