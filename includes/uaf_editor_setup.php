<?php
function uaf_mce_before_init( $init_array ) {
	
	$theme_advanced_fonts = '';
	$fontsRawData 	= get_option('uaf_font_data');
	$fontsData		= json_decode($fontsRawData, true);
	if (!empty($fontsData)):
		foreach ($fontsData as $key=>$fontData):
			$theme_advanced_fonts .= ucfirst(str_replace('_',' ', $fontData['font_name'])) .'='.$fontData['font_name'].';';		
		endforeach;
	endif;
	
	$theme_advanced_fonts .= 'Andale Mono=Andale Mono, Times;';
	$theme_advanced_fonts .= 'Arial=Arial, Helvetica, sans-serif;';
	$theme_advanced_fonts .= 'Arial Black=Arial Black, Avant Garde;';
	$theme_advanced_fonts .= 'Book Antiqua=Book Antiqua, Palatino;';
	$theme_advanced_fonts .= 'Comic Sans MS=Comic Sans MS, sans-serif;';
	$theme_advanced_fonts .= 'Courier New=Courier New, Courier;';
	$theme_advanced_fonts .= 'Georgia=Georgia, Palatino;';
	$theme_advanced_fonts .= 'Helvetica=Helvetica;';
	$theme_advanced_fonts .= 'Impact=Impact, Chicago;';
	$theme_advanced_fonts .= 'Symbol=Symbol;';
	$theme_advanced_fonts .= 'Tahoma=Tahoma, Arial, Helvetica, sans-serif;';
	$theme_advanced_fonts .= 'Terminal=Terminal, Monaco;';
	$theme_advanced_fonts .= 'Times New Roman=Times New Roman, Times;';
	$theme_advanced_fonts .= 'Trebuchet MS=Trebuchet MS, Geneva;';
	$theme_advanced_fonts .= 'Verdana=Verdana, Geneva;';
	$theme_advanced_fonts .= 'Webdings=Webdings;';
	$theme_advanced_fonts .= 'Wingdings=Wingdings, Zapf Dingbats;';
	
	$init_array['theme_advanced_fonts'] = $theme_advanced_fonts;
	return $init_array;
}

function wp_editor_fontsize_filter( $options ) {
	array_unshift( $options, 'fontsizeselect');
	array_unshift( $options, 'fontselect');
	return $options;
}