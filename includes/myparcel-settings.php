<?php declare(strict_types=1);

add_action('admin_enqueue_scripts','settingPageJsCss',999);

/**
 *
 * @return void
 */
function settingPageJsCss(): void
{	
	wp_enqueue_script('validation',plugins_url('',__FILE__).'/../assets/admin/js/jquery.validate.js','','',false);
	wp_register_script('setting_page_js',plugins_url('',__FILE__).'/../assets/admin/js/setting-page.js','','',true);
	wp_enqueue_script('setting_page_js');
}

add_action( 'admin_init', 'registerSettings' );

/** 
 * Register setting in setting panel
 *
 * @return void
 */
function registerSettings(): void
{ 
	add_option( 'client_key', '');
	add_option( 'client_secret_key', '');
	add_option( 'ship_exists', '0');
	add_option( 'act_test_mode', '0');
   	
   	register_setting( 'myplugin_options_group', 'client_key');
   	register_setting( 'myplugin_options_group', 'client_secret_key');
   	register_setting( 'myplugin_options_group', 'ship_exists');   	
   	register_setting( 'myplugin_options_group', 'act_test_mode');
   	register_setting( 'myplugin_options_group', 'checkValidation','validationCallBack');
}

/**
 *
 * @return bool
 */
function validationCallBack(): bool
{
	$error = false;
	$clientKey 	= get_option('client_key');
	$secretKey 	= get_option('client_secret_key');
	$actTestMode = get_option('act_test_mode');
	if (empty($clientKey) || empty($secretKey)) {
		$error = true;
	}	
	if ($error) {	
		add_settings_error('show_message',esc_attr('settings_updated'),__('Settings NOT saved. Please fill all the required fields.'),'error');
    	add_action('admin_notices', 'printErrors');	
    	updateOption();

    	return false;
	}else{
		
		add_settings_error('show_message',esc_attr('settings_updated'),__('Settings saved.'),'updated');
    	add_action('admin_notices', 'printErrors');	

    	return true;
	}
    
}
/**
 *
 * @return void
 */
function printErrors(): void
{
    settings_errors( 'show_message' );
}

/**
 *
 * @return void
 */
function updateOption(): void
{
	update_option('client_key','');
	update_option('client_secret_key','');	
	update_option( 'ship_exists', '0');	
}

add_action('admin_menu', 'addSettingMenu');

/** 
 *
 * @return void
 */
function addSettingMenu(): void
{
  add_options_page('API Setting', 'MyParcel.com API setting', 'manage_options', 'api_setting', 'settingPage');  
}

/** 
 *
 * @return void
 */
function settingPage(): void
{
	global $woocommerce;
    $countries_obj = new WC_Countries();
    $countries = $countries_obj->__get('countries');

?>
  	<div>
	  
	  	<h2>MyParcel.com API setting</h2>

	  	<form method="post" action="options.php" id="api-setting-form">
	  		<?php 
	  		settings_fields( 'myplugin_options_group' );

	  		?>
	  		 
		    <table class="form-table">
				
				<tbody>					
					<tr valign="top">
					  	<th scope="row"><label for="client_key">* Client ID </label></th>
					  	<td>
					  		<input type="text" id="client_key" class="regular-text" name="client_key" value="<?php echo get_option('client_key'); ?>" />
					  	</td>
					</tr>
					<tr valign="top">

					  	<th scope="row"><label for="client_secret_key">* Client secret key </label></th>
					  	<td>
					  		<input type="password" id="client_secret_key"  class="regular-text" name="client_secret_key" value="<?php echo get_option('client_secret_key'); ?>" />
					  	</td>
					</tr>					
					<tr>
						<th scope="row">Activate testmode</th>
						<td> 
							<fieldset><legend class="screen-reader-text"><span></span></legend>
								<label for="users_can_register">
									<input type="checkbox" name="act_test_mode" value="1" <?php checked(1, (int)get_option('act_test_mode'));?>> 
								</label>
							</fieldset>
						</td>
					</tr>
				</tbody>
		    </table>
			<h2>MyParcel.com</h2>
			<table  cellpadding = "5" cellspacing = "5" class="form-table">
		         <tr valign="top">
		            <th scope="row"><label>Current version</label> </th>
		            <td>1.0</td>
		         </tr>
		         <tr valign="top">
		            <th scope="row"><label>MyParcel.com support</label> </th>
		            <td><a href="https://myparcelcom.freshdesk.com/a/solutions/folders/16000093107" target="_blank">https://myparcelcom.freshdesk.com/a/solutions/folders/16000093107</a></td>
		         </tr>		         
		     </table>			
	  		<?php submit_button('Save changes'); ?>
	  	</form>
  	</div>
<?php
} ?>