<?php 
if ($_POST['ucf_api_key_submit']){
	$api_key_return = wp_remote_fopen('http://dineshkarki.com.np/license/validate_key.php?license_key='.$_POST['uaf_api_key']);
	$api_key_return = json_decode($api_key_return);
	if (!empty($api_key_return)){
		if ($api_key_return->status == 'success'){
			update_option('uaf_api_key', $_POST['uaf_api_key']);
		}
		$api_message 	= $api_key_return->msg;
	} else {
		$api_message 	= 'Sorry there was an error. Please try again.';
	}	
}
$uaf_api_key			=	get_option('uaf_api_key');
?>
<?php if (!empty($api_message)):?>
	<div class="updated" id="message"><p><?php echo $api_message ?></p></div>
<?php endif; ?>
<div class="wrap">
<h2>Use Any Font</h2>
<table width="100%">
	<tr>
    	<td valign="top">
            <table class="wp-list-table widefat fixed bookmarks">
                <thead>
                    <tr>
                        <th>API KEY</th>
                    </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                    	<form action="options-general.php?page=use-any-font/plugin_interface.php" method="post" >
                        API KEY :
                    	<?php if (empty($uaf_api_key)): ?>
                        <input name="uaf_api_key" type="text" style="width:350px; margin-left:50px;" />
                        <input type="submit" name="ucf_api_key_submit" class="button-primary" value="Verify" style="padding:2px;" />
                        <br/> <br/>                       
                        Please keep the API key to start using this plugin. Offer your contribution (Free to $100) and get the API key from <a href="http://dineshkarki.com.np/license/" target="_blank">here</a>.<br/>
                        <?php else: ?>
                        	<span class="active_key"><?php echo $uaf_api_key;  ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - Active</span>
                        <?php endif;?>
                        </form>
                        <br/>                        
                        <strong>Note</strong> : API key is need to connect to our server for font conversion. Our server converts your fonts to required types and sends it back.
                        <br/><br/>
                   	</td>
                    
                </tr>
                </tbody>
            </table>
            <br/>