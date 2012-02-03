<?php 

if (!class_exists('wpmarketing_feed')) {
	 
	class wpmarketing_feed {

		// Class initialization
		function wpmarketing_feed() {
			if (isset($_GET['show_wpmarketing_widget'])) {
				if ($_GET['show_wpmarketing_widget'] == "true") {
					update_option( 'show_wpmarketing_widget', 'noshow' );
				} else {
					update_option( 'show_wpmarketing_widget', 'show' );
				}
			} 
		
			// Add the widget to the dashboard
			add_action( 'wp_dashboard_setup', array(&$this, 'register_widget') );
			add_filter( 'wp_dashboard_widgets', array(&$this, 'add_widget') );
		}

		// Register this widget -- we use a hook/function to make the widget a dashboard-only widget
		function register_widget() {
			wp_register_sidebar_widget( 'wpmarketing_feed', __( 'WP Marketing Blog', 'wpmarketing' ), array(&$this, 'widget'), array( 'all_link' => 'http://wpmarketing.org/', 'feed_link' => 'http://wpmarketing.org/feed/', 'edit_link' => 'options.php' ) );
		}

		// Modifies the array of dashboard widgets and adds this plugin's
		function add_widget( $widgets ) {
			global $wp_registered_widgets;
			if ( !isset($wp_registered_widgets['wpmarketing_feed']) ) return $widgets;
			array_splice( $widgets, 2, 0, 'wpmarketing_feed' );
			return $widgets;
		}

		function widget($args = array()) {
			$show = get_option('show_wpmarketing_widget');
			if ($show != 'noshow') {
				if (is_array($args))
					extract( $args, EXTR_SKIP );
				echo $before_widget.$before_title.$widget_name.$after_title;
				echo '<a href="http://wpmarketing.org/"><img style="margin: 0 0 5px 5px;" src="http://wpmarketing.org/image/wpmarketing_rss.png" align="right" alt="WPMarketing.org"/></a>';
				include_once(ABSPATH . WPINC . '/rss.php');
				$rss = fetch_rss('http://wpmarketing.org/feed/');
				
				if ($rss) {
					$items = array_slice($rss->items, 0, 2);
					if (empty($items)) 
						echo 'No items';
					else {
						foreach ( $items as $item ) { ?>
						<p><a style="font-size: 12px; font-weight:bold; text-decoration:underline" href='<?php echo $item['link']; ?>' title='<?php echo $item['title']; ?>'><?php echo $item['title']; ?></a><br/> 
						<span style="font-size: 10px; color: #aaa;"><?php echo date('j F Y',strtotime($item['pubdate'])); ?></span> 
						<?php echo substr($item['summary'],0,170)  . '...'; ?></p>
						
						<?php }
					}
				}
				echo $after_widget;
			}
		}
	}

	// Start this plugin once all other plugins are fully loaded
	add_action( 'plugins_loaded', create_function( '', 'global $wpmarketing_feed; $wpmarketing_feed = new wpmarketing_feed();' ) );
}
