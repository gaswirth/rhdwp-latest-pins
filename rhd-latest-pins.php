<?php
/**
 * Plugin Name: RHD Latest Pins
 * Description: Retrieves a user's latest Pinterest pins.
 * Author: Roundhouse Designs
 * Author URI: https://roundhouse-designs.com
 * Version: 0.1
 */
 
 // TODO Build shortcode


/* ==========================================================================
	Initialization
   ========================================================================== */

define( 'RHD_PIN_PLUGIN_DIR', plugin_dir_url(__FILE__) );


/* ==========================================================================
	Base Functionality
   ========================================================================== */

/**
 * rhd_latest_pin function.
 * 
 * @access public
 * @param mixed $pinterest_user
 * @param int $pin_count (default: 4)
 * @param bool $link_to_user (default: false)
 * @return void
 */
function rhd_latest_pin( $pinterest_user, $pin_count = 4, $link_to_user = false ) {
	$feed_url = "https://pinterest.com/{$pinterest_user}/feed.rss";
	
	$rss = fetch_feed( $feed_url );
	$latest_pins = $rss->get_items( 0, $pin_count );
	?>
		
	<?php if ( ! empty( $latest_pins ) ) : ?>
		<ul class="rhd-latest-pins">
			<?php foreach( $latest_pins as $pin ) : ?>
				<li class="rhd-pin">
					<?php
					$pin_desc = $pin->get_description();		
						
					preg_match('/<img[^>]+>/i', $pin_desc, $pin_image);
					
					if ( $link_to_user === false ) {
						preg_match('/<a href="(.*?)"/i', $pin_desc, $pin_link);
						$link = $pin_link[1];
					} else {
						$link = "https://pinterest.com/{$pinterest_user}";
					}
					?>
					
					<a href="<?php echo $link; ?>" rel="nofollow" target="_blank">
						<?php echo $pin_image[0]; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<?php
}


/* ==========================================================================
	Widget
   ========================================================================== */

class RHD_Latest_Pin extends WP_Widget {
	function __construct() {
		parent::__construct(
	 		'rhd_latest_pin', // Base ID
			__( 'RHD Latest Pin', 'rhd' ), // Name
			array( 'description' => __( 'Displays a user\'s latest Pinterest pins. That\'s it', 'rhd' ), ) // Args
		);
		
		add_action( 'wp_enqueue_scripts', array( $this, 'display_enqueue' ) );
	}
	
	public function display_enqueue() {
		wp_enqueue_style( 'rhd-pin-main', RHD_PIN_PLUGIN_DIR . 'css/rhd-pin-main.css' );
		wp_enqueue_script( 'rhd-crop-frame', RHD_PIN_PLUGIN_DIR . 'js/rhd-crop-frame.js', array( 'jquery' ), null, true );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['title'] = ( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['pinterest_user'] = ( $new_instance['pinterest_user'] ) ? esc_attr( $new_instance['pinterest_user'] ) : '';
		$instance['pin_count'] = ( $new_instance['pin_count'] ) ? absint( $new_instance['pin_count'] ) : null;
		
		$instance['link_to_user'] = ( isset( $new_instance['link_to_user'] ) & ! empty( $new_instance['link_to_user'] ) ) ? 1 : null;

		return $instance;
	}

	public function widget( $args, $instance ) {
		// outputs the content of the widget

		extract( $args );
		extract( $instance );
		
		$link_to_user = ( isset( $link_to_user ) ) ? true : false;

		$title = ( ! empty( $title ) ) ? apply_filters( 'widget_title', $title ) : '';

		echo $before_widget;

		if ( $title )
			echo $before_title . $title . $after_title;

		if ( ! $pinterest_user || $pinterest_user === '' )
			echo "No user selected.";
		else
			rhd_latest_pin( $pinterest_user, $pin_count, $link_to_user );

		echo $after_widget;
	}

	public function form( $instance ) {
		// outputs the options form on admin
		$args = array();

		$args['title'] = ! empty( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$args['pinterest_user'] = ! empty( $instance['pinterest_user'] ) ? esc_attr( $instance['pinterest_user'] ) : '';
		$args['pin_count'] = ! empty( $instance['pin_count'] ) ? absint( $instance['pin_count'] ) : null;
		$args['link_to_user'] = isset( $instance['link_to_user'] ) ? $instance['link_to_user'] : '';
		?>
		
		<p>
			<label for="<?php echo $this->get_field_name( 'title' ); ?>">Widget Title (optional) </label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $args['title']; ?>" >
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_name( 'pinterest_user' ); ?>">Pinterest username </label>
			<input id="<?php echo $this->get_field_id( 'pinterest_user' ); ?>" name="<?php echo $this->get_field_name( 'pinterest_user' ); ?>" type="text" value="<?php echo $args['pinterest_user']; ?>" >
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_name( 'pin_count' ); ?>">Pins to retrieve <em>(Default: 4)</em> </label>
			<input id="<?php echo $this->get_field_id( 'pin_count' ); ?>" name="<?php echo $this->get_field_name( 'pin_count' ); ?>" type="number" value="<?php echo $args['pin_count']; ?>" >
		</p>
		
		<p>
			<input id="<?php echo $this->get_field_id( 'link_to_user' ); ?>" name="<?php echo $this->get_field_name( 'link_to_user' ); ?>" type="checkbox" <?php checked( true, $args['link_to_user'] ) ?>>
			<label for="<?php echo $this->get_field_name( 'link_to_user' ); ?>">Link images to user profile</label>
		</p>
		
		<?php
	}
}


/**
 * Register RHD_latest_pin
 *
 * @access public
 * @return void
 */
function register_rhd_latest_pin_widget()
{
    register_widget( 'RHD_Latest_Pin' );
}
add_action( 'widgets_init', 'register_rhd_latest_pin_widget' );