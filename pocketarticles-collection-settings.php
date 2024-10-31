<?php
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

if (!class_exists("Pocket_Articles_Collection_Settings")) :

class Pocket_Articles_Collection_Settings {

	public static $default_settings = 
		array( 	'consumer_key' => '17341-fb0e02a7d395f41a67aeed9e',
				'access_token' => '',
				'auth_code' => '',
			  	'favoritesonly_checkbox' => '1'
				);
	var $pagehook, $page_id, $settings_field, $options;

	
	function __construct() {	
		$this->page_id = 'pocket_articles';
		// This is the get_options slug used in the database to store our plugin option values.
		$this->settings_field = 'pocketarticlescollection_settings';
		$this->options = get_option( $this->settings_field );

		add_action('admin_init', array($this,'admin_init'), 20 );
		add_action( 'admin_menu', array($this, 'admin_menu'), 20);
	}
	
	function admin_init() {
		register_setting( $this->settings_field, $this->settings_field, array($this, 'sanitize_theme_options') );
		add_option( $this->settings_field, Pocket_Articles_Collection_Settings::$default_settings );
		
		
		/* 
			This is needed if we want WordPress to render our settings interface
			for us using -
			do_settings_sections
			
			It sets up different sections and the fields within each section.
		*/
		add_settings_section('pocketarticles_main', '',  
			array($this, 'main_section_text'), 'pocketarticles_settings_page');

		add_settings_field('consumer_key', __('Consumer Key','pocketarticles-collection'), 
			array($this, 'render_consumer_key_text'), 'pocketarticles_settings_page', 'pocketarticles_main');

		add_settings_field('access_token', __('Access Token','pocketarticles-collection'), 
			array($this, 'render_access_token_text'), 'pocketarticles_settings_page', 'pocketarticles_main');

		add_settings_field('favoritesonly_checkbox', __('Favorites only','pocketarticles-collection'), 
			array($this, 'render_favoritesonly_checkbox'), 'pocketarticles_settings_page', 'pocketarticles_main', 
			array('id' => 'favoritesonly_checkbox', 'value' => '1', 'text' => '') );
	}

	function admin_menu() {
		if ( ! current_user_can('update_plugins') )
			return;
	
		// Add a new submenu to the standard Settings panel
		$this->pagehook = $page =  add_options_page(	
			__('Pocket articles', 'pocketarticles-collection'), __('Pocket articles', 'pocketarticles-collection'), 
			'administrator', $this->page_id, array($this,'render') );
		
		// Executed on-load. Add all metaboxes.
		add_action( 'load-' . $this->pagehook, array( $this, 'metaboxes' ) );

		// Include js, css, or header *only* for our settings page
		add_action("admin_print_scripts-$page", array($this, 'js_includes'));
//		add_action("admin_print_styles-$page", array($this, 'css_includes'));
		add_action("admin_head-$page", array($this, 'admin_head') );
	}

	function admin_head() { ?>
		<style>
		.settings_page_pocket_articles label { display:inline-block; width: 150px; }
		</style>

	<?php }

     
	function js_includes() {
		// Needed to allow metabox layout and close functionality.
		wp_enqueue_script( 'postbox' );
	}


	/*
		Sanitize our plugin settings array as needed.
	*/	
	function sanitize_theme_options($options) {
		$options['consumer_key'] = stripcslashes($options['consumer_key']);
		$options['access_token'] = stripcslashes($options['access_token']);
		return $options;
	}


	/*
		Settings access functions.
		
	*/
	protected function get_field_name( $name ) {

		return sprintf( '%s[%s]', $this->settings_field, $name );

	}

	protected function get_field_id( $id ) {

		return sprintf( '%s[%s]', $this->settings_field, $id );

	}

	protected function get_field_value( $key ) {

		return $this->options[$key];

	}
		

	/*
		Render settings page.
		
	*/
	private function curPageURL() {
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
	
	function render() {
		global $wp_meta_boxes;

		if (isset($_GET["start_authorization"]))
		{
			$args = array(
				'method' => 'POST',
				'httpversion' => '1.1',
				'headers' => array( 
					'Authorization' => 'Basic ' . $credentials,
					'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'
				),
				'body' => array( 'consumer_key' => esc_attr( $this->get_field_value( 'consumer_key' ) ), 'redirect_uri' => $this->curPageURL() )
				);

			$response = wp_remote_post( 'http://getpocket.com/v3/oauth/request', $args );
			if (is_wp_error($response) || $response["response"]["code"] != 200)
			{
				$message = __('ERROR: Could not connect to pocket for authorization!','pocketarticles-collection');
			}
			else
			{
				$code = str_replace("code=", "", $response["body"]);
				$this->options["auth_code"] = $code;
				update_option($this->settings_field, $this->options);
				$message = __('Click button to start authorization','pocketarticles-collection');
			}
			
		}
		
		if (isset($_GET["authorized_from_pocket"]) && isset($this->options["auth_code"]))
		{
			$options = get_option( $this->settings_field );
			$args = array(
				'method' => 'POST',
				'httpversion' => '1.1',
				'headers' => array( 
					'Authorization' => 'Basic ' . $credentials,
					'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'
				),
				'body' => array( 'consumer_key' => esc_attr( $this->get_field_value( 'consumer_key' ) ), 'code' => $this->get_field_value( 'auth_code' ) )
			);
			$response = wp_remote_post( 'http://getpocket.com/v3/oauth/authorize', $args );
			
			if (is_wp_error($response) || $response["response"]["code"] != 200)
			{
				$message = __('ERROR: Could not connect to pocket for authorization!','pocketarticles-collection');
			}
			else
			{
				$response_array = explode("&", $response["body"]);
				$token_found = false;
				foreach($response_array as $response_array_item)
				{
					if (stristr ($response_array_item, "access_token") )
					{
						$access_token = str_replace("access_token=", "", $response_array_item);
						unset($this->options["auth_code"]);
						$this->options["access_token"] = $access_token;
						update_option($this->settings_field, $this->options);
						$token_found = true;
					}
				}
				if (!$token_found)
				{
					$message = __('ERROR: Could not connect to pocket for authorization!', 'pocketarticles-collection');
				}
				else
				{
					$message = __('Congratulations! Your application is now authorized with Pocket.', 'pocketarticles-collection');
				}
			}
		}
		$title = __('Pocket articles', 'pocketarticles-collection');
		?>
		<div class="wrap">   
			<?php screen_icon(); ?>
			<h2><?php echo esc_html( $title ); ?></h2>
		
			<?php
				if ( !empty($message) ) : 
				?>
				<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
				<?php 
				endif; 
			?>
			<div>
			<?php if (isset($_GET["start_authorization"]) ) {?>
			<input type="button" onclick="location.href='https://getpocket.com/auth/authorize?redirect_uri=<?php echo urlencode(str_replace("&start_authorization=true", "", $this->curPageURL()) . "&authorized_from_pocket=true") . "&request_token=" . $this->get_field_value( 'auth_code' ) ?>';" class="button button-primary" name="save_options" value="<?php esc_attr_e('Authorize with Pocket', 'pocketarticles-collection'); ?>" />
			<?php 
			} else { 
				if (!isset($_GET["authorized_from_pocket"]) ) {
			?>
			<input type="button" onclick="location.href='<?php echo $this->curPageURL() . "&start_authorization=true" ?>';" class="button button-primary" name="start_autho_button" value="<?php esc_attr_e('Click here if you want to authorize your app to your pocket account', 'pocketarticles-collection'); ?>" />
			<?php } } ?>
			</div>
			<form method="post" action="options.php">
				<p>
				<input type="submit" class="button button-primary" name="save_options" value="<?php esc_attr_e('Save Options', 'pocketarticles-collection'); ?>" />
				
				</p>
                
                <div class="metabox-holder">
                    <div class="postbox-container" style="width: 99%;">
                    <?php 
						// Render metaboxes
                        settings_fields($this->settings_field); 
                        do_meta_boxes( $this->pagehook, 'main', null );
                      	if ( isset( $wp_meta_boxes[$this->pagehook]['column2'] ) )
 							do_meta_boxes( $this->pagehook, 'column2', null );
                    ?>
                    </div>
                </div>

				<p>
				<input type="submit" class="button button-primary" name="save_options" value="<?php esc_attr_e('Save Options', 'pocketarticles-collection'); ?>" />
				</p>
			</form>
			<p>
				<?php esc_attr_e('Pocket articles background job history', 'pocketarticles-collection'); 
				$run_messages = get_option('pocketarticlescollection_run_messages');
				foreach($run_messages as $message){echo '<br/>'.$message;}?>
			</p>
			</div>
        
        <!-- Needed to allow metabox layout and close functionality. -->
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function ($) {
				// close postboxes that should be closed
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
			});
			//]]>
		</script>
	<?php }
	
	function metaboxes() {

		// Example metabox containing an example text box & two example checkbox controls.
		// Example settings rendered by WordPress using the do_settings_sections function.
		add_meta_box( 	'pocketarticles-all', 
						__( 'Pocket articles settings', 'pocketarticles-collection' ), 
						array( $this, 'do_settings_box' ), $this->pagehook, 'main' );

	}

	function do_settings_box() {
		do_settings_sections('pocketarticles_settings_page'); 
	}
	
	/* 
		WordPress settings rendering functions
		
		ONLY NEEDED if we are using wordpress to render our controls (do_settings_sections)
	*/
																	  
																	  
	function main_section_text() {
		$options = get_option( 'pocketarticlescollection_settings' );
	}
	
	function render_consumer_key_text() { 
		?>
        <input id="consumer_key" style="width:50%;"  type="text" name="<?php echo $this->get_field_name( 'consumer_key' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'consumer_key' ) ); ?>" />	
		<?php 
	}
	
	function render_access_token_text() { 
		?>
        <input id="access_token" style="width:50%;"  type="text" name="<?php echo $this->get_field_name( 'access_token' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'access_token' ) ); ?>" />	
		<?php 
	}

	function render_favoritesonly_checkbox($args) {
		$id = 'pocketarticlescollection_settings['.$args['id'].']';
		?>
  		<input name="<?php echo $id;?>" type="checkbox" value="<?php echo $args['value'];?>" <?php echo isset($this->options[$args['id']]) ? 'checked' : '';?> /> <?php echo " {$args['text']}"; ?> <br/>
		<?php 
	}
	

} // end class
endif;



if (!class_exists("Pocket_Articles_Collection")) :

class Pocket_Articles_Collection {
	var $settings, $options_page;
	
	function __construct() {	

		if (is_admin()) {
			$this->settings = new Pocket_Articles_Collection_Settings();	
		}
		
		add_action('init', array($this,'init') );
		add_action('admin_init', array($this,'admin_init') );
		add_action('admin_menu', array($this,'admin_menu') );
	}

	

	/*
		Load language translation files (if any) for our plugin.
	*/
	function init() {
	}

	function admin_init() {
	}

	function admin_menu() {
	}

} // end class
endif;



// Initialize our plugin object.
global $pocket_articles_collection;
if (class_exists("Pocket_Articles_Collection") && !$pocket_articles_collection) {
    $pocket_articles_collection = new Pocket_Articles_Collection();	
}	
?>