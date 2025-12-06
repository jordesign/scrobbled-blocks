<?php
/**
 * Settings page for Scrobbled Blocks.
 *
 * @package ScrobbledBlocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings class.
 */
class Scrobbled_Blocks_Settings {

	/**
	 * Option name for storing settings.
	 */
	const OPTION_NAME = 'scrobbled_blocks_settings';

	/**
	 * Settings page slug.
	 */
	const PAGE_SLUG = 'scrobbled-blocks';

	/**
	 * Single instance of the class.
	 *
	 * @var Scrobbled_Blocks_Settings|null
	 */
	private static $instance = null;

	/**
	 * Get single instance of the class.
	 *
	 * @return Scrobbled_Blocks_Settings
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Add settings page to admin menu.
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'Scrobbled Blocks', 'scrobbled-blocks' ),
			__( 'Scrobbled Blocks', 'scrobbled-blocks' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings.
	 */
	public function register_settings() {
		register_setting(
			'scrobbled_blocks_settings_group',
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(),
			)
		);

		add_settings_section(
			'scrobbled_blocks_main_section',
			__( 'Last.fm API Settings', 'scrobbled-blocks' ),
			array( $this, 'render_section_description' ),
			self::PAGE_SLUG
		);

		add_settings_field(
			'lastfm_username',
			__( 'Last.fm Username', 'scrobbled-blocks' ),
			array( $this, 'render_username_field' ),
			self::PAGE_SLUG,
			'scrobbled_blocks_main_section'
		);

		add_settings_field(
			'lastfm_api_key',
			__( 'Last.fm API Key', 'scrobbled-blocks' ),
			array( $this, 'render_api_key_field' ),
			self::PAGE_SLUG,
			'scrobbled_blocks_main_section'
		);

		add_settings_field(
			'placeholder_image',
			__( 'Default Artwork Placeholder', 'scrobbled-blocks' ),
			array( $this, 'render_placeholder_field' ),
			self::PAGE_SLUG,
			'scrobbled_blocks_main_section'
		);
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( 'settings_page_' . self::PAGE_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_script(
			'scrobbled-blocks-admin',
			SCROBBLED_BLOCKS_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			SCROBBLED_BLOCKS_VERSION,
			true
		);

		wp_enqueue_style(
			'scrobbled-blocks-admin',
			SCROBBLED_BLOCKS_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			SCROBBLED_BLOCKS_VERSION
		);
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $input Input values.
	 * @return array Sanitized values.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		if ( isset( $input['lastfm_username'] ) ) {
			$sanitized['lastfm_username'] = sanitize_text_field( $input['lastfm_username'] );
		}

		if ( isset( $input['lastfm_api_key'] ) ) {
			$sanitized['lastfm_api_key'] = sanitize_text_field( $input['lastfm_api_key'] );
		}

		if ( isset( $input['placeholder_image'] ) ) {
			$sanitized['placeholder_image'] = absint( $input['placeholder_image'] );
		}

		// Validate API connection if credentials are provided.
		if ( ! empty( $sanitized['lastfm_username'] ) && ! empty( $sanitized['lastfm_api_key'] ) ) {
			$api    = Scrobbled_Blocks_API::get_instance();
			$result = $api->test_connection( $sanitized['lastfm_username'], $sanitized['lastfm_api_key'] );

			if ( is_wp_error( $result ) ) {
				add_settings_error(
					self::OPTION_NAME,
					'api_connection_failed',
					sprintf(
						/* translators: %s: Error message */
						__( 'Last.fm API connection test failed: %s', 'scrobbled-blocks' ),
						$result->get_error_message()
					),
					'error'
				);
			} else {
				add_settings_error(
					self::OPTION_NAME,
					'api_connection_success',
					__( 'Last.fm API connection successful!', 'scrobbled-blocks' ),
					'success'
				);
			}
		}

		return $sanitized;
	}

	/**
	 * Render section description.
	 */
	public function render_section_description() {
		printf(
			'<p>%s <a href="%s" target="_blank" rel="noopener noreferrer">%s</a></p>',
			esc_html__( 'Enter your Last.fm API credentials. You can get an API key from', 'scrobbled-blocks' ),
			esc_url( 'https://www.last.fm/api/account/create' ),
			esc_html__( 'Last.fm API Account', 'scrobbled-blocks' )
		);
	}

	/**
	 * Render username field.
	 */
	public function render_username_field() {
		$settings = $this->get_settings();
		$value    = isset( $settings['lastfm_username'] ) ? $settings['lastfm_username'] : '';

		printf(
			'<input type="text" id="lastfm_username" name="%s[lastfm_username]" value="%s" class="regular-text" />',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $value )
		);
		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Your Last.fm username to fetch scrobbles for.', 'scrobbled-blocks' )
		);
	}

	/**
	 * Render API key field.
	 */
	public function render_api_key_field() {
		$settings = $this->get_settings();
		$value    = isset( $settings['lastfm_api_key'] ) ? $settings['lastfm_api_key'] : '';

		printf(
			'<input type="password" id="lastfm_api_key" name="%s[lastfm_api_key]" value="%s" class="regular-text" autocomplete="off" />',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $value )
		);
		printf(
			'<button type="button" class="button button-secondary" id="toggle-api-key">%s</button>',
			esc_html__( 'Show', 'scrobbled-blocks' )
		);
		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Your Last.fm API key.', 'scrobbled-blocks' )
		);
	}

	/**
	 * Render placeholder image field.
	 */
	public function render_placeholder_field() {
		$settings       = $this->get_settings();
		$attachment_id  = isset( $settings['placeholder_image'] ) ? $settings['placeholder_image'] : 0;
		$attachment_url = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'thumbnail' ) : '';

		printf(
			'<input type="hidden" id="placeholder_image" name="%s[placeholder_image]" value="%s" />',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $attachment_id )
		);

		echo '<div id="placeholder-image-preview" style="margin-bottom: 10px;">';
		if ( $attachment_url ) {
			printf(
				'<img src="%s" alt="%s" style="max-width: 150px; height: auto;" />',
				esc_url( $attachment_url ),
				esc_attr__( 'Placeholder preview', 'scrobbled-blocks' )
			);
		}
		echo '</div>';

		printf(
			'<button type="button" class="button button-secondary" id="select-placeholder-image">%s</button> ',
			esc_html__( 'Select Image', 'scrobbled-blocks' )
		);
		printf(
			'<button type="button" class="button button-secondary" id="remove-placeholder-image" %s>%s</button>',
			$attachment_id ? '' : 'style="display:none;"',
			esc_html__( 'Remove Image', 'scrobbled-blocks' )
		);
		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Custom placeholder image when album art is unavailable. Plugin ships with a default.', 'scrobbled-blocks' )
		);
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'scrobbled_blocks_settings_group' );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Get settings.
	 *
	 * @return array Settings array.
	 */
	public function get_settings() {
		return get_option( self::OPTION_NAME, array() );
	}

	/**
	 * Get a specific setting.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value.
	 * @return mixed Setting value.
	 */
	public function get_setting( $key, $default = '' ) {
		$settings = $this->get_settings();
		return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}

	/**
	 * Get the Last.fm username.
	 *
	 * @return string Username or empty string.
	 */
	public function get_username() {
		return $this->get_setting( 'lastfm_username', '' );
	}

	/**
	 * Get the Last.fm API key.
	 *
	 * @return string API key or empty string.
	 */
	public function get_api_key() {
		return $this->get_setting( 'lastfm_api_key', '' );
	}

	/**
	 * Get the placeholder image URL.
	 *
	 * @return string Placeholder image URL.
	 */
	public function get_placeholder_url() {
		$attachment_id = $this->get_setting( 'placeholder_image', 0 );

		if ( $attachment_id ) {
			$url = wp_get_attachment_image_url( $attachment_id, 'medium' );
			if ( $url ) {
				return $url;
			}
		}

		// Return default placeholder.
		return SCROBBLED_BLOCKS_PLUGIN_URL . 'assets/images/placeholder.svg';
	}

	/**
	 * Check if the plugin is configured.
	 *
	 * @return bool True if configured, false otherwise.
	 */
	public function is_configured() {
		return ! empty( $this->get_username() ) && ! empty( $this->get_api_key() );
	}
}

// Initialize settings.
Scrobbled_Blocks_Settings::get_instance();
