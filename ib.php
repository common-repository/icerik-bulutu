<?php
/**
* Plugin Name: İçerik Bulutu
* Plugin URI: https://www.icerikbulutu.com/teknolojiler/wordpress-entegrasyonu/
* Description: Official plugin of Icerik Bulutu. It directly imports and synchronizes contents written by professional content writers from the Icerik Bulutu
* Version: 2.1
* Author: İçerik Bulutu
* Author URI: https://icerikbulutu.com/
* Text Domain: icerik-bulutu
* Domain Path: /languages
**/
function my_plugin_load_plugin_textdomain() {
    load_plugin_textdomain( 'icerik-bulutu', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'my_plugin_load_plugin_textdomain' );

add_action( 'admin_init', 'icerik_bulutu_settings_init' );
function icerik_bulutu_settings_init(  ) { 
    register_setting( 'pluginPage', 'icerik_bulutu_settings' );

    add_settings_section(
        'icerik_bulutu_pluginPage_section', 
        __( 'İçerik Bulutu WordPress Plugin', 'icerik-bulutu' ), 
        'icerik_bulutu_settings_section_callback', 
        'pluginPage'
    );

    add_settings_field( 
        'icerik_bulutu_status', 
        __( 'Save Imported Posts As', 'icerik-bulutu' ), 
        'icerik_bulutu_status_render', 
        'pluginPage', 
        'icerik_bulutu_pluginPage_section' 
    );

    add_settings_field( 
        'icerik_bulutu_author', 
        __( 'Choose Author', 'icerik-bulutu' ), 
        'icerik_bulutu_author_render', 
        'pluginPage', 
        'icerik_bulutu_pluginPage_section' 
    );


}


function icerik_bulutu_status_render(  ) { 

    $options = get_option( 'icerik_bulutu_settings' );
    ?>
    <select name='icerik_bulutu_settings[icerik_bulutu_status]'>
        <option value='publish' <?php selected( $options['icerik_bulutu_status'], 'publish' ); ?>><?php _e( 'Published', 'icerik-bulutu' ); ?></option>
        <option value='draft' <?php selected( $options['icerik_bulutu_status'], 'draft' ); ?>><?php _e( 'Draft', 'icerik-bulutu' ); ?></option>
    </select>

<?php

}


function icerik_bulutu_author_render(  ) { 

    $options = get_option( 'icerik_bulutu_settings' );
    ?>
    <?php wp_dropdown_users( array( 'role__in' => array('administrator','author','editor'),'name' => 'icerik_bulutu_settings[icerik_bulutu_author]','selected' => $options['icerik_bulutu_author']) ); ?>

<?php

}


function icerik_bulutu_settings_section_callback(  ) { 

    _e( 'Using our WordPress plugin, you can import content from İçerik Bulutu to your WordPress website either manually or automatically, depending on your content strategy.', 'icerik-bulutu' );

}


function plugin_name_scripts() {
wp_enqueue_style( 'style', plugins_url('css/admin.css', __FILE__));
//wp_enqueue_script( 'script', plugins_url('js/cookie.js', __FILE__), array('jquery'));
}
add_action('init', 'plugin_name_scripts');

function ib_admin_menu() {
    
    add_menu_page('Settings', 'İçerik Bulutu', 'manage_options', 'icerik-bulutu', 'ib_admin_page_contents','dashicons-cloud', );
    add_submenu_page(
        'icerik-bulutu',       // parent slug
        'Settings',    // page title
        __( 'Settings', 'icerik-bulutu' ),             // menu title
        'manage_options',           // capability
        'icerik-bulutu',      // slug
        'ib_admin_page_contents' // callback
    );
    add_submenu_page(
        'icerik-bulutu',       // parent slug
        'Import',    // page title
        __( 'Import', 'icerik-bulutu' ),             // menu title
        'manage_options',           // capability
        'icerik-bulutu-content',      // slug
        'ib_contents' // callback
    );
    add_submenu_page(
        'icerik-bulutu',       // parent slug
        'İçerik Bulutu İçe Aktar',    // page title
        null,             // menu title
        'manage_options',           // capability
        'icerik-bulutu-import',      // slug
        'ib_import' // callback
    ); 
}
add_action( 'admin_menu', 'ib_admin_menu' );

function ib_contents() {
	global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'icerikbulutu_apikey';
	$resultslogin = $wpdb->get_var( "SELECT apikey FROM $table_name");
	if($resultslogin == null){
		wp_die( __( 'Please enter your API Key First', 'icerik-bulutu' ) );
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'icerik-bulutu' ) );
	}
	include( dirname( __FILE__ ) . '/contents.php' );
}
function ib_import() {
    include( dirname( __FILE__ ) . '/import.php' );
}

function ib_admin_page_contents() {
	global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $table_name = $wpdb->prefix . 'icerikbulutu_apikey';

    if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {
		$results = $wpdb->get_var( "SELECT apikey FROM $table_name");
	}
	else {
	    $sql = "CREATE TABLE $table_name (
        id INTEGER NOT NULL AUTO_INCREMENT,
        apikey TEXT NOT NULL,
        PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta( $sql );
    }

	if(!empty($results)){
	    ?>
        <div class="wrap">
            <div class="wrap-ib">

                <form action='options.php' method='post'>

                    <?php
                    settings_fields( 'pluginPage' );
                    do_settings_sections( 'pluginPage' );
                    submit_button();
                    ?>
                    <?php if( isset($_GET['settings-updated']) ) { ?>
                    <div class="notice notice-success is-dismissible">
                        <p><strong><?php _e('Settings Saved', 'icerik-bulutu' ) ?></strong></p>
                    </div>
                    <?php } ?>

                </form>
                <form method="post"> 
                    <input type="submit" name="delete" class="button" value="<?php _e( 'Disconnect to İçerik Bulutu', 'icerik-bulutu' ); ?>" /> 
                </form>
                <div class="footer">
                    <a target="_blank" href="https://icerikbulutu.com/"><img class="logo-footer" src="<?php echo plugin_dir_url( __FILE__ ); ?>img/logo_d.svg"></a>
                </div>
            </div>
        </div>
	    <?php
        if ( isset( $_POST['save'] ) ){
            $poststatus = $_POST['post_status'];
            $postauthor = $_POST['author'];
            $wpdb->query("UPDATE $table_name SET status = '$poststatus', author = '$postauthor' WHERE id = 1");
            $resultsstatus = $wpdb->get_var( "SELECT status FROM $table_name");
        }

	    if(array_key_exists('delete', $_POST)) { 
			$wpdb->query("TRUNCATE TABLE $table_name");
			?> <script>location.reload();</script><?php
		}
    

	}else{
		?>
        <div class="wrap">
            <div class="wrap-ib">
                <h2><?php _e( 'İçerik Bulutu WordPress Plugin', 'icerik-bulutu' ); ?></h2>
                <?php _e( 'Using our WordPress plugin, you can import content from İçerik Bulutu to your WordPress website either manually or automatically, depending on your content strategy.', 'icerik-bulutu' ); ?>
        		<form style="margin-top:20px" action="" id="postjob" method="post">
        	        <table>
        	            <tr>
        	                <td><label style="font-weight: 500" for="api"><?php _e( 'Enter API Key', 'icerik-bulutu' ); ?></label></td>
        	                <td><input type="text" size="40" name="api" id="api" value="" /></td>
        	            </tr>
        	            <tr>
        	                <td><button style="margin-top:20px;" class="button button-primary" type="submit" name="submit"><?php _e( 'Save', 'icerik-bulutu' ); ?></button></td>
        	            </tr>
        	        </table>
           		</form>
                <div class="footer">
                    <a target="_blank" href="https://icerikbulutu.com/"><img class="logo-footer" src="<?php echo plugin_dir_url( __FILE__ ); ?>img/logo_d.svg"></a>
                </div>
            </div>
        </div>
		<?php
		}
    	if ( isset( $_POST['submit'] ) ){
	            $wpdb->query("TRUNCATE TABLE $table_name");
	            $wpdb->insert($table_name, array('apikey' => $_POST['api']));

	            $resultskey = $wpdb->get_var( "SELECT apikey FROM $table_name");
	            $data_arraykey =  array(
			      "Entity" => array(
			            "WordpressKey"=>$resultskey
			      ),
			    );
			    $makekey_call = callAPI('POST', 'https://api.icerikbulutu.com/v2/word-press/key', json_encode($data_arraykey));
    			$responsekey = json_decode($makekey_call,true);

    			if($responsekey["HasError"] == 1){
    				echo "
                    <div class='notice notice-error is-dismissible'>
                        <p>". __( 'API Key is invalid', 'icerik-bulutu' ) ."</p>
                    </div>
                    ";
    				$wpdb->query("TRUNCATE TABLE $table_name");
    			}
    			else{
    				?> <script>location.reload();</script><?php
    			}
	    }
}

function callAPI($method, $url, $data){
    $curl = curl_init();

    switch ($method){
      case "POST":
         curl_setopt($curl, CURLOPT_POST, 1);
         if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
         break;
      case "PUT":
         curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
         if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);                              
         break;
      default:
         if ($data)
            $url = sprintf("%s?%s", $url, http_build_query($data));
    }
    // OPTIONS:
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    // EXECUTE:
    $result = curl_exec($curl);
    if(!$result){
        wp_die( __( 'There is a problem. Please try again later.', 'icerik-bulutu' ) );
    }
    curl_close($curl);
    return $result;
}
