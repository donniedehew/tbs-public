<?php
defined('ABSPATH') || exit;

function tbs_lidar_content() {
    $c = '';
    $hero = tbs_case_p('CASE STUDY / 03', 'tbs-case-eyebrow')
        . tbs_case_p('LiDAR App', 'tbs-case-product')
        . tbs_case_h('Convertir datos del terreno en decisiones sobre el mapa.', 1)
        . tbs_case_p('Diseño UX/UI de una herramienta de exploración geoespacial que conecta visualización 3D, clasificación del terreno y planificación de rutas en una misma experiencia.', 'tbs-case-lead');
    $hero .= tbs_case_facts(array(
        'Proyecto' => 'LiDAR App · 2022',
        'Área' => 'Investigación geoespacial',
        'Disciplina' => 'Product Design / UX/UI',
        'Plataforma' => 'Aplicación web',
        'Especialidad' => 'Visualización de datos e interacción espacial',
    ));
    $hero .= tbs_case_media('lidar-hero', 'LIDAR / HERO — 02 Escena Valores');
    $c .= tbs_case_group($hero, 'tbs-case-hero');

    $c .= tbs_case_section('01', 'intro', 'Explorar información que existe en tres dimensiones.',
        tbs_case_p('La tecnología LiDAR permite representar superficies y objetos a través de nubes de puntos tridimensionales.')
        . tbs_case_p('Trabajar con estos datos abre posibilidades para explorar terrenos, reconocer sus características y analizar recorridos sobre representaciones espaciales.')
        . tbs_case_p('El proyecto consistía en diseñar la experiencia de una herramienta que reuniera estas operaciones en una interfaz accesible desde el navegador.')
        . tbs_case_p('Mi trabajo se centró en organizar sus funcionalidades, definir la interacción con los escenarios y diseñar un sistema de interfaz capaz de acompañar tareas técnicas de diferente complejidad.'));

    $principles = '';
    foreach (array(
        array('01', 'Exploración', 'Dar protagonismo al terreno y facilitar el acceso a diferentes escenarios y formas de representación.'),
        array('02', 'Control', 'Agrupar parámetros y herramientas según la tarea que está realizando el usuario.'),
        array('03', 'Contexto', 'Mantener una relación comprensible entre el terreno, las rutas definidas y las visualizaciones complementarias.'),
    ) as $item) {
        $principles .= tbs_case_group(tbs_case_p($item[0], 'tbs-case-eyebrow') . tbs_case_h($item[1], 3) . tbs_case_p($item[2]), 'tbs-case-principle');
    }
    $c .= tbs_case_section('02', 'reto', 'La información espacial necesita herramientas para interpretarse.',
        tbs_case_p('Una nube de puntos permite representar un terreno con gran riqueza de información, pero visualizarlo no equivale necesariamente a comprenderlo.')
        . tbs_case_p('La persona que utiliza la herramienta necesita explorar el escenario, reconocer diferentes áreas, establecer parámetros y trabajar sobre puntos concretos del terreno.')
        . tbs_case_p('Además, estas operaciones deben mantenerse conectadas para evitar que cada funcionalidad se convierta en una tarea aislada.')
        . tbs_case_statement('El reto de diseño: combinar un espacio de visualización tridimensional con controles técnicos y herramientas de planificación sin perder claridad ni contexto.')
        . tbs_case_h('Tres principios de experiencia', 3)
        . tbs_case_group($principles, 'tbs-case-principles'));

    $tasks = '';
    foreach (array(
        array('Selección de escenarios', 'El punto de entrada permite localizar y abrir diferentes escenas representadas mediante nubes de puntos.'),
        array('Herramientas contextuales', 'Dentro de una escena, la interfaz agrupa las funcionalidades en secciones como visualización, áreas reconocidas y rutas de escape.'),
        array('Área de trabajo', 'El visualizador mantiene el terreno como referencia principal durante las operaciones.'),
    ) as $item) $tasks .= tbs_case_group(tbs_case_h($item[0], 3) . tbs_case_p($item[1]), 'tbs-case-principle');
    $c .= tbs_case_section('03', 'estructura', 'Separar los controles. Mantener conectado el trabajo.',
        tbs_case_p('Una de las características más relevantes de la interfaz es su distribución en dos áreas complementarias.')
        . tbs_case_p('El panel lateral reúne navegación, herramientas y parámetros, mientras que el espacio principal está reservado para la representación del terreno.')
        . tbs_case_p('Esta organización permite trabajar sobre una visualización de gran tamaño sin perder el acceso a las operaciones necesarias.')
        . tbs_case_h('Un sistema basado en tareas.', 3)
        . tbs_case_group($tasks, 'tbs-case-principles')
        . tbs_case_media('lidar-scenes', 'LIDAR / NAVEGACIÓN — 01 Inicio', 'standard'));

    $visual = tbs_case_p('La experiencia debía permitir interpretar el terreno desde distintas perspectivas sin cambiar de herramienta.')
        . tbs_case_p('La representación tridimensional facilita comprender la forma y continuidad de la superficie.')
        . tbs_case_p('El plano 2D ofrece una referencia cartográfica complementaria, mientras que el perfil de altura permite observar las variaciones de elevación asociadas a un recorrido.')
        . tbs_case_p('Estas formas de visualización tienen propósitos diferentes, pero pertenecen al mismo contexto de análisis.');
    $visual .= tbs_case_feature('4.1 / RECONOCIMIENTO DE ÁREAS', 'Hacer legible la información del terreno.',
        array('Las áreas reconocidas se representan mediante categorías diferenciadas: carreteras, terreno, vegetación, construcciones y elementos no clasificados.',
              'La interfaz incorpora una leyenda que permite identificar estas categorías mediante códigos visuales.'),
        'Ofrecer una referencia clara para interpretar diferentes tipos de superficie sin abandonar el escenario tridimensional.',
        tbs_case_media('lidar-areas', 'LIDAR / ÁREAS — 04 Dibujar2D', 'standard'));
    $visual .= tbs_case_feature('4.2 / VISUALIZACIÓN COMPLEMENTARIA', 'Del volumen al plano.',
        array('La combinación de representaciones permite consultar un mismo entorno desde diferentes escalas de lectura.',
              'El plano cartográfico aporta una referencia territorial a las operaciones realizadas en la nube de puntos.'),
        'Complementar la perspectiva tridimensional con una vista cartográfica contextual, conservando la relación entre ambas.',
        tbs_case_media('lidar-map', 'LIDAR / PLANO 2D — 05 Plano2D'), true);
    $c .= tbs_case_section('04', 'visualizacion', 'Una misma superficie. Diferentes lecturas.', $visual);

    $routes = tbs_case_p('Una de las funcionalidades centrales de la herramienta es la definición de recorridos sobre los escenarios.')
        . tbs_case_p('El diseño ofrece dos formas complementarias de introducir una ruta: establecer valores mediante campos de coordenadas o definir puntos directamente sobre la representación del terreno.')
        . tbs_case_p('Estas alternativas permiten adaptar la interacción a distintas formas de trabajo.');
    $routes .= tbs_case_feature('5.1 / DEFINIR MEDIANTE PARÁMETROS', 'Precisión cuando es necesaria.',
        array('El panel de ruta incorpora campos para puntos de inicio, intermedios y finales, además de parámetros como anchura y pendiente.',
              'La información se organiza siguiendo una lógica de configuración que agrupa los valores necesarios para definir el recorrido.'),
        'Presentar los parámetros dentro de una sección dedicada, con etiquetas, unidades y acciones reconocibles.',
        tbs_case_media('lidar-route-values', 'LIDAR / PARÁMETROS — 02 Escena Valores', 'standard'));
    $routes .= tbs_case_feature('5.2 / DIBUJAR DIRECTAMENTE', 'La interacción sucede sobre el propio terreno.',
        array('La herramienta también permite definir puntos de una ruta mediante interacción directa con la nube tridimensional o una representación en plano.',
              'Esta modalidad conecta los controles de la interfaz con el espacio sobre el que se está trabajando.'),
        'Ofrecer una alternativa visual a la entrada numérica para definir un recorrido conservando la referencia espacial.',
        tbs_case_media('lidar-route-drawing', 'LIDAR / DIBUJO — 03 Dibujar3D'), true);
    $routes .= tbs_case_feature('5.3 / TRABAJAR CON VARIAS RUTAS', 'Un sistema preparado para tareas sucesivas.',
        array('La interfaz permite incorporar diferentes rutas dentro de un mismo escenario y trabajar con sus respectivos controles.',
              'El panel lateral utiliza una organización vertical y secciones repetibles para alojar estas operaciones.'),
        'Mantener una estructura consistente al añadir elementos, evitando cambiar las reglas de interacción entre una ruta y la siguiente.',
        tbs_case_media('lidar-multiple-routes', 'LIDAR / MÚLTIPLES RUTAS — 07 Múltiple', 'standard'));
    $c .= tbs_case_section('05', 'rutas', 'De observar el terreno a intervenir sobre él.', $routes);

    $c .= tbs_case_section('06', 'analisis', 'La visualización no termina cuando se define una ruta.',
        tbs_case_p('Una representación tridimensional ayuda a identificar la disposición del terreno, pero determinadas características pueden interpretarse con más claridad mediante visualizaciones específicas.')
        . tbs_case_p('La herramienta incorpora una representación del perfil de altura que permite examinar las variaciones de elevación del recorrido.')
        . tbs_case_p('Este tipo de información amplía las posibilidades de lectura y análisis sin abandonar el escenario principal.')
        . tbs_case_h('Información complementaria, contexto compartido.', 3)
        . tbs_case_p('La representación del perfil aparece vinculada al entorno de trabajo, conservando la referencia visual de la ruta sobre el terreno.')
        . tbs_case_p('La experiencia conecta así dos formas de comprender una misma operación: su posición espacial y su comportamiento en altura.')
        . tbs_case_statement('Integrar una visualización analítica complementaria sin sustituir el área principal de trabajo.')
        . tbs_case_media('lidar-elevation-profile', 'LIDAR / PERFIL — 06 Perfil', 'standard'));

    $c .= tbs_case_section('07', 'sistema', 'Complejidad funcional. Coherencia visual.',
        tbs_case_p('El proyecto reúne diferentes patrones de interacción: listados, paneles desplegables, formularios, controles de visualización, mapas y gráficos.')
        . tbs_case_p('El desafío de interfaz consiste en lograr que estas herramientas se comporten como partes de un único entorno.')
        . tbs_case_p('La organización del panel lateral, los estados activos, las etiquetas y los controles permiten mantener un lenguaje reconocible mientras cambia la tarea que realiza el usuario.')
        . tbs_case_p('El diseño busca equilibrar precisión técnica, jerarquía visual y continuidad de la experiencia.')
        . tbs_case_statement('Una herramienta compleja necesita reglas sencillas de interacción.'));

    $c .= tbs_case_section('08', 'conclusiones', 'Diseñar es conectar información y acción.',
        tbs_case_p('LiDAR App aborda un problema de diseño especialmente interesante: convertir un escenario tridimensional en un entorno donde las personas puedan explorar, configurar operaciones e interpretar información especializada.')
        . tbs_case_p('Las decisiones de interfaz responden a diferentes necesidades: mantener el terreno como referencia principal, separar las herramientas por tareas y ofrecer distintas maneras de visualizar y definir recorridos.')
        . tbs_case_p('El proyecto refleja el valor del diseño UX/UI en productos técnicos, donde la calidad de la experiencia depende tanto de la organización de la información como de la relación entre controles, representaciones y acciones.')
        . tbs_case_h('Reflexión final', 3)
        . tbs_case_statement('La complejidad de los datos no debería convertirse en complejidad de uso.'));

    $c .= tbs_case_next_project('ME AND ME', 'Diseño editorial y comercio para el cuidado de la piel.', '/work/meandme/');
    return $c;
}
