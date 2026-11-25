<?php
defined('ABSPATH') || exit;

function tbs_meandme_journey() {
    $steps = array(
        array('01', 'Contexto', 'Identificar la necesidad.'),
        array('02', 'Comprensión', 'Aportar información relevante.'),
        array('03', 'Producto', 'Presentar opciones relacionadas.'),
        array('04', 'Compra', 'Facilitar una decisión informada.'),
    );
    $content = '';
    foreach ($steps as $step) {
        $content .= tbs_case_group(
            tbs_case_p($step[0], 'tbs-case-eyebrow')
            . tbs_case_h($step[1], 3)
            . tbs_case_p($step[2]),
            'tbs-case-journey-step'
        );
    }
    return tbs_case_group($content, 'tbs-case-journey');
}

function tbs_meandme_content() {
    $hero = tbs_case_p('CASE STUDY / 02', 'tbs-case-eyebrow')
        . tbs_case_p('ME AND ME', 'tbs-case-product')
        . tbs_case_h('De un catálogo de cosmética a una experiencia dermohormonal.', 1)
        . tbs_case_p('Rediseño UX/UI de una experiencia de e-commerce que conecta ciencia, contenido y necesidades personales para facilitar el descubrimiento de productos de cuidado de la piel.', 'tbs-case-lead');
    $hero .= tbs_case_facts(array(
        'Cliente' => 'ME AND ME',
        'Sector' => 'Dermocosmética',
        'Disciplina' => 'Product Design · UX/UI',
        'Proyecto' => 'Rediseño de e-commerce · 2026',
        'Alcance' => 'Arquitectura de información, diseño de interfaz y desarrollo web',
        'Colaboración' => 'Lachary Agencia Creativa',
    ));
    $hero .= tbs_case_media('meandme-hero', 'ME AND ME / VISIÓN PRINCIPAL · 16:9');
    $c = tbs_case_group($hero, 'tbs-case-hero');

    $c .= tbs_case_section('01', 'intro', 'Un producto que necesita algo más que una tienda.',
        tbs_case_p('ME AND ME propone una manera de entender el cuidado de la piel vinculada a las distintas etapas hormonales de la mujer.')
        . tbs_case_p('Su universo de contenidos combina información sobre salud dermohormonal, recomendaciones de cuidado, explicaciones sobre formulaciones y un catálogo de productos.')
        . tbs_case_p('Trasladar esta propuesta al entorno digital exigía algo más que organizar productos en categorías: era necesario construir una experiencia capaz de conectar conocimiento, necesidades y compra.')
        . tbs_case_p('Mi trabajo consistió en estructurar esa información y trasladarla a una experiencia web coherente, combinando diseño editorial, navegación y funcionalidades de e-commerce.'));

    $principles = '';
    foreach (array(
        array('01', 'Descubrimiento', 'Permitir explorar la propuesta de la marca desde las necesidades y etapas hormonales.'),
        array('02', 'Comprensión', 'Organizar los contenidos científicos y divulgativos mediante una lectura clara, progresiva y accesible.'),
        array('03', 'Compra', 'Conectar el contexto informativo con productos, características y acciones de compra.'),
    ) as $item) {
        $principles .= tbs_case_group(tbs_case_p($item[0], 'tbs-case-eyebrow') . tbs_case_h($item[1], 3) . tbs_case_p($item[2]), 'tbs-case-principle');
    }
    $c .= tbs_case_section('02', 'reto', 'Antes de elegir un producto, hay que entender qué se necesita.',
        tbs_case_p('Un catálogo organizado únicamente por tipos de producto no representa toda la complejidad de una marca cuyo posicionamiento parte de las necesidades de la piel en diferentes etapas hormonales.')
        . tbs_case_p('El desafío era conectar dos maneras de acercarse al cuidado personal: quienes ya saben qué producto buscan y quienes necesitan comprender qué opciones pueden ser relevantes para su situación.')
        . tbs_case_p('Además, la web debía comunicar contenidos especializados sin dificultar el descubrimiento de productos ni la experiencia de compra.')
        . tbs_case_statement('El reto no era mostrar más información. Era construir un recorrido que le diera sentido.')
        . tbs_case_h('Tres objetivos de diseño', 3)
        . tbs_case_group($principles, 'tbs-case-principles'));

    $entries = tbs_case_group(tbs_case_h('Explorar por etapa hormonal', 3)
        . tbs_case_p('Un recorrido que parte del contexto personal y proporciona información y opciones de cuidado relacionadas con cada etapa.'), 'tbs-case-principle')
        . tbs_case_group(tbs_case_h('Explorar por producto', 3)
        . tbs_case_p('Un acceso directo al catálogo, sus categorías y fichas de producto, pensado para quienes ya conocen qué necesitan.'), 'tbs-case-principle');
    $c .= tbs_case_section('03', 'arquitectura', 'Del producto al contexto.',
        tbs_case_p('Una de las decisiones centrales del rediseño fue reorganizar la arquitectura de información para incorporar las etapas hormonales como una vía principal de descubrimiento.')
        . tbs_case_p('La experiencia permite aproximarse a la oferta desde dos caminos complementarios: explorar el cuidado de la piel según la etapa hormonal o acceder directamente a productos y categorías.')
        . tbs_case_p('Esto permite mantener un recorrido comercial reconocible sin renunciar al carácter divulgativo de la marca.')
        . tbs_case_h('Dos entradas. Una experiencia conectada.', 3)
        . tbs_case_group($entries, 'tbs-case-principles tbs-case-two-entries')
        . tbs_case_h('Un recorrido con sentido.', 3)
        . tbs_meandme_journey()
        . tbs_case_media('meandme-navigation', 'ME AND ME / ARQUITECTURA DE NAVEGACIÓN'));

    $experience = tbs_case_p('La nueva experiencia combina diferentes niveles de información y distintas necesidades de navegación.')
        . tbs_case_p('Desde la presentación general de la marca hasta el detalle de una formulación, cada página cumple una función específica dentro del recorrido.');
    $experience .= tbs_case_feature('4.1 / HOME', 'Una puerta de entrada a todo el universo de la marca.',
        array('La home debía presentar de manera comprensible el posicionamiento dermohormonal de ME AND ME sin desconectarlo de su actividad comercial.',
            'La composición combina presentación de marca, información divulgativa, exploración por etapas hormonales, productos y contenidos de confianza.',
            'La jerarquía permite descubrir el concepto de la marca y elegir cómo continuar el recorrido.'),
        'La home se plantea como un punto de orientación entre conocimiento y comercio, evitando convertirse en un simple catálogo de productos destacados.',
        tbs_case_media('meandme-home', 'ME AND ME / HOME · CAPTURA EDITORIAL'));
    $experience .= tbs_case_feature('4.2 / ETAPAS HORMONALES', 'Diseñar desde las necesidades, no solo desde las categorías.',
        array('Las etapas hormonales se incorporaron como una dimensión principal de la estructura web.',
            'Este enfoque permite organizar contenidos especializados alrededor de situaciones y necesidades concretas, conectando información de contexto con opciones de cuidado y productos relacionados.',
            'Las páginas dedicadas a las diferentes etapas forman parte de un recorrido de descubrimiento que complementa la navegación tradicional del catálogo.'),
        'Dar a las etapas hormonales entidad propia dentro de la navegación y convertirlas en un punto de entrada hacia contenidos y productos.',
        tbs_case_media('meandme-stages', 'ME AND ME / ETAPAS HORMONALES'), true);
    $experience .= tbs_case_feature('4.3 / FICHAS DE PRODUCTO', 'Del argumento científico a una decisión informada.',
        array('Las fichas de producto debían responder a preguntas de distinta naturaleza: qué es el producto, para quién está indicado, cuáles son sus beneficios y cómo se utiliza.',
            'La propuesta de diseño organiza esa información en diferentes niveles de lectura.',
            'Los elementos esenciales para la compra conviven con contenido ampliado sobre formulación, contexto hormonal, instrucciones de uso y material complementario.'),
        'Jerarquizar la ficha para facilitar una primera lectura rápida, manteniendo accesible la información que necesita quien desea profundizar.',
        tbs_case_media('meandme-product', 'ME AND ME / FICHA DE PRODUCTO'));
    $experience .= tbs_case_feature('4.4 / COHERENCIA EDITORIAL Y VISUAL', 'Muchas páginas. Un mismo lenguaje.',
        array('El rediseño necesitaba mantener consistencia entre contenidos de marca, artículos divulgativos, páginas por etapa hormonal y funcionalidades propias del comercio electrónico.',
            'La solución se articuló mediante un lenguaje de interfaz común y estructuras modulares que facilitan organizar y evolucionar el contenido.',
            'La tipografía, los espaciados, las jerarquías y los patrones de navegación permiten que distintos tipos de página formen parte de una misma experiencia.'),
        'Un lenguaje de interfaz común conecta contenido editorial y funciones comerciales.',
        tbs_case_media('meandme-responsive', 'ME AND ME / SISTEMA RESPONSIVE'), true);
    $c .= tbs_case_section('04', 'experiencia', 'Un sistema de contenidos que acompaña la decisión.', $experience);

    $c .= tbs_case_section('05', 'implementacion', 'Del diseño a una experiencia real.',
        tbs_case_p('El proyecto no terminaba en las pantallas de diseño.')
        . tbs_case_p('La nueva web debía integrarse en un e-commerce existente, manteniendo la continuidad de los procesos comerciales y la información necesaria para su funcionamiento.')
        . tbs_case_p('Mi participación incluyó la implementación del nuevo diseño mediante WordPress y WooCommerce, junto con trabajos de integración, migración y adaptación de funcionalidades existentes.')
        . tbs_case_p('El proyecto también exigió atender a las dependencias técnicas que sostienen la operación comercial: contenidos, productos, clientes, pedidos, procesos de compra e integraciones.')
        . tbs_case_statement('Una buena experiencia de producto también depende de lo que sucede fuera de la pantalla.')
        . tbs_case_p('WordPress · Elementor · WooCommerce · Integraciones e-commerce', 'tbs-case-tech'));

    $c .= tbs_case_section('06', 'aprendizajes', 'Diseñar también es conectar información, necesidades y negocio.',
        tbs_case_p('ME AND ME fue un proyecto en el que la arquitectura de información tuvo un papel esencial para trasladar el posicionamiento de una marca a una experiencia digital.')
        . tbs_case_p('La decisión más relevante fue ampliar el modelo de navegación más allá del catálogo tradicional, incorporando el contexto hormonal como una dimensión de descubrimiento.')
        . tbs_case_p('El trabajo reúne contenidos especializados, recorridos editoriales y funciones comerciales bajo un lenguaje común de interfaz.')
        . tbs_case_p('El proyecto también pone de manifiesto el valor de combinar criterio UX/UI y capacidad de implementación: diseñar considerando tanto lo que necesita la persona que navega como lo que requiere el sistema para funcionar.')
        . tbs_case_h('Reflexión final', 3)
        . tbs_case_statement('Diseñar un e-commerce no es solo facilitar la compra. También es ayudar a entender qué estamos eligiendo.'));

    $c .= tbs_case_next_project('InprOTech Guardian', 'Hacer visible la complejidad de una red industrial.', '/work/guardian/');
    return $c;
}
