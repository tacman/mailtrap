# Bonificación: Programar nuestro comando de correo electrónico

¿Todavía estás aquí? ¡Estupendo! Tengo un capítulo extra para ti.

Uno de nuestros becarios, Hugo, se queja de que tiene que conectarse a nuestro servidor y ejecutar el comando de recordatorio de reservas, todas las noches a medianoche. No sé cuál es el problema, ¿para eso no están los becarios?

## Instalando el Programador de Symfony

Pero... Supongo que para ser más robustos, deberíamos automatizar esto por si está enfermo o se le olvida. Podríamos configurar una tarea CRON... pero eso no sería ni de lejos tan genial o flexible como usar el componente Programador de Symfony. Es perfecto para esto. En tu terminal, ejecuta:

```terminal
composer require scheduler
```

Piensa en Symfony Scheduler como un complemento para Messenger. Proporciona su propio transporte especial que, en lugar de una cola, determina si es el momento de ejecutar un trabajo. Cada trabajo, o tarea, es un mensaje de Messenger, por lo que requiere un gestor de mensajes. Consumes la programación, como cualquier transporte de Messenger, con el comando`messenger:consume`.

## `make:schedule`

Crea un horario con:

```terminal
symfony console make:schedule
```

¿Nombre del transporte? Utiliza `default`. ¿Nombre del programa? Utiliza el predeterminado: `MainSchedule`. ¡Excitante!

Es posible tener varios horarios, pero para la mayoría de las aplicaciones, un solo horario es suficiente.

## Configurar el horario

Compruébalo: `src/Scheduler/MainSchedule.php`. Es un servicio que implementa`ScheduleProviderInterface` y está marcado con el atributo `#[AsSchedule]` con el nombre `default`. El creador inyectó automáticamente la caché, y veremos por qué en un segundo. El método `getSchedule()` es donde configuramos la programación y añadimos tareas.

Este `->stateful()` al que pasamos `$this->cache` es importante. Si el proceso que está ejecutando este programa se cae -como si nuestros trabajadores de Messenger se detuvieran temporalmente durante un reinicio del servidor-, cuando vuelva a estar en línea, sabrá todas las tareas que se ha saltado y las ejecutará. Si se suponía que una tarea debía ejecutarse 10 veces mientras estaba inactiva, las ejecutará todas. Esto puede no ser lo deseado, así que añade`->processOnlyLastMissedRun(true)` para que sólo se ejecute la última:

[[[ code('42edee9352') ]]]

¡A prueba de balas!

Para aplicaciones más complejas, puedes estar consumiendo el mismo programa en varios trabajadores. Utiliza `->lock()` para configurar un bloqueo de modo que sólo un trabajador ejecute la tarea cuando le corresponda.

## Añadir una tarea

¡Es hora de añadir nuestra primera tarea! En `->add()`, escribe `RecurringMessage::`. Hay varias formas de activar una tarea. A mí me gusta utilizar `cron()`. Quiero que esta tarea se ejecute a medianoche, todos los días, así que utiliza `0 0 * * *`. El segundo argumento es el mensaje de Messenger a enviar. Queremos ejecutar `SendBookingRemindersCommand`, pero no podemos añadirlo aquí directamente. En su lugar, utiliza `new RunCommandMessage()` y pasa el nombre del comando: `app:send-booking-reminders` (aquí también puedes pasar argumentos y opciones):

[[[ code('3bc022f94d') ]]]

## Depurar el programa

En tu terminal, lista las tareas de nuestro programa ejecutando:

```terminal
symfony console debug:schedule
```

Tenemos un error.

> No puedes utilizar "CronExpressionTrigger" porque el paquete "cron expression" no está instalado

Solución fácil: copia el comando de instalación y ejecútalo:

```terminal
composer require dragonmantank/cron-expression
```

¡Buen nombre! Ahora vuelve a ejecutar el comando de depuración:

```terminal-silent
symfony console debug:schedule
```

Aquí vamos, la salida está un poco torcida en esta pequeña pantalla, pero puedes ver la expresión cron, el mensaje (y el comando), y el próximo tiempo de ejecución: esta noche a medianoche.

## `#[AsCronTask]`

Hay una alternativa para programar comandos. En `MainSchedule::getSchedule()`, borra el atributo `->add()`. Luego salta a nuestro `SendBookingRemindersCommand` y añade otro atributo: `#[AsCronTask()]` pasando a: `0 0 * * *`:

[[[ code('abd86914ce') ]]]

En tu terminal, depura de nuevo el horario para asegurarte de que sigue apareciendo:

```terminal-silent
symfony console debug:schedule
```

Y lo está, bastante bien.

Si tienes muchas tareas programadas a la misma hora, como a medianoche, puede que veas un pico de CPU a esta hora en tu servidor. A menos que sea superimportante que las tareas se ejecuten a una hora muy concreta, deberías repartirlas. Una forma de hacerlo, por supuesto, es asegurarte manualmente de que todas tienen expresiones cron diferentes, pero... eso es un rollo.

## Expresiones de cron con hash

Para nuestro comando `app:send-booking-reminders`, no me importa cuándo se ejecuta, sólo que se ejecute una vez al día. Podemos utilizar una expresión cron con hash. En nuestra expresión, sustituye los 0 por #. El # significa "elige un valor aleatorio válido para esta parte":

[[[ code('4bcf01205d') ]]]

Vuelve a depurar la programación:

```terminal-silent
symfony console debug:schedule
```

Está programado para ejecutarse a las 5:11 h. Ejecuta de nuevo el comando:

```terminal-silent
symfony console debug:schedule
```

Siguen siendo las 5:11 h. Vale, no es realmente aleatorio, los valores se calculan de forma determinista basándose en los detalles del mensaje. En nuestro caso, la cadena`app:send-booking-reminders`. Un comando diferente con la misma expresión hash tendrá valores diferentes.

La documentación del Programador tiene todos los detalles al respecto. Incluso hay alias para hashes comunes. Por ejemplo, `#mignight` elegirá una hora entre medianoche y las 3 de la madrugada. Utilízalo para nuestra expresión:

[[[ code('d0f2f8f5e7') ]]]

y vuelve a depurar la programación:

```terminal-silent
symfony console debug:schedule
```

Uy, una errata, lo arreglo y lo vuelvo a ejecutar:

```terminal-silent
symfony console debug:schedule
```

Ahora está programado para ejecutarse todos los días a las 2:11 h. ¡Genial!

## Ejecutar la programación

Ya hemos configurado nuestro programa, pero ¿cómo lo ejecutamos? Recuerda que las programaciones no son más que transportes de Messenger. El nombre del transporte es `scheduler_<schedule_name>`, en nuestro caso, `scheduler_default`. Ejecútalo con:

```terminal
symfony console messenger:consume scheduler_default
```

En tu servidor de producción, configúralo para que se ejecute en segundo plano como un trabajador normal de Messenger.

Muy bien, éste es un rápido resumen del componente Programador. Consulta la documentación para obtener más información

¡Feliz programación!
