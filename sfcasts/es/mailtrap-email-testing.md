# Previsualizar correos electrónicos con Mailtrap (Pruebas de correo electrónico)

Previsualizar correos electrónicos en el perfilador está bien para correos básicos, pero pronto añadiremos estilos HTML e imágenes de gatos espaciales. Para ver correctamente el aspecto de nuestros correos electrónicos, necesitamos una herramienta más robusta. Vamos a utilizar la herramienta de prueba de correo electrónico de [Mailtrap](https://mailtrap.io/). Esto nos proporciona un servidor SMTP real al que podemos conectarnos, pero en lugar de entregar los correos electrónicos a bandejas de entrada reales, ¡van a una bandeja de entrada falsa que podemos comprobar! Es como si enviáramos un correo electrónico de verdad y luego pirateáramos la cuenta de esa persona para verlo... ¡pero sin las molestias ni todas esas cosas ilegales!

## Bandeja de entrada falsa

Ve a https://mailtrap.io y regístrate para obtener una cuenta gratuita. Su plan gratuito tiene algunos límites, pero es perfecto para empezar. Una vez dentro, estarás en la página de inicio de su aplicación. Lo que nos interesa ahora es probar el correo electrónico, así que haz clic en él. Deberías ver algo así. Si aún no tienes una bandeja de entrada, añade una aquí.

Abre esa nueva y brillante bandeja de entrada. A continuación, tenemos que configurar nuestra aplicación para que envíe correos electrónicos a través del servidor SMTP Mailtrap. Esto es muy fácil Aquí abajo, en "Ejemplos de código", haz clic en "PHP" y luego en "Symfony". Copia el archivo `MAILER_DSN`.

## `MAILER_DSN` para Bandeja de entrada falsa

Como se trata de un valor sensible, y puede variar entre desarrolladores, no lo añadas a `.env`, ya que está compilado en git. En su lugar, crea un nuevo archivo `.env.local`en la raíz de tu proyecto. Pega aquí `MAILER_DSN` para anular el valor de `.env`.

¡Ya estamos preparados para probar Mailtrap! ¡Ha sido fácil! ¡A probar!

De vuelta en la aplicación, reserva un nuevo viaje: Nombre: `Steve`, Email: `steve@minecraft.com`, cualquier fecha en el futuro, y... ¡reserva! Esta petición tarda un poco más porque se está conectando al servidor SMTP externo Mailtrap.

## Correo electrónico en Mailtrap

De vuelta en Mailtrap, ¡bam! ¡El correo electrónico ya está en nuestra bandeja de entrada! Haz clic para comprobarlo. Aquí tienes una vista previa "Texto" y una vista "Sin procesar". También hay un "Análisis de Spam" - ¡genial! la "Información técnica" muestra todas las "cabeceras de correo electrónico" en un formato fácil de leer.

Estas pestañas "HTML" están en gris porque no tenemos una versión HTML de nuestro correo electrónico... todavía... ¡Cambiemos eso a continuación!
