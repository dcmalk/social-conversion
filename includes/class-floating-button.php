<?php
/**
 * Floating Button Class
 *
 * This is the class for handling the floating button methods.
 *
 * @package     RSM
 * @subpackage  Admin/Subscribers
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RSM_Floating_Button Class
 *
 * @since 1.0
 */
class RSM_Floating_Button {
	/**
	 * Floating button status
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $status;

	/**
	 * The FB list to trigger when clicked
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $list_id;

	/**
	 * The list segment being targeted
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $segment_id;

	/**
	 * The display position
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $position;

	/**
	 * Button text
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $text;

	/**
	 * Button text color
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $text_color;

	/**
	 * Button color
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $button_color;


	/**
	 * Initialize the floating button class.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->status       = rsm_get_option( 'float_status' );
		$this->list_id      = rsm_get_option( 'float_list_id' );
		$this->segment_id   = rsm_get_option( 'float_segment_id' );
		$this->text         = rsm_get_option( 'float_text' );
		$this->text_color   = rsm_get_option( 'float_color' );
		$this->button_color = rsm_get_option( 'float_button_color' );
		$this->position     = rsm_get_option( 'float_position' );

		add_action( 'wp', array( $this, 'load_floating_button' ) );
	}

	/**
	 * Loads the floating button styles and scripts.
	 *
	 * @since 1.0
	 * return void
	 */
	public function load_floating_button() {
		if ( $this->status ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'load_styles' ) );
			add_filter( 'wp_footer', array( $this, 'get_button_html' ) );
			add_action( 'wp_footer', array( $this, 'get_button_script' ) );
		}
	}

	/**
	 * Register and enqueue public-facing styles.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function load_styles() {
		global $is_IE, $is_edge;  // https://codex.wordpress.org/Global_Variables

		$slug    = RSM_PLUGIN_SLUG;
		$css_dir = RSM_PLUGIN_URL . 'assets/css/';

		// Enqueue floating button styles
		wp_enqueue_style( $slug . '-rsm-floating-style', $css_dir . 'rsm-floating.min.css' );
		if ( $is_IE || $is_edge ) wp_enqueue_style( $slug . '-rsm-floating-ie-style', $css_dir . 'rsm-floating-ie.css' );
	}

	/**
	 * Print the button HTML code.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function get_button_html() {
		switch ( trim( $this->position ) ) {
			case "left":
				$position = "rsm-floating-left";
				break;
			case "top-left":
				$position = "rsm-floating-top-left";
				break;
			case "bottom-right":
				$position = "rsm-floating-bottom-right";
				break;
			default:
				$position = "rsm-floating-right";
		}

		$text       = esc_html( $this->text );
		$color      = esc_attr( $this->text_color );
		$background = esc_attr( $this->button_color );

		$popup_html = '<button id="rsm-floating-button" type="button" class="btn ' . $position . ' animated zoomIn" style="background:' . $background . ';color:' . $color . ';">' . $text . '</button>';
		echo $popup_html;
	}

	/**
	 * Add click script to the footer.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function get_button_script() {
		$url       = home_url( '?rsm-action=opt&id=' . abs( $this->list_id ), 'http' );
		$url      .= empty( $this->segment_id ) ? '' : '&sid=' . $this->segment_id;
		$popup_url = add_query_arg( 'display', 'popup', $url );
		?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$('#rsm-floating-button').on('click', function (e) {
					e.preventDefault();
					window.open('<?php echo $popup_url; ?>', 'fbConnect', 'top=' + ((screen.height / 2) - 300) + ',left=' + ((screen.width / 2) - 298) + ',width=596,height=300');
					return false;
				});
			});
		</script>
		<?php
	}

}
