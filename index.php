<?php
 /*
	Plugin Name: Yahoo! Weather Forecasts
	Plugin URI: http://zourbuth.com/?p=238
	Description:  Show your favorite location weather, very easy to customize and support multi-widget and custom css stylesheet and script for adding the style and script per widget to the header easily.
	Version: 2.0.2
	Author: zourbuth
	Author URI: http://zourbuth.com/
	License: Under GPL2
*/
 
/*  
	Copyright 2013 zourbuth (email : zourbuth@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Launch the plugin. */
add_action( 'plugins_loaded', 'yahoo_weather_plugins_loaded' );

/* Initializes the plugin and it's features. */
function yahoo_weather_plugins_loaded() {

	// Set constant
	define( 'YAHOO_WEATHER_VERSION', '2.0.2' );
	define( 'YAHOO_WEATHER_DIR', plugin_dir_path( __FILE__ ) );
	define( 'YAHOO_WEATHER_URL', plugin_dir_url( __FILE__ ) );
		
	load_plugin_textdomain( 'yahoo!-weather-forecasts', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	
	/* Loads and registers the new widgets. */
	add_action( 'widgets_init', 'yahoo_weather_load_widgets' );
}

/* Register the extra widgets. Each widget is meant to replace or extend the current default  */
function yahoo_weather_load_widgets() {

	/* Load widget file. */
	require_once( trailingslashit( YAHOO_WEATHER_DIR ) . 'yahoo!-wheather.php' );

	/* Register widget. */
	register_widget( 'Yahoo_Weather_Forecasts_Pl461n' );
}

?>