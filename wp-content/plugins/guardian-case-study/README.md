# TBS — Case Studies

Patrones editoriales y plantillas compartidas para los casos públicos Guardian, LiDAR y ME AND ME del CPT `work`. Otros adaptadores reutilizan esta estructura junto con `tbs-pig-laboratory/single-work.php`.

La cabecera, el pie y la lista de proyectos proceden de TBS Pig Laboratory. Cada diseño conserva su mapa de capítulos, los marcos de dispositivo, el ajuste de medios y el visor compartido. El contenido publicado se edita en WordPress.

Esta copia pública conserva los generadores de contenido como referencia editorial y los patrones del editor. Se han retirado la creación automática de borradores y las migraciones que sustituían contenidos existentes. No es un volcado del WordPress de producción.

Los identificadores de medios pertenecen a cada instalación. Los vídeos de portada se leen de `tbs_video` en el Work correspondiente. Los demás medios se añaden desde el editor; para generar ejemplos con imágenes locales existe el filtro `tbs_case_media_images`, que recibe un mapa por ancla con valores `array(attachment_id, alternative_text)`. El mapa está vacío por defecto y los ejemplos muestran un espacio pendiente de contenido.

Las tipografías comerciales no se distribuyen. Los estilos conservan sus alternativas del sistema. El aspecto tipográfico y la animación basada en ejes de una fuente variable pueden diferir de la web publicada.

Consulta el [README del repositorio](../../../README.md) antes de utilizar esta muestra.
