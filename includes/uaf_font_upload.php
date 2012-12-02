<?php
if ($_POST['submit-uaf-font']){	
	$uaf_api_key		= get_option('uaf_api_key');
	$font_file_name 	= $_FILES['font_file']['name'];
	$font_file_details 	= pathinfo($_FILES['font_file']['name']);
	$file_extension		= strtolower($font_file_details['extension']);	
	$upload_dir 		= wp_upload_dir();	
	if ($file_extension == 'woff'):
		$newFileName		= date('ymdhis').$_FILES['font_file']['name'];
		$newFilePath		= $upload_dir['path'].'/'.$newFileName;
		$newFileUrl			= $upload_dir['url'].'/'.$newFileName;
		move_uploaded_file($_FILES['font_file']['tmp_name'],$newFilePath);
	else:
		$fontNameToStore 		= date('ymdhis').$font_file_details['filename'];
		$fontNameToStoreWithUrl = $upload_dir['url'].'/'.$fontNameToStore;
		//CONVERT TO WOFF
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: multipart/form-data"));
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux i686; rv:6.0) Gecko/20100101 Firefox/6.0Mozilla/4.0 (compatible;)");
		curl_setopt($ch, CURLOPT_URL, 'http://dineshkarki.com.np/font-convertor/woff.php');
		curl_setopt($ch, CURLOPT_POST, true);
		$post = array(
			'ttffile' => "@".$_FILES['font_file']['tmp_name'],
			'api_key' => $uaf_api_key
		);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$response = curl_exec($ch);
		if(curl_errno($ch)) {
			echo 'Error: ' . curl_error($ch);
			exit();
		}
		else {
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if ($http_code == 200) {
				$newFileName		= $fontNameToStore.'.woff';
				$newFilePath		= $upload_dir['path'].'/'.$newFileName;
				$fh = fopen($newFilePath, 'w') or die("Can't open file");
				fwrite($fh, $response);
				fclose($fh);
			} else {
				echo $response;
				exit();
			}
		}
		
		//CONVERT TO EOT
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: multipart/form-data"));
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux i686; rv:6.0) Gecko/20100101 Firefox/6.0Mozilla/4.0 (compatible;)");
		curl_setopt($ch, CURLOPT_URL, 'http://dineshkarki.com.np/font-convertor/eot.php');
		curl_setopt($ch, CURLOPT_POST, true);
		$post = array(
			'ttffile' => "@".$_FILES['font_file']['tmp_name'],
			'api_key' => $uaf_api_key
		);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$response = curl_exec($ch);
		if(curl_errno($ch)) {
			echo 'Error: ' . curl_error($ch);
			exit();
		}
		else {
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if ($http_code == 200) {
				$newFileName		= $fontNameToStore.'.eot';
				$newFilePath		= $upload_dir['path'].'/'.$newFileName;
				$fh = fopen($newFilePath, 'w') or die("Can't open file");
				fwrite($fh, $response);
				fclose($fh);
			} else {
				echo $response;
				exit();
			}
		}
	endif;
	$fontsRawData 	= get_option('uaf_font_data');
	$fontsData		= json_decode($fontsRawData, true);
	if (empty($fontsData)):
		$fontsData = array();
	endif;
	
	$fontsData[date('ymdhis')]	= array('font_name' => $_POST['font_name'], 'font_path' => $fontNameToStoreWithUrl);
	$updateFontData	= json_encode($fontsData);
	update_option('uaf_font_data',$updateFontData);
	uaf_write_css();
	$show_msg 		= 'Font Added';
}

if ($_GET['delete_font_key']):
	$fontsRawData 	= get_option('uaf_font_data');
	$fontsData		= json_decode($fontsRawData, true);
	$key_to_delete	= $_GET['delete_font_key'];
	@unlink(realpath($fontsData[$key_to_delete]['font_path'].'.woff'));
	@unlink(realpath($fontsData[$key_to_delete]['font_path'].'.eot'));
	unset($fontsData[$key_to_delete]);
	$updateFontData	= json_encode($fontsData);
	update_option('uaf_font_data',$updateFontData);
	$show_msg = 'Font Deleted';
	uaf_write_css();
endif;
?>

<table class="wp-list-table widefat fixed bookmarks">
    <thead>
        <tr>
            <th>Upload Fonts</th>
        </tr>
    </thead>
    <tbody>
    <tr>
        <td>

<?php if (!empty($show_msg)):?>
	<div class="updated" id="message"><p><?php echo $show_msg ?></p></div>
<?php endif; ?>

<?php 
$fontsRawData 	= get_option('uaf_font_data');
$fontsData		= json_decode($fontsRawData, true);
?>

<p align="right"><input type="button" name="open_add_font" onClick="open_add_font();" class="button-primary" value="Add Fonts" /><br/></p>

<div id="font-upload" style="display:none;">
	<form action="options-general.php?page=use-any-font/plugin_interface.php" id="open_add_font_form" method="post" enctype="multipart/form-data">
    	<table class="uaf_form">
        	<tr>
            	<td width="175">Font Name</td>
                <td><input type="text" name="font_name" value="" class="required" style="width:200px;" /></td>
            </tr>	
            <tr>    
                <td>Font File</td>
                <td><input type="file" name="font_file" value="" class="required" /></td>
            </tr>
            <tr>        
                <td>&nbsp;
                	
                </td>
                <td><input type="submit" name="submit-uaf-font" class="button-primary" value="Upload" />
                <p>By clicking on Upload, you confirm that you have rights to use this font.</p>
                </td>
            </tr>
        </table>	
    </form>
    <br/><br/>
</div>

<table cellspacing="0" class="wp-list-table widefat fixed bookmarks">
	<thead>
    	<tr>
        	<th width="20">Sn</th>
            <th>Font</th>
            <th width="100">Delete</th>
        </tr>
    </thead>
    
    <tbody>
    	<?php if (!empty($fontsData)): ?>
        <?php 
		$sn = 0;
		foreach ($fontsData as $key=>$fontData):
		$sn++
		?>
        <tr>
        	<td><?php echo $sn; ?></td>
            <td><?php echo $fontData['font_name'] ?></td>
            <td><a onclick="if (!confirm('Are you sure ?')){return false;}" href="options-general.php?page=use-any-font/plugin_interface.php&delete_font_key=<?php echo $key; ?>">Delete</a></td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr>
        	<td colspan="3">No font found. Please click on Add Fonts to add font</td>
        </tr>
        <?php endif; ?>        
    </tbody>
    
</table>

<script>
	function open_add_font(){
		jQuery('#font-upload').toggle('fast');
		jQuery("#open_add_font_form").validate({
		  rules: {
			font_name 			: {required:true, maxlength:40},
			font_file 			: {required:true}
			//accept:"ttf|otf|woff"
			},
		  messages:{
			//font_file			: {accept:'Only accepts ttf,otf,woff'}
		  }
		});
	}	
</script>
<br/>
</td>
</tr>
</tbody>
</table><br/>