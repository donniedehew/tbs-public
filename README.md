# The Butchery Studio — código público

Selección del código del portfolio [thebutchery.studio](https://thebutchery.studio): estructura editorial en WordPress, plantillas de proyectos, navegación compartida y presentación de medios.

El código muestra cómo se relacionan diseño, contenido y comportamiento: PHP para las plantillas y el CPT `work`, Gutenberg para el contenido editable, CSS para la composición y JavaScript para la interacción.

## Qué contiene

- Componentes compartidos de Home y proyectos, con la escena de Home compilada y su modelo 3D.
- Plantillas para Guardian, LiDAR y ME AND ME; adaptadores de Benchmarks, LAYERS y TiiVii.
- Tema de bloques, movimiento del pie y datos estructurados.
- Miniaturas públicas y avatar utilizados por la presentación.
- Pruebas de navegación y documentación para mantener el código.

Empezar por la [arquitectura](docs/arquitectura.md). Para colaborar o trabajar con un agente, consultar [AGENTS.md](AGENTS.md). El [flujo de medios](docs/medios.md) explica cómo preparar capturas y animaciones sin perder sus fuentes ni sus proporciones.

## Alcance

Esta copia pública tiene un historial independiente. No se sincroniza ni despliega automáticamente con el sitio.

Se han excluido contenidos y módulos privados, configuración del alojamiento, inventarios internos, credenciales, base de datos, uploads y tipografías cuya redistribución no está acreditada. Se han retirado sus descargas y precargas; esta copia utiliza fuentes de reserva del sistema. Tampoco se incluyen los proyectos editables de diseño y animación. Las exclusiones y dependencias de presentación se detallan en la [arquitectura](docs/arquitectura.md).

Es una referencia de código, no una copia completa de WordPress ni un paquete listo para instalar. Los bloques, metadatos, opciones y medios publicados pertenecen a una instalación independiente.

## Trabajo y validación

Preparar cambios en una rama, revisar el diff y usar una pull request. Ejecutar las comprobaciones pertinentes y revisar en navegador cualquier modificación visual; las pruebas de código no sustituyen esa revisión.

Desde la raíz, con Node.js disponible:

```sh
node tests/navigation-glass.cjs
node tests/navigation-count.cjs
```

La segunda prueba requiere PHP en `PATH` o la variable de entorno `PHP_CLI` configurada con su ejecutable. No requiere una base de datos de WordPress: usa una fixture para comprobar la cabecera real y su JavaScript.

La publicación de esta selección no añade una licencia general al conjunto. Deben respetarse los avisos y licencias que correspondan a cada componente; los recursos de terceros no adquieren permisos adicionales por aparecer referenciados en el código.
