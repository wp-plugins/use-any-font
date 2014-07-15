<?php
add_action('admin_menu', 'uaf_create_menu');
add_action("admin_print_scripts", 'adminjslibs');
add_action("admin_print_styles", 'adminCsslibs');
add_action('wp_enqueue_scripts', 'uaf_client_css');
add_action('plugins_loaded', 'uaf_update_check');

$uaf_disbale_editor_font_list_value = get_option('uaf_disbale_editor_font_list');
if ($uaf_disbale_editor_font_list_value != 1):
	add_filter('mce_buttons_2', 'wp_editor_fontsize_filter');
	add_filter('tiny_mce_before_init', 'uaf_mce_before_init' );
endif;

function uaf_client_css() {
	$uaf_upload 	= wp_upload_dir();
	$uaf_upload_url = $uaf_upload['baseurl'];
	$uaf_upload_url = $uaf_upload_url . '/useanyfont/';
	wp_register_style( 'uaf_client_css', $uaf_upload_url.'uaf.css');
	wp_enqueue_style( 'uaf_client_css' );	
}

function adminjslibs(){
	wp_register_script('uaf_validate_js',plugins_url("use-any-font/js/jquery.validate.min.js"));		
	wp_enqueue_script('uaf_validate_js');	
}

function adminCsslibs(){
	$uaf_upload 	= wp_upload_dir();
	$uaf_upload_url = $uaf_upload['baseurl'];
	$uaf_upload_url = $uaf_upload_url . '/useanyfont/';
	wp_register_style('uaf-admin-style', plugins_url('use-any-font/css/uaf_admin.css'));
    wp_enqueue_style('uaf-admin-style');
	wp_register_style('uaf-font-style', $uaf_upload_url.'admin-uaf.css');
    wp_enqueue_style('uaf-font-style');
	add_editor_style($uaf_upload_url.'admin-uaf.css');
}
		
function uaf_create_menu() {
	add_options_page('Use Any Font', 'Use Any Font', 'administrator', __FILE__, 'uaf_settings_page');	
}

function uaf_create_folder() {
	$uaf_upload 	= wp_upload_dir();
	$uaf_upload_dir = $uaf_upload['basedir'];
	$uaf_upload_dir = $uaf_upload_dir . '/useanyfont/';
	if (! is_dir($uaf_upload_dir)) {
       mkdir( $uaf_upload_dir, 0755 );
    }
}

function uaf_activate(){
	uaf_create_folder(); // CREATE FOLDER
	uaf_write_css(); //rewrite css when plugin is activated after update or somethingelse......
}

function uaf_update_check() { // MUST CHANGE WITH EVERY VERSION
    $uaf_version_check = get_option('uaf_current_version');
	if ($uaf_version_check != '4.2.1'):
		update_option('uaf_current_version', '4.2.1');
		if ($uaf_version_check < 4.0):
			uaf_create_folder();
			uaf_move_file_to_newPath();
		endif;
		uaf_write_css();
	endif;	
}

function uaf_settings_page() {
	$uaf_upload 	= wp_upload_dir();
	$uaf_upload_dir = $uaf_upload['basedir'];
	$uaf_upload_dir = $uaf_upload_dir . '/useanyfont/';
	$uaf_upload_url = $uaf_upload['baseurl'];
	$uaf_upload_url = $uaf_upload_url . '/useanyfont/';
	include('includes/uaf_header.php');
	include('includes/uaf_font_upload.php');
	include('includes/uaf_font_implement.php');
	include('includes/uaf_footer.php');
}

// MOVING OLD FONTFILE PATH TO NEW PATH 
function uaf_move_file_to_newPath(){
	$uaf_upload 	= wp_upload_dir();
	$uaf_upload_dir = $uaf_upload['basedir'];
	$uaf_upload_dir = $uaf_upload_dir . '/useanyfont/';
	$fontsRawData 	= get_option('uaf_font_data');
	$fontsData		= json_decode($fontsRawData, true);
	if (!empty($fontsData)):
		foreach ($fontsData as $key=>$fontData):
			
			$oldFilePathInfo		= pathinfo($fontData['font_path']);			
			$parsedPath				= parse_url($fontData['font_path']);
			$relativeFilePath		= $_SERVER['DOCUMENT_ROOT'].$parsedPath['path'];			
			$oldfilename			= $oldFilePathInfo['filename'];
			
			if (file_exists($relativeFilePath.'.woff')){
				
				$woffFileContent 		= file_get_contents($relativeFilePath.'.woff');
				$eotFileContent 		= file_get_contents($relativeFilePath.'.eot');
				
				$fhWoff = fopen($uaf_upload_dir.'/'.$oldfilename.'.woff' , 'w') or die("can't open file. Make sure you have write permission to your upload folder");
				fwrite($fhWoff, $woffFileContent);
				fclose($fhWoff);
				
				$fhEot = fopen($uaf_upload_dir.'/'.$oldfilename.'.eot' , 'w') or die("can't open file. Make sure you have write permission to your upload folder");
				fwrite($fhEot, $eotFileContent);
				fclose($fhEot);
				
				$fontsData[$key]['font_path']	= $oldfilename;
			}
		endforeach;
	endif;
	
	$updateFontData	= json_encode($fontsData);
	update_option('uaf_font_data',$updateFontData);	
}

function uaf_write_css(){
	$uaf_upload 	= wp_upload_dir();
	$uaf_upload_dir = $uaf_upload['basedir'];
	$uaf_upload_dir = $uaf_upload_dir . '/useanyfont/';
	$uaf_upload_url = $uaf_upload['baseurl'];
	$uaf_upload_url = $uaf_upload_url . '/useanyfont/';	
	$uaf_upload_url = preg_replace('#^https?:#', '', $uaf_upload_url);
	
	ob_start();
		$fontsRawData 	= get_option('uaf_font_data');
		$fontsData		= json_decode($fontsRawData, true);
		if (!empty($fontsData)):
			foreach ($fontsData as $key=>$fontData): ?>
			@font-face {
				font-family: '<?php echo $fontData['font_name'] ?>';
				font-style: normal;
				src: url('<?php echo $uaf_upload_url.$fontData['font_path'] ?>.eot');
				src: local('<?php echo $fontData['font_name'] ?>'), url('<?php echo $uaf_upload_url.$fontData['font_path'] ?>.eot') format('embedded-opentype'), url('<?php echo $uaf_upload_url.$fontData['font_path'] ?>.woff') format('woff');
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
		$uafStyleSheetPath	= $uaf_upload_dir.'/uaf.css';
		$fh = fopen($uafStyleSheetPath, 'w') or die("Can't open file");
		fwrite($fh, $uaf_style);
		fclose($fh);
	ob_end_clean();
	
	ob_start();
		$fontsRawData 	= get_option('uaf_font_data');
		$fontsData		= json_decode($fontsRawData, true);
		if (!empty($fontsData)):
			foreach ($fontsData as $key=>$fontData): ?>
			@font-face {
				font-family: '<?php echo $fontData['font_name'] ?>';
				font-style: normal;
				src: url('<?php echo $uaf_upload_url.$fontData['font_path'] ?>.eot');
				src: local('<?php echo $fontData['font_name'] ?>'), url('<?php echo $uaf_upload_url.$fontData['font_path'] ?>.eot') format('embedded-opentype'), url('<?php echo $uaf_upload_url.$fontData['font_path'] ?>.woff') format('woff');
			}
		<?php
		endforeach;
		endif;
		$uaf_style = ob_get_contents();
		$uafStyleSheetPath	= $uaf_upload_dir.'/admin-uaf.css';
		$fh = fopen($uafStyleSheetPath, 'w') or die("Can't open file");
		fwrite($fh, $uaf_style);
		fclose($fh);
	ob_end_clean();
}

include('includes/uaf_editor_setup.php');