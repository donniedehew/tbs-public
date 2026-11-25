# Arquitectura de la selección pública

La estructura conserva la separación entre código, contenido editorial y medios. WordPress aporta el estado de las páginas y los proyectos; las plantillas determinan cómo se presentan.

## Componentes

Las rutas siguientes parten de `wp-content/plugins/`.

| Área | Archivos principales | Responsabilidad |
| --- | --- | --- |
| Home y enrutado | `tbs-pig-laboratory/tbs-pig-laboratory.php`, `view.php`, `editorial.php` | Selección de plantillas y composición de regiones editoriales. |
| Escena de Home | `tbs-pig-laboratory/dist/app.js`, `dist/assets/pig.glb` | Bundle de la escena y modelo 3D. |
| CPT y portfolio | `tbs-pig-laboratory/portfolio.php` | Registro de `work`, campos, consulta y orden de tarjetas. |
| Cabecera y navegación | `tbs-pig-laboratory/work-header.php`, `work-header.css`, `dist/items-menu.js` | Marca, menú, contador, botones y capas visuales compartidas. |
| Pie | `tbs-pig-laboratory/shared-footer.php` | Pie compartido por las plantillas. |
| Página de proyecto | `tbs-pig-laboratory/single-work.php`, `work.css`, `work.js`, `work-background.js` | Estructura general, presentación, visor y fondo. |
| Layouts editoriales | `guardian-case-study/work-integration.php` y sus plantillas | Estructuras de caso, capítulos y siguiente proyecto. |
| Adaptadores públicos | `tbs-benchmarks-case`, `tbs-layers-case`, `tbs-tiivii-case` | Particularidades de presentación y medios de los casos públicos. |
| Movimiento del pie | `tbs-footer-motion/tbs-footer-motion.php` | Comportamiento de movimiento del pie. |
| Datos estructurados | `tbs-structured-data/tbs-structured-data.php` | Generación de datos estructurados del sitio. |

El tema de bloques está en `wp-content/themes/thebutchery-studio/`; conserva sus partes, plantillas, estilos y scripts, incluidas las bibliotecas de terceros con sus avisos y licencias. Las miniaturas públicas y el avatar están junto a los recursos de presentación de Pig Laboratory.

Antes de cambiar un comportamiento, seguir sus llamadas y dependencias. Las partes genéricas del tema no sustituyen necesariamente la cabecera y el pie de un documento generado por un plugin.

## Contenido y CPT

Home se compone con regiones Gutenberg `tbs-*`. Las funciones de contenido inicial sirven para inicializar páginas; modificarlas no actualiza automáticamente los bloques de una página existente.

En un proyecto, los campos tienen responsabilidades distintas:

| Campo | Uso |
| --- | --- |
| `tbs_show` | Inclusión en el listado público; no es un control de acceso. |
| `tbs_order` | Orden de presentación. |
| `tbs_category`, `tbs_summary` | Información de la tarjeta. |
| `tbs_image`, `tbs_video` | Recursos usados por la tarjeta o el hero según la plantilla. |
| `tbs_case_layout` | Selección del layout editorial. |

La consulta pública combina el estado `publish` con `tbs_show=1`. El contenido, el estado editorial y la selección de layout son decisiones independientes. Esta copia no incluye un sistema de acceso a casos privados.

Los capítulos dependen del layout:

| Layout | Anclas |
| --- | --- |
| Guardian | `intro`, `reto`, `enfoque`, `solucion`, `sistema`, `conclusiones` |
| ME AND ME | `intro`, `reto`, `arquitectura`, `experiencia`, `implementacion`, `aprendizajes` |
| LiDAR | `intro`, `reto`, `estructura`, `visualizacion`, `rutas`, `conclusiones` |

Consultar el mapa y el contenido antes de modificar la navegación. No regenerar un proyecto existente para cambiar su aspecto.

## Navegación y presentación

El contador de proyectos se calcula a partir de la lista y se presenta con un mínimo de dos cifras tanto en PHP como en JavaScript. No fijar un número literal en CSS.

Las capas de blur se resuelven en el componente compartido. El degradado es homogéneo de lado a lado y pierde intensidad en vertical hacia el centro. La franja inferior se conserva desactivada en el código; la superior permanece activa. Una modificación debe comprobar también scroll, anclaje al pie, interacción, teclado y fallback visual.

Separar siempre la escena exterior, el marco y la ventana del medio. El fondo de los proyectos y la escena de Home tienen responsabilidades distintas. El visor compartido tampoco implica que exista una galería o un segundo nivel de zoom.

## Dependencias y límites

No se incluyen WordPress, base de datos, uploads, configuración del servidor ni los editables de diseño. Las referencias de ejemplos o semillas no reconstruyen una instalación. Se han retirado los módulos de acceso privado y las rutinas específicas de la instalación de producción que no corresponden a esta selección.

Se conservan el bundle de Home, el modelo 3D, las miniaturas públicas y el avatar. Se han omitido las tipografías cuya redistribución no está acreditada y se han retirado sus descargas y precargas. Esta copia utiliza fuentes de reserva del sistema, por lo que su tipografía puede diferir de la del sitio publicado.

La presencia de archivos en `dist/` no acredita un proceso reproducible de compilación. Antes de modificar un bundle, localizar sus fuentes y dependencias. No sustituir bibliotecas ni generar un bundle desde una receta supuesta.

La selección pública no es un paquete listo para instalar. Para preparar una instalación de desarrollo hay que resolver las dependencias de los módulos escogidos y proporcionar contenido y medios propios. No desplegar todo el árbol sobre una web existente.

## Comprobaciones

Con Node.js disponible, las pruebas de navegación se ejecutan desde la raíz:

```sh
node tests/navigation-glass.cjs
node tests/navigation-count.cjs
```

El test del contador requiere PHP en `PATH` o permite indicar su ejecutable con la variable de entorno `PHP_CLI`. Renderiza la cabecera PHP con una fixture y ejecuta el JavaScript real; no necesita una base de datos WordPress. Ejecutar además la comprobación de sintaxis de los archivos PHP y JavaScript modificados.

Para cambios visuales, comprobar escritorio y móvil, overflow, capas, enlaces, teclado, scroll en ambos sentidos y movimiento reducido. Para medios, añadir poster, reproducción, pausa y apertura/cierre del visor. Indicar los navegadores realmente probados; una revisión estática no acredita una prueba en todos ellos.

Los cambios de documentación requieren comprobar enlaces, rutas y coherencia con el código. Las pruebas existentes no sustituyen una instalación WordPress ni una revisión visual completa.
