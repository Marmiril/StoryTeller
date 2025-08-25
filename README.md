PROYECTO FINAL - CUENTACUENTOS / STORYTELLER PROJECT
====================================================

Trabajo de Fin de Grado - Desarrollo de Aplicaciones Web (DAW)
--------------------------------------------------------------
Centro: Ilerna Online
Alumno: Ángel Plata Benítez
Fecha: 25/04/2025

OBJETIVO DEL PROYECTO
---------------------
Crear una aplicación web colaborativa donde los usuarios puedan escribir cuentos de manera secuencial, sin conocer el contenido de los pasos anteriores.

FUNCIONALIDADES
---------------
- Registro e inicio de sesión de usuarios.
- Creación de cuentos especificando:
  * Título
  * Temática
  * Número de pasos (colaboraciones)
  * Palabra guía (opcional)
- Participación de un único fragmento por usuario por cuento.
- Visualización de cuentos terminados.
- Visualización pública de cuentos para todos los usuarios (logados o no).
- Panel de usuario con:
  * Perfil personal
  * Preferencias (editable)
  * Historial de colaboraciones
- Sistema de notificaciones:
  * Aviso si un fragmento ha sido eliminado por el administrador.
  * Aviso si un cuento completo ha sido eliminado.
- Moderación por parte del administrador:
  * Eliminar colaboraciones específicas.
  * Eliminar cuentos completos (incluso finalizados).
- Estadísticas al finalizar un cuento (media de edad, color favorito predominante).

TECNOLOGÍAS UTILIZADAS
-----------------------
Frontend:
- HTML5
- CSS3
- JavaScript (validaciones y gestión de visibilidad)

Backend:
- PHP (puro, sin frameworks)
- MySQL (gestionado con phpMyAdmin en XAMPP)

ESTRUCTURA DE ARCHIVOS
-----------------------
- index.php                  → Página principal del proyecto
- login.php / logout.php     → Gestión de inicio y cierre de sesión
- register.php               → Registro de nuevos usuarios
- create_story.php           → Crear un nuevo cuento
- collaborate-story.php      → Enviar un fragmento
- collection.php             → Visualizar un cuento completo
- profile.php                → Perfil del usuario
- consult_tale.php           → Últimos fragmentos enviados
- moderate_admin.php         → Panel de administración

Carpetas:
- /views                     → Archivos relacionados con visualización de cuentos
- /css                       → Estilos CSS
- /js                        → Scripts JS
- /img                       → Imágenes del sitio
- /php                       → Scripts auxiliares PHP
- /includes                  → Conexión a la base de datos y funciones comunes

NOTAS FINALES
-------------
- No se han utilizado frameworks ni bibliotecas externas.
- Código separado claramente por lenguajes y funciones.
- Validado para entorno local XAMPP.
