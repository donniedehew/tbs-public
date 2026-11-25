<?php
defined('ABSPATH') || exit;
$template=$GLOBALS['tbs_benchmarks_shared_template']??'';
$id=(int)($GLOBALS['tbs_benchmarks_render_id']??0);
if(!$id || !$template || basename($template)!=='single-work.php' || !is_readable($template)) return;
$layout_filter=static function($value,$object_id,$key,$single)use($id){
 if((int)$object_id===$id && $key==='tbs_case_layout') return $single?'guardian':array('guardian');
 return $value;
};
add_filter('get_post_metadata',$layout_filter,10,4);
ob_start();
try { include $template; $html=ob_get_contents(); }
finally { ob_end_clean(); remove_filter('get_post_metadata',$layout_filter,10); }
$html=preg_replace_callback('~<body\b[^>]*>~i',static function($match){return str_replace('tbs-guardian-case-page','tbs-benchmarks-case-page',$match[0]);},$html,1);
$style='<link rel="stylesheet" id="tbs-benchmarks-case-css" href="'.esc_url(plugins_url('benchmarks.css',__FILE__)).'?ver=1.1.0">';
$script='<script id="tbs-benchmarks-videos-js" src="'.esc_url(plugins_url('videos.js',__FILE__)).'?ver=1.1.0" defer></script>';
$html=str_replace('</head>',$style.'</head>',$html);
echo str_replace('</body>',$script.'</body>',$html);

