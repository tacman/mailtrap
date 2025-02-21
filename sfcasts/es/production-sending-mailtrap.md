# Envío en producción con Mailtrap

Muy bien, ¡por fin ha llegado el momento de enviar correos electrónicos reales en producción!

## Transportes de Mailer

Mailer viene con varias formas de enviar correos electrónicos, llamadas "transportes". Este `smtp` es el que estamos utilizando para nuestras pruebas con Mailtrap. Podríamos configurar nuestro propio servidor SMTP para enviar correos... pero... eso es complejo, y tienes que hacer un montón de cosas para asegurarte de que tus correos no se marcan como spam. Boo.

## transportes de terceros

Te recomiendo encarecidamente que utilices un servicio de correo electrónico de terceros. Éstos gestionan todas estas complejidades por ti y Mailer proporciona puentes a muchos de ellos para que la configuración sea pan comido.

## Puente Mailtrap

Utilizamos Mailtrap para las pruebas, pero Mailtrap también tiene funciones de envío a producción ¡Fantástico! Incluso tiene un puente oficial

En tu terminal, instálalo con:

```terminal
composer require symfony/mailtrap-mailer
```

Una vez instalado, comprueba tu IDE. En `.env`, la receta añade algunos stubs de `MAILER_DSN`. Podemos obtener los valores DSN reales de Mailtrap, pero antes tenemos que hacer algunos ajustes.

## Dominio de envío

En Mailtrap, tenemos que configurar un "dominio de envío". Esto configura un dominio de tu propiedad para permitir que Mailtrap envíe correos electrónicos correctamente en su nombre.

Nuestros abogados aún están negociando la compra de `universal-travel.com`, así que, por ahora, estoy utilizando un dominio personal que poseo: `zenstruck.com`. Añade tu dominio aquí.

Una vez añadido, estarás en esta página de "Verificación del dominio". Esto es súper importante, pero Mailtrap lo hace fácil. Sólo tienes que seguir las instrucciones hasta que aparezca esta marca de verificación verde. Básicamente, tendrás que añadir un montón de registros DNS específicos a tu dominio. DKIM, que verifica los correos electrónicos enviados desde tu dominio, y SPF, que autoriza a Mailtrap a enviar correos electrónicos en nombre de tu dominio, son los más importantes. Mailtrap proporciona una gran documentación sobre ellos si quieres profundizar en cómo funcionan exactamente. Pero básicamente, le estamos diciendo al mundo que Mailtrap está autorizado a enviar correos electrónicos en nuestro nombre.

## Producción `MAILER_DSN`

Una vez que tengas la marca de verificación verde, haz clic en "Integraciones" y luego en "Integrar" en la sección "Flujo de transacciones".

Ahora podemos decidir entre utilizar SMTP o API. Yo utilizaré la API, pero cualquiera de las dos funciona. Y ¡hey! Esto me resulta familiar: como con las pruebas de Mailtrap, elige PHP y luego Symfony. ¡Este es el `MAILER_DSN`que necesitamos! Cópialo y salta a tu editor.

Se trata de una variable de entorno sensible, así que añádela a `.env.local` para evitar confirmarla en git. Comenta el DSN de prueba de Mailtrap y pégalo a continuación. Eliminaré este comentario porque nos gusta mantener la vida ordenada.

¡Casi listo! Recuerda que sólo podemos enviar correos en producción desde el dominio que hemos configurado. En mi caso, `zenstruck.com`. Abre `config/services.yaml` y actualiza el`global_from_email` a tu dominio.

¡Veamos si funciona! En tu aplicación, reserva un viaje. Esta vez utiliza una dirección de correo electrónico real. Pondré el nombre `Kevin` y utilizaré mi correo electrónico personal:`kevin@symfonycasts.com`. Por mucho que te quiera a ti y a los viajes espaciales, pon aquí tu propio correo electrónico para evitar enviarme spam. ¡Elige una fecha y reserva!

Estamos en la página de confirmación de la reserva, ¡es una buena señal! Ahora, comprueba tu correo electrónico personal. Yo voy al mío y espero... actualizo... ¡aquí está! Si hago clic, ¡esto es exactamente lo que esperamos! La imagen, el archivo adjunto, ¡todo está aquí!

A continuación, vamos a ver cómo podemos rastrear los correos electrónicos enviados con Mailtrap, ¡además de añadir etiquetas y metadatos para mejorar ese rastreo!
