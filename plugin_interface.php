<?php
add_action('admin_menu', 'uaf_create_menu');
add_action("admin_print_scripts", 'adminjslibs');
add_action("admin_print_styles", 'adminCsslibs');
add_action('wp_enqueue_scripts', 'uaf_client_css');

function uaf_client_css() {
	wp_register_style( 'uaf_client_css', plugins_url('use-any-font/css/uaf.css'));
	wp_enqueue_style( 'uaf_client_css' );	
}

function adminjslibs(){
	wp_register_script('uaf_validate_js',plugins_url("use-any-font/js/jquery.validate.min.js"));		
	wp_enqueue_script('uaf_validate_js');	
}

function adminCsslibs(){
	wp_register_style('uaf-admin-style', plugins_url('use-any-font/css/uaf_admin.css'));
    wp_enqueue_style('uaf-admin-style');
}
		
function uaf_create_menu() {
	add_options_page('Use Any Font', 'Use Any Font', 'administrator', __FILE__, 'uaf_settings_page');	
}

function uaf_activate(){
	uaf_write_css(); //rewrite css when plugin is activated after update or somethingelse......
}

function uaf_settings_page() {
	include('includes/uaf_header.php');
	include('includes/uaf_font_upload.php');
	include('includes/uaf_font_implement.php');
	include('includes/uaf_footer.php');
}

function uaf_write_css(){
	ob_start();
	$fontsRawData 	= get_option('uaf_font_data');
		$fontsData		= json_decode($fontsRawData, true);
		if (!empty($fontsData)):
			foreach ($fontsData as $key=>$fontData): ?>
			@font-face {
				font-family: '<?php echo $fontData['font_name'] ?>';
				font-style: normal;
				src: url(<?php echo $fontData['font_path'] ?>.eot);
				src: local('<?php echo $fontData['font_name'] ?>'), url(<?php echo $fontData['font_path'] ?>.eot) format('embedded-opentype'), url(<?php echo $fontData['font_path'] ?>.woff) format('woff');
			}
		<?php
		endforeach;
		endif;	
			
		$fontsImplementRawData 	= get_option('uaf_font_implement');
		$fontsImplementData		= json_decode($fontsImplementRawData, true);
		if (!empty($fontsImplementData)):
			foreach ($fontsImplementData as $key=>$fontImplementData): ?>
				<?php echo $fontImplementData['font_elements']; ?>{
					font-family: '<?php echo $fontsData[$fontImplementData['font_key']]['font_name']; ?>' !important;
				}
		<?php
			endforeach;
		endif;	
		$uaf_style = ob_get_contents();
		$uafStyleSheetPath	= realpath('../wp-content/plugins/use-any-font/css/uaf.css');
		$fh = fopen($uafStyleSheetPath, 'w') or die("Can't open file");
		fwrite($fh, $uaf_style);
		fclose($fh);
	ob_end_clean();
}
?>