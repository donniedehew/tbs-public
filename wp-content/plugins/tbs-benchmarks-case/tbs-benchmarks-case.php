<?php
/**
 * Plugin Name: TBS · Caso CiTIUS AI Benchmarks
 * Description: Adapta AI Benchmarks a la plantilla editorial compartida del CPT Work.
 * Version: 1.1.0
 * Author: The Butchery Studio
 * Requires PHP: 7.4
 */
defined('ABSPATH') || exit;
function tbs_benchmarks_is_target($post) {
 if (!($post instanceof WP_Post) || $post->post_type !== 'work' || $post->post_name !== 'benchmarks') return false;
 $layout=get_post_meta($post->ID,'tbs_case_layout',true);
 return $layout==='' || $layout==='benchmarks';
}
function tbs_benchmarks_sync_metadata($id,$post,$update) {
 if (!tbs_benchmarks_is_target($post) || wp_is_post_revision($id) || wp_is_post_autosave($id) || $post->post_status!=='publish') return;
 $defaults=array(
  'tbs_case_layout'=>'benchmarks','tbs_show'=>1,'tbs_order'=>5,
  'tbs_category'=>'Product Design · UI · Navegación',
  'tbs_summary'=>'Diseño de interfaz para preparar datasets, consultar experimentos y comparar modelos predictivos en una plataforma de benchmarking.',
  'rank_math_title'=>'CiTIUS / AI Benchmarks · The Butchery Studio',
  'rank_math_description'=>'Diseño de interfaz para preparar datasets, consultar experimentos y comparar modelos predictivos en una plataforma de benchmarking.'
 );
 foreach($defaults as $key=>$value){ $empty_text=in_array($key,array('tbs_case_layout','rank_math_title','rank_math_description'),true) && trim((string)get_post_meta($id,$key,true))===''; if(!metadata_exists('post',$id,$key)||$empty_text) update_post_meta($id,$key,$value); }
 $image=(int)get_post_thumbnail_id($id);
 if($image && wp_attachment_is_image($image) && !(int)get_post_meta($id,'tbs_image',true)) update_post_meta($id,'tbs_image',$image);
 foreach(array('show','order','category','summary','image') as $field) {
  if(metadata_exists('post',$id,'tbs_'.$field) && !metadata_exists('post',$id,'_tbs_'.$field)) update_post_meta($id,'_tbs_'.$field,'field_tbs_'.$field);
 }
}
add_action('wp_after_insert_post','tbs_benchmarks_sync_metadata',20,3);
function tbs_benchmarks_template($template) {
 $post=get_queried_object();
 if(!is_singular('work') || post_password_required() || !tbs_benchmarks_is_target($post)) return $template;
 if(basename($template)!=='single-work.php' || !is_readable($template) || !function_exists('tbs_case_embedded_body') || !function_exists('tbs_case_navigation')) return $template;
 $GLOBALS['tbs_benchmarks_shared_template']=$template;
 $GLOBALS['tbs_benchmarks_render_id']=(int)$post->ID;
 return __DIR__.'/shared.php';
}
add_filter('template_include','tbs_benchmarks_template',PHP_INT_MAX);

function tbs_benchmarks_video_markup($html,$block) {
 if(is_admin() || (defined('REST_REQUEST') && REST_REQUEST) || !is_singular('work')) return $html;
 $post=get_queried_object();
 if(!($post instanceof WP_Post) || $post->post_type!=='work' || $post->post_name!=='benchmarks') return $html;
 // The shared adapter temporarily renders this Work through the Guardian layout.
 $render_id=(int)($GLOBALS['tbs_benchmarks_render_id']??0);
 if($render_id!==(int)$post->ID && !tbs_benchmarks_is_target($post)) return $html;
 $classes=' '.($block['attrs']['className']??'').' ';
 if(strpos($classes,' benchmarks-case-video ')===false || !class_exists('WP_HTML_Tag_Processor')) return $html;
 $p=new WP_HTML_Tag_Processor($html);
 if(!$p->next_tag('VIDEO')) return $html;
 $src=$p->get_attribute('src');
 if(!$src) return $html;
 $labels=array('benchmarks-hero'=>'Recorrido de AI Benchmarks','benchmarks-datasets'=>'Preparación y consulta de datasets','benchmarks-experimentos'=>'Resultados y estadísticas de un experimento','benchmarks-comparacion'=>'Comparación de experimentos','benchmarks-sistema'=>'Componentes, visibilidad y cuenta');
 $label=$labels[$block['attrs']['anchor']??'']??'AI Benchmarks';
 $p->set_attribute('data-bm-video','1');
 $p->set_attribute('data-bm-src',$src);
 $p->set_attribute('aria-label',$label);
 $p->set_attribute('preload','none');
 $p->remove_attribute('src');
 $p->remove_attribute('autoplay');
 $p->remove_attribute('controls');
 return $p->get_updated_html().'<noscript><p class="benchmarks-case-caption"><a href="'.esc_url($src).'">Ver vídeo: '.esc_html($label).'</a></p></noscript>';
}
add_filter('render_block_core/video','tbs_benchmarks_video_markup',10,2);

