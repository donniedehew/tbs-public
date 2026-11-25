# Guía para agentes

Estas instrucciones se aplican a esta selección pública del código de The Butchery Studio. Las peticiones y autorizaciones de la conversación determinan el alcance de cada tarea.

## Antes de cambiar código

1. Leer [README.md](README.md), la [arquitectura](docs/arquitectura.md) y los archivos del módulo afectado.
2. Comprobar rama, estado y diff. Esta copia tiene historial propio y no acredita el estado actual de la web.
3. Identificar si el cambio pertenece a un componente compartido, un adaptador, contenido de WordPress o un medio. No confundir el contenido inicial de una plantilla con el contenido publicado.
4. Conservar una referencia recuperable y limitar el cambio al alcance solicitado.

## Criterios de implementación

- Resolver cabecera, pie, navegación y comportamiento transversal en sus componentes compartidos. No añadir parches de CSS personalizado ni duplicar una solución por página.
- Respetar el layout y las anclas de cada proyecto. Los capítulos no son idénticos en todas las plantillas.
- Mantener el contenido editable. No reejecutar semillas, activaciones o migraciones para corregir textos o estilos de una instalación existente.
- Separar escena, marco y medio. No deformar vídeos ni capturas para encajarlos; conservar las proporciones y el encuadre previsto.
- Revisar las fuentes y el proceso real antes de regenerar archivos compilados. No inventar un comando de build.
- Mantener las exclusiones de esta copia pública. No incorporar contenido privado, mecanismos de acceso de producción, secretos, datos de alojamiento ni recursos sin permiso de redistribución.
- Conservar avisos y licencias aplicables. No deducir una licencia general del nombre del repositorio o de la licencia de un componente.

## Método

1. Preparar una rama de trabajo y un diff revisable; no forzar el historial ni actualizar `main` directamente.
2. Implementar en la capa correcta, reutilizando las dependencias existentes.
3. Validar según el cambio: sintaxis y pruebas para lógica; navegador para presentación, navegación y medios; enlaces y consistencia para documentación.
4. Revisar lo que se hará público, incluidos nuevos archivos, URLs, comentarios y recursos. Integrar mediante pull request.
5. Verificar el resultado remoto. Si una escritura falla o agota el tiempo, consultar el estado antes de repetirla.
6. Documentar resultado, pruebas realizadas y límites. Distinguir cambios locales, registrados en GitHub y desplegados; un merge aquí no modifica WordPress.

Para capturas, Adobe XD y After Effects, seguir el [flujo de medios](docs/medios.md). No afirmar que se ha editado, exportado o comprobado un recurso sin haber realizado esa operación.

Actualizar esta guía cuando cambie una regla compartida comprobada. Mantener las instrucciones de esta copia públicas y autosuficientes, sin enlazar documentación operativa privada.
