(() => {
  'use strict';
  if(!window.wp?.blocks||!window.wp?.element||!window.wp?.blockEditor||!window.wp?.components)return;
  const {registerBlockType,getBlockType}=wp.blocks;
  const {createElement}=wp.element;
  const {useBlockProps}=wp.blockEditor;
  const {Placeholder}=wp.components;
  const sharedBlocks=[
    {
      name:'tbs/site-navigation',
      title:'TBS · Navegación del sitio',
      icon:'menu',
      description:'Logotipo y proyectos compartidos; Perfil y Contacto aparecen solo en la home.',
      instructions:'Edita Proyectos en «Enlace superior» de Index. El listado y el contador proceden de los Work publicados. Perfil y Contacto, editables en «Navegación fija», aparecen únicamente en la home.'
    },
    {
      name:'tbs/site-footer',
      title:'TBS · Pie del sitio',
      icon:'layout',
      description:'Pie compartido con los proyectos, el contacto y los enlaces legales de TBS.',
      instructions:'Este pie reutiliza los textos de contacto, copyright y enlaces legales de Index. Los proyectos proceden de los Work publicados. El contenido de esta página se edita en sus propios bloques.'
    }
  ];
  sharedBlocks.forEach(definition=>{
    if(getBlockType(definition.name))return;
    registerBlockType(definition.name,{
      apiVersion:3,
      title:definition.title,
      description:definition.description,
      icon:definition.icon,
      category:'theme',
      supports:{html:false,multiple:false},
      edit:()=>createElement('div',useBlockProps(),createElement(Placeholder,{
        icon:definition.icon,
        label:definition.title,
        instructions:definition.instructions
      })),
      save:()=>null
    });
  });
})();
