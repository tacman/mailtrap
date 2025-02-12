# Envío en producción con Mailtrap

Muy bien, ¡por fin ha llegado el momento de enviar correos electrónicos a un correo real en producción!

## Transportes Mailer

Si recuerdas, Mailer utiliza transportes para enviar correos electrónicos. Si miras la documentación de Symfony Mailer, enumera algunos transportes incorporados. Este de `smtp`es el que estamos utilizando para nuestras pruebas con Mailtrap. Podríamos configurar nuestro propio servidor SMTP para enviar correos electrónicos... pero... hay que tener en cuenta un montón de cosas complejas. No estamos en los años 90, cuando podías enviar un correo electrónico a cualquiera, desde cualquiera, y lo recibiría. Hay un montón de cosas que hay que configurar para que tus correos electrónicos pasen los filtros de spam.

## transportes de terceros

Recomiendo encarecidamente utilizar un servicio de correo electrónico de terceros. Estos manejan todas estas complejidades por ti. Symfony Mailer proporciona puentes a muchos de ellos en forma de paquetes adicionales.

## Puente Mailtrap

Ya estamos utilizando Mailtrap para pruebas, pero Mailtrap también tiene capacidades de envío a producción. Y, ¡hay un puente oficial para ello!

En tu terminal, instálalo con:

```terminal
composer require symfony/mailtrap-mailer
```

Una vez instalado, comprueba tu IDE. En `.env`, la receta de este paquete añade algunos stubs de `MAILER_DSN`. Podemos obtener los valores DSN reales de Mailtrap, pero primero tenemos que hacer algunos ajustes.

## Dominio de envío

En Mailtrap, tenemos que configurar un "dominio de envío". Esto configura un dominio de tu propiedad para permitir que Mailtrap envíe correos electrónicos correctamente en su nombre. Haz clic en "Dominios de envío".

Nuestros abogados aún están negociando la compra de `universal-travel.com`, así que, por ahora, estoy utilizando un dominio personal que poseo: `zenstruck.com`. Si nos sigues, tendrás que añadir tu propio dominio aquí.

Una vez añadido, estarás en esta página de "Verificación del dominio". Esto es súper importante, pero Mailtrap lo hace fácil. Sólo tienes que seguir las instrucciones hasta que aparezca esta marca de verificación verde. Básicamente, tendrás que añadir un montón de registros DNS específicos a tu dominio. DKIM, que verifica los correos electrónicos enviados desde tu dominio, y SPF, que autoriza a Mailtrap a enviar correos electrónicos en nombre de tu dominio, son los más importantes. Mailtrap proporciona una gran documentación sobre ellos si quieres profundizar en cómo funcionan exactamente.

Si no es así, sólo tienes que marcar las casillas verdes aquí y ¡listo!

Una vez lo hayas hecho, haz clic en "Integración" y luego en "Integrar" en la sección "Flujo de transacciones".

Ahora puedes decidir entre utilizar SMTP o API. Yo voy a utilizar la API. Esto debería resultarte familiar. Al igual que con las pruebas de Mailtrap, elige PHP y luego Symfony. ¡Este es el `MAILER_DSN`que necesitamos! Cópialo y salta a tu IDE.

De nuevo, se trata de una variable de entorno sensible, por lo que la añadiremos a `.env.local` para que no se envíe a nuestro repositorio. Primero, comenta nuestro DSN de prueba de Mailtrap y pégalo debajo. Elimina este comentario de arriba.

¡Casi listo! Recuerda que sólo podemos enviar correos en producción desde el dominio que hemos configurado. En mi caso, `zenstruck.com`. Abre `config/services.yaml` y actualiza el`global_from_email` a tu dominio.

¡Veamos si funciona! En tu aplicación, reserva un viaje. Recuerda que aquí tienes que utilizar una dirección de correo electrónico real. Yo pondré el nombre `Kevin` y utilizaré mi correo electrónico personal:`kevin@symfonycasts.com`, ¡pero tú tendrás que poner aquí tu propio correo electrónico para no enviarme spam! ¡Elige una fecha y reserva!

Estamos en la página de confirmación de la reserva, ¡eso es buena señal! Ahora, comprueba tu correo electrónico personal. Yo voy al mío y espero... actualizo... ¡aquí está! Si hago clic, ¡esto es exactamente lo que esperamos! La imagen, el archivo adjunto, ¡todo está aquí! ¡Qué bien!

A continuación, vamos a ver cómo podemos rastrear los correos electrónicos enviados con Mailtrap, ¡además de añadir etiquetas y metadatos para mejorar el rastreo!
