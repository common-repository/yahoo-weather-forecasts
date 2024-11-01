<?php
/**
	Widget - Yahoo Weather 1.0

	@package Zourbuth
	@subpackage Classes
	http://zourbuth.com/plugins/yahoo-weather
	http://developer.yahoo.com/weather/#terms
	For another improvement, you can drop email to zourbuth@gmail.com or visit http://zourbuth.com
	Parameter 		Description 								Example
	---------------------------------------------------------------------
	w 				woeid 										w=2502265
	u 				Units for temperature (case sensitive)
	f: Fahrenheit
	c: Celsius
	---------------------------------------------------------------------
	Example
	http://weather.yahooapis.com/forecastrss?w=2442047&u=c
*/



/**
 * SimplePie function for handling feed, extract the items, items images, items description,
 * items link and the bla and the bla and the bla. Making cache for best performance,
 * and content modification. Ok, so we start from here, please watch and be carefull mate!
 */
function yahoo_weather_simplepie( $woeid, $temperature = 'c', $show_credit = true ) {
	
	if( empty( $woeid ) )
		return __('Please provide the WOEID.', 'yahoo-weather');
		
	if( function_exists('fetch_feed') ) {
		
		require_once ( ABSPATH . WPINC . '/class-simplepie.php' );
		require_once ( trailingslashit( YAHOO_WEATHER_DIR ) . 'simplepie_yahoo_weather.inc');
		
		$feed = new SimplePie();
		
		/* specify the feed source */
		$param = 'http://weather.yahooapis.com/forecastrss?w=' . $woeid . '&u=' . $temperature;
		//$param = YAHOO_WEATHER_URL . 'forecastrss.xml';
		
		$feed->set_feed_url( $param );

		$feed->set_cache_location( YAHOO_WEATHER_DIR . 'cache');
		
		$feed->set_item_class('SimplePie_Item_YWeather');
		
		// Initialize the feed
		$feed->init();
		 
		// Since Y! Weather feeds only have one item, we'll base everything around that.
		$weather = $feed->get_item(0);

		/**
		 * if the feed is broken, or having problem to get the feed
		 * and maybe there is no item in the feed then print the 'content'
		 */
		
		foreach ( $weather->get_forecasts() as $forecast ) {
			$date	= $forecast->get_date('l, F jS');
			$low	= $forecast->get_low(); 
			$high	= $forecast->get_high();
			$label 	= $forecast->get_label();
		}
		
		// rising: state of the barometric pressure: steady (0), rising (1), or falling (2). (integer: 0, 1, 2)
		$rising = $weather->get_rising();
		if ( $rising == 0 )
			$rising = __('steady', 'yahoo-weather');
		elseif ( $rising == 1 )
			$rising = __('rising', 'yahoo-weather');
		elseif ( $rising == 2 )
			$rising = __('falling', 'yahoo-weather');
		
		$content  = '';
		$content .= '<p class="last-update">' . __('Current conditions for ', 'yahoo-weather') . $weather->get_city() . __(' as of ', 'yahoo-weather') . $weather->get_last_updated( $format = null ) . '</p>';
		
		$content .= '<div class="forecast-icon">';
			$content .= '<img src="http://l.yimg.com/a/i/us/nws/weather/gr/' . $weather->get_condition_code() . 'd.png" alt="' . $weather->get_condition() . '" />';
			$content .= '<p class="temperature">' . $weather->get_temperature() . '&deg;</p>';
			$content .= '<p class="high-low">High: ' . $high . '&deg; Low: ' . $low . '&deg;</p>';
		$content .= '</div>';
		
		$content .= '<p class="condition">' . $weather->get_condition() . '</p>';
		$content .= '<p class="y-temperature">' . __('Feels like: ', 'yahoo-weather') . $weather->get_temperature() . ' &deg;' . $weather->get_units_temp() . '</p>';
		$content .= '<p class="pressure">' . __('Barometer: ', 'yahoo-weather') . $weather->get_pressure() . ' ' . $weather->get_units_pressure() . ' and ' . $rising . '</p>';
		$content .= '<p class="pressure">' . __('Humidity: ', 'yahoo-weather') . $weather->get_humidity() . '%</p>';
		$content .= '<p class="pressure">' . __('Visibility: ', 'yahoo-weather') . $weather->get_visibility() . ' ' . $weather->get_units_distance() . '</p>';
		$content .= '<p class="pressure">' . __('Dewpoint: ', 'yahoo-weather') . $low . ' &deg;' . $weather->get_units_temp() . '</p>';
		$content .= '<p class="pressure">' . __('Wind: ', 'yahoo-weather') . $weather->get_wind_speed() . ' ' . $weather->get_units_speed() . '</p>';
		$content .= '<p class="pressure">' . __('Sunrise: ', 'yahoo-weather') . $weather->get_sunrise() . '</p>';
		$content .= '<p class="pressure">' . __('Sunset: ', 'yahoo-weather') . $weather->get_sunset() . '</p>';
		
		if ( $show_credit ) $credit = ' & <a href="http://zourbuth.com/plugins/yahoo-weather/">zourbuth.com</a>';
		$content .= '<span class="copyright">Powered by <a href="http://weather.yahoo.com/">Yahoo! Weather</a>' . $credit . '</span>';

		return $content;
	}
}




/**
 * function yahoo_weather_style()
 * function update($new_instance, $old_instance)
 * function form($instance)
 */
class Yahoo_Weather_Forecasts_Pl461n extends WP_Widget {
	
	var $textdomain;
	
	function __construct() {

		/* load the widget stylesheet for the widgets screen. */
		add_action( 'load-widgets.php', array(&$this, 'load_widgets') );
		
		/* ddd some informations to the widget */
		$widget_options = array('classname' => 'yahoo_weather_forecast', 'description' => __( 'Show the location weather using Yahoo! Weather.', $this->textdomain ) );

		/* set up the widget control options. */
		$control_options = array(
			'width' => 430,
			'height' => 350,
		);	
		
		add_action( 'wp_ajax_ywf_utils', array(&$this, 'ywf_utils') );
		
		/* create the widget with controls and options given above */
		$this->WP_Widget('yahoo-weather-forecast', 'Yahoo! Weather Forecasts', $widget_options, $control_options);
		
		if ( is_active_widget(false, false, $this->id_base) ) {
			wp_enqueue_style( 'yahoo-weather-forecast', YAHOO_WEATHER_URL . 'css/widget.css' );		
			wp_enqueue_script( 'jquery' );
			add_action( 'wp_head', array(&$this, 'yahoo_weather_costum_style_script')); // print the user costum style sheet
		}
	}

	/* push the widget stylesheet widget.css */
	function load_widgets() {
		wp_enqueue_style( 'yahoo_weatheroptions', YAHOO_WEATHER_URL . 'css/widget-admin.css', false, 0.7, 'screen' );
		wp_enqueue_script( 'yahoo_weatheroptions', YAHOO_WEATHER_URL . 'js/jquery.dialog.js');
		wp_localize_script( 'yahoo_weatheroptions', 'ywf', array(
			'nonce'	 => wp_create_nonce( 'ywf-nonce' ),  // generate a nonce for further checking below
			'action' => 'ywf_utils'
		));		
	}
	
	
	/**
	 * Outputs another item
	 * @since 2.0.2
	 */
	function ywf_utils() {
		// Check the nonce and if not isset the id, just die.
		$nonce = $_POST['nonce'];
		if ( ! wp_verify_nonce( $nonce, 'ywf-nonce' ) )
			die();

		$response = wp_remote_get( 'http://marketplace.envato.com/api/edge/collection:4204349.json' );
		$data = json_decode( wp_remote_retrieve_body( $response ) );
		
		$html = '';
		if( $data )
			foreach( $data->{'collection'} as $key => $value )
				$html .= '<a href="'.$value->url.'?ref=zourbuth"><img src="'.$value->thumbnail.'"></a>&nbsp;';

		echo $html;
		exit;
	}	

	/** print the user stylesheet and script **/
	function yahoo_weather_costum_style_script() {
	/* Print the custom script-style to the header wp_head */
		$all_widgets = $this->get_settings();
		foreach ($all_widgets as $key => $yw_setting){
			$widget_id = $this->id_base . '-' . $key;
			if( is_active_widget( false, $widget_id, $this->id_base ) ){
				if ( !empty( $yw_setting['customstylescript'] ) )
					echo $yw_setting['customstylescript'];
			}
		}		
	}
	
	/**
	 * Outputs the widget based on the arguments input through the widget controls.
	 * @since 0.6.0
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/** Set up the arguments **/
		$args = array(
			'woeid'				=> intval( $instance['woeid'] ),
			'temperature'		=> intval( $instance['temperature'] ),
			'show_credit'		=> !empty( $instance['show_credit'] ) ? true : false,
			'tabs'				=> $instance['tabs'],
			'intro_text' 		=> $instance['intro_text'],
			'outro_text' 		=> $instance['outro_text'],
			'customstylescript'	=> $instance['customstylescript']
		); 
		
		echo $before_widget;
		
		/* If a title was input by the user, display it. */
		if ( !empty( $instance['title'] ) )
			echo $before_title . apply_filters( 'widget_title',  $instance['title'], $instance, $this->id_base ) . $after_title;

		/* Print intro text if exist */
		if ( !empty( $instance['intro_text'] ) )
			echo '<p class="intro-text">' . $instance['intro_text'] . '</p>';
		
		echo '<div class="yahoo-weather">'; // open the block
			echo yahoo_weather_simplepie( $instance['woeid'], $instance['temperature'], $instance['show_credit'], true ); // processing the feed
		echo '</div>'; // close block
		
		/* Print outro text if exist */
		if ( !empty( $instance['outro_text'] ) )
			echo '<p class="outro_text">' . $instance['outro_text'] . '</p>';

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Set the instance to the new instance. */
		$instance = $new_instance;
		$instance['woeid'] 			= strip_tags( $new_instance['woeid'] );
		$instance['temperature'] 	= strip_tags( $new_instance['temperature'] );
		$instance['show_credit'] 	= ( isset( $new_instance['show_credit'] ) ? 1 : 0 );
		$instance['tabs'] 			= $new_instance['tabs'];
		$instance['intro_text'] 	= $new_instance['intro_text'];
		$instance['outro_text'] 	= $new_instance['outro_text'];
		$instance['customstylescript']	= $new_instance['customstylescript'];
		return $instance;
	}	

	/** Displays the widget control options in the Widgets admin screen. **/
	function form( $instance ) {

		/** Set up the default form values. **/
		$defaults = array(
			'title' 			=> 'Yahoo! Weather Forecasts',
			'woeid' 			=> '',
			'temperature' 		=> 'c',
			'show_credit'		=> true,
			'tabs'		=> array( 0 => true, 1 => false, 2 => false, 3 => false, 4 => false ),
			'intro_text' 		=> '',
			'outro_text' 		=> '',
			'customstylescript'	=> ''
		);

		/** Merge the user-selected arguments with the defaults. **/
		$instance = wp_parse_args( (array) $instance, $defaults );
		$temperature = strip_tags($instance['temperature']);
		$temperature_option = array( 'c' => __( 'Celcius', $this->textdomain ), 'f' => __( 'Fahreinheit', $this->textdomain ) );
		
		$tabs = array( 
			__( 'General', $this->textdomain ),  
			__( 'Customs', $this->textdomain ),
			__( 'Supports', $this->textdomain ),
		);				
	?>

		
		<div class="pluginName">Yahoo! Weather Forecasts<span class="pluginVersion"><?php echo YAHOO_WEATHER_VERSION; ?></span></div>
		
		<div id="tupro-<?php echo $this->id ; ?>" class="totalControls tabbable tabs-left">
			
			<ul class="nav nav-tabs">
				<?php foreach ($tabs as $key => $tab ) : ?>
					<li class="<?php echo $instance['tabs'][$key] ? 'active' : '' ; ?>"><?php echo $tab; ?><input type="hidden" name="<?php echo $this->get_field_name( 'tabs' ); ?>[]" value="<?php echo $instance['tabs'][$key]; ?>" /></li>
				<?php endforeach; ?>	
			</ul>
			
			<ul class="tab-content">
				<li class="tab-pane <?php if ( $instance['tabs'][0] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title', 'premium-widget-pack' ); ?></label>
							<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'woeid' ); ?>"><?php _e( 'WOEID', $this->textdomain ); ?></label>
							<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'woeid' ); ?>" name="<?php echo $this->get_field_name( 'woeid' ); ?>" value="<?php echo esc_attr( $instance['woeid'] ); ?>" />
							<span class="controlDesc">
								<?php _e( 'Example for New York City:', $this->textdomain ); ?> <tt>2459115</tt><br />
								<?php _e( 'Use <a href="http://goo.gl/lR6Nk">WOEID Generator</a>.', $this->textdomain ); ?>
							</span>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'temperature' ); ?>"><?php _e('Temperature', $this->textdomain ) ?></label> 
							<select class="smallfat" id="<?php echo $this->get_field_id( 'temperature' ); ?>" name="<?php echo $this->get_field_name( 'temperature' ); ?>">
								<?php foreach ( $temperature_option as $dataype => $option_label ) { ?>
									<option value="<?php echo esc_attr( $dataype ); ?>" <?php selected( $temperature, $dataype ); ?>><?php echo esc_html( $option_label ); ?></option>
								<?php } ?>
							</select>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'show_credit' ); ?>">
							<input class="checkbox" type="checkbox" <?php checked( $instance['show_credit'], true ); ?> id="<?php echo $this->get_field_id( 'show_credit' ); ?>" name="<?php echo $this->get_field_name( 'show_credit' ); ?>" /> <?php _e( 'Show credit?', $this->textdomain ); ?></label>
						</li>						
					</ul>
				</li>
				
								
				<li class="tab-pane <?php if ( $instance['tabs'][1] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<label for="<?php echo $this->get_field_id( 'intro_text' ); ?>"><?php _e('Intro Text', $this->textdomain ) ?></label>
							<span class="controlDesc"><?php _e( 'This option will display addtional text before the widget content and supports HTML.', $this->textdomain ); ?></span>
							<textarea name="<?php echo $this->get_field_name( 'intro_text' ); ?>" id="<?php echo $this->get_field_id( 'intro_text' ); ?>" rows="2" class="widefat"><?php echo htmlentities($instance['intro_text']); ?></textarea>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'outro_text' ); ?>"><?php _e('Outro Text', $this->textdomain ) ?></label>
							<span class="controlDesc"><?php _e( 'This option will display addtional text after widget and supports HTML.', $this->textdomain ); ?></span>
							<textarea name="<?php echo $this->get_field_name( 'outro_text' ); ?>" id="<?php echo $this->get_field_id( 'outro_text' ); ?>" rows="2" class="widefat"><?php echo htmlentities($instance['outro_text']); ?></textarea>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id('customstylescript'); ?>"><?php _e( 'Custom Script & Stylesheet', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Use this box for additional widget CSS style of custom javascript. Current widget selector: ', $this->textdomain ); ?><?php echo '<tt>#' . $this->id . '</tt>'; ?></span>
							<textarea style="font-size: 11px;" name="<?php echo $this->get_field_name( 'customstylescript' ); ?>" id="<?php echo $this->get_field_id( 'customstylescript' ); ?>" rows="4" class="widefat code"><?php echo htmlentities($instance['customstylescript']); ?></textarea>
						</li>				
					</ul>
				</li>
								
				<li class="tab-pane <?php if ( $instance['tabs'][2] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<p><strong>Our premium WordPress plugin</strong></p>
							<div id="<?php echo $this->id; ?>-ywf-utils"></div>
						</li>
						<li>
							Please give rating to <a href="https://wordpress.org/plugins/yahoo-weather-forecasts/">Yahoo! Weather Forecasts</a> 
							and visit <a href="http://zourbuth.com/archives/498/feedburner-email-subscription-wordpress-plugin/">zourbuth.com</a> for more informations.
						</li>						
						<li>
							<?php _e( 'Like my work? Please consider to ', $this->textdomain ); ?>
							<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=W6D3WAJTVKAFC" title="Donate"><?php _e( 'donate', $this->textdomain ); ?></a>.	
						</li>						
					</ul>
				</li>
			</ul>
		</div>
		<script type='text/javascript'>
			jQuery(document).ready(function($){
				$("#<?php echo $this->id; ?>-ywf-utils").empty().append("<p>Loading...</p>");
				$.post( ajaxurl, { action: ywf.action, nonce : ywf.nonce }, function(data){
					$("#<?php echo $this->id; ?>-ywf-utils").empty().append(data);
				});				
			});
		</script>			
	<?php
	}
}
?>