<?php
/*
Plugin Name: Side Matter
Plugin URI: http://wordpress.org/extend/plugins/side-matter/
Description: Turns footnotes into sidenotes, magically aligning each note in the sidebar next to its corresponding reference in the text.
Version: 1.4
Author: Christopher Setzer
Author URI: http://christophersetzer.com
License: GPLv2 or later
Text Domain: side-matter
Domain Path: /languages/
*/

/*
Copyright (C) 2014  Christopher Setzer

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

$side_matter = new Side_Matter;

class Side_Matter {

	public $version;

	public $defaults;
	public $options;
	
	public $notes;

	public function __construct() {

		add_action( 'admin_init', array( &$this, 'load_textdomain' ) ); // Load text domain 'side-matter' for localization
		add_action( 'admin_init', array( &$this, 'build_settings_section' ) ); // Assemble settings section
		add_action( 'admin_menu', array( &$this, 'admin_add_settings_page' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue' ) ); // Load script and CSS for options menu
		add_action( 'admin_print_footer_scripts', array( &$this, 'admin_add_quicktag' ) ); // Load quicktag script for post/page HTML editor
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( &$this, 'add_plugins_link' ) ); // Link options menu from Plugins screen

		add_shortcode( 'ref', array( &$this, 'shortcode' ) ); // Register shortcode
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue' ) ); // Load script and CSS; embed variables in script

		add_action( 'widgets_init', array( &$this, 'add_widget' ) ); // Register widget-loading function

		add_action( 'side_matter_list_notes', array( &$this, 'list_notes' ) ); // Register custom action 'side_matter_list_notes' for use in widget or elsewhere

		$this->defaults = array( // Set option defaults
			'user_colors' => array(
				'colors_enabled' => 0, // Default: 0
				'colors' => array(
					'figure_color' => '#ff543a', // Default: '#ff543a'
					'text_color' => '#777777' // Default: '#777777'
					)
				),
			'figure_style' => 'decimal', // Default: 'decimal'
			'is_responsive' => 0, // Default: 0
			'use_effects' => 0, // Default: 0
			'note_adjust' => 0, // Default: 0
			'pages_active' => array(
				'front' => 0, // Default: 0
				'home' => 0, // Default: 0
				'post' => 1, // Default: 1
				'page' => 1 // Default: 1
				),
			'html_class' => 'side-matter' // Default: 'side-matter'
			);
		$this->options = wp_parse_args( get_option( 'side_matter_options' ), $this->defaults ); // Get options from database and apply defaults in place of any that are not set
		$this->version = (string) '1.4'; // Current version of plugin

	}

	public function load_textdomain() {
		$dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		load_plugin_textdomain( 'side-matter', false, $dir );
	}

	public function admin_add_settings_page() {
		add_theme_page( __( 'Side Matter', 'side-matter' ), __( 'Side Matter', 'side-matter'), 'manage_options', 'side-matter', array( &$this, 'admin_build_settings_page' ) );
	}

	public function admin_build_settings_page() {
		?>
		<div class='wrap'>
			<form method='post' action='options.php'>
				<h2><?php _e( 'Side Matter', 'side-matter' ); ?></h2>
				<?php settings_fields( 'side-matter' ); ?>
				<?php do_settings_sections( 'side-matter' ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	public function build_settings_section() { // Assemble Side Matter options menu

		$section = 'side_matter_section';
		$page = 'side-matter';
		$callback = array( &$this, 'build_field' );
		$fields = array(
			'preview_field' => array(
				'id' => 'preview_field',
				'title' => __( 'Preview', 'side-matter' ),
				'label_for' => 'side-matter-preview-field'
				),
			'user_colors' => array(
				'id' => 'user_colors',
				'title' => __( 'Colors', 'side-matter' ),
				'label' => __( 'Use custom colors for Side Matter elements', 'side-matter' ),
				'label_for' => 'side-matter-colors-enabled',
				'colors' => array(
					'figure_color' => __( 'Reference numerals', 'side-matter' ),
					'text_color' => __( 'Note text', 'side-matter' )
					)
				),
			'figure_style' => array(
				'id' => 'figure_style',
				'title' => __( 'Display numeral figures as', 'side-matter' ),
				'label_for' => 'side-matter-figure-style',
				'styles' => array(
					'none' => __( 'None', 'side-matter' ),
					'decimal' => __( 'Decimal: 1, 2, 3, 4, 5, 6, …', 'side-matter' ),
					'lower-alpha' => __( 'Latin: a, b, c, d, e, f, …', 'side-matter' ),
					'lower-roman' => __( 'Roman: i, ii, iii, iv, v, vi, …', 'side-matter' ),
					'armenian' => __( 'Armenian: Ա, Բ, Գ, Դ, Ե, Զ, …', 'side-matter' ),
					'georgian' => __( 'Georgian: ა, ბ, გ, დ, ე, ვ, …', 'side-matter' ),
					'lower-greek' => __( 'Greek: α, β, γ, δ, ε, ζ, …', 'side-matter' ),
					'hebrew' => _x( 'Hebrew: ו ,ה ,ד ,ג ,ב ,א, …', 'Note RTL text reversal of Hebrew characters', 'side-matter' ), // Comma placement is due to RTL text reversal of Hebrew characters
					'hiragana' => __( 'Hiragana: あ, い, う, え, お, か, …', 'side-matter' ),
					'hiragana-iroha' => __( 'Hiragana—Iroha: い, ろ, は, に, ほ, へ, …' ),
					'katakana' => __( 'Katakana: ア, イ, ウ, エ, オ, カ, …', 'side-matter' ),
					'katakana-iroha' => __( 'Katakana—Iroha: イ, ロ, ハ, ニ, ホ, ヘ, …', 'side-matter' )
					)
				),
			'is_responsive' => array(
				'id' => 'is_responsive',
				'title' => __( 'Responsive Positioning', 'side-matter' ),
				'label' => __( 'Responsively position notes when viewport is resized or zoomed', 'side-matter' ),
				'label_for' => 'side-matter-is-responsive'
				),
			'use_effects' => array(
				'id' => 'use_effects',
				'title' => __( 'Fade Effects', 'side-matter' ),
				'label' => __( 'Use fade effects when displaying notes', 'side-matter' ),
				'label_for' => 'side-matter-use-effects'
				),
			'note_adjust' => array(
				'id' => 'note_adjust',
				'title' => __( 'Adjust vertical offset of notes by', 'side-matter' ),
				'label' => _x( 'px', 'Abbreviation for "pixels"', 'side-matter' ),
				'label_for' => 'side-matter-note-adjust'
				),
			'pages_active' => array(
				'id' => 'pages_active',
				'title' => __( 'Display Side Matter notes on', 'side-matter' ),
				'label_for' => 'side-matter-pages-active',
				'pages' => array(
					'front' => __( 'Front page', 'side-matter' ),
					'home' => __( 'Posts page', 'side-matter' ),
					'post' => __(  'Posts', 'side-matter' ),
					'page' => __( 'Pages', 'side-matter' )
					)
				)
			);

		add_settings_section( $section, '', array( &$this, 'build_section_heading' ), $page );
		foreach ( $fields as $option => $param ) { // Loop through settings field values and send them, one at a time, to build_field()
			extract( $fields[ $option ] );
			add_settings_field( $id, $title, $callback, $page, $section, $fields[ $option ] );
		}
		register_setting( $page, 'side_matter_options', array( &$this, 'validate_settings' ) );

	}

	public function build_section_heading() {
		return; // Don't build a section heading
	}

	public function build_field( $args ) { // Generate settings fields for each array $args passed from build_settings_section()

		extract( $args, EXTR_SKIP );

		$options = $this->options;
		$figure_style = $options['figure_style'];
		$options['preview_field'] = null;
		$state = $options[ $id ];

		switch ( $id ) {
			case 'preview_field':
				global $side_matter;
				$figure = $side_matter->get_figure( 1, $options['figure_style'] );
				?>
				<div class='side-matter-preview' tabindex='0'>
					<div class='side-matter-preview-content'>
						<div class='side-matter-preview-main'>
							Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor tincidunt ut labore et dolore nostrud.<span class='side-matter-preview-ref'><sup class='side-matter-preview-sup'><?php echo $side_matter->get_figure( 1, $figure_style ); ?></sup></span> Ut enim ad minim veniam, quis ullamco laboris nisi ut aliquip ex ea consequat. Cras ante lacus, libero et commodo sit magna aliqua.
						</div>
						<div class='side-matter-preview-side'>
							<ol class='side-matter-preview-list'>
								<li class='side-matter-preview-note' style='list-style-type: <?php echo $figure_style; ?>;'>
									<div class='side-matter-preview-text'>
										Velit esse cillum dolore eu fugiat nulla.
									</div>
								</li>
							</ol>
						</div>
						<div class='side-matter-preview-clear'>
							<!-- empty -->
						</div>
					</div>
				</div>
				<?php
				break;
			case 'user_colors':
				?>
				<fieldset>
					<label><input id='side-matter-colors-enabled' class='side-matter-colors-enabled' type='checkbox' name='side_matter_options[<?php echo $id; ?>][colors_enabled]' value='1' <?php checked( 1, $state['colors_enabled'] ); ?> /> <?php echo $label; ?></label>
					<ul class='side-matter-user-colors'>
					<?php
					foreach ( $colors as $color => $color_label ) {
						?>
						<li>
							<label><?php echo $color_label; ?>:<br>
							<input class='color-input side-matter-<?php echo str_replace( '_', '-', $color ); ?>' type='text' name='side_matter_options[<?php echo $id; ?>][colors][<?php echo $color; ?>]' maxlength='7' data-default-color='<?php echo $this->defaults['user_colors']['colors'][ $color ]; ?>' value='<?php echo $state['colors'][ $color ]; ?>' /></label>
						</li>
						<?php
					}
					?>
					</ul>
				</fieldset>
				<?php
				break;
			case 'figure_style':
				?>
				<select id='<?php echo $label_for; ?>' class='<?php echo $label_for; ?>' name='side_matter_options[<?php echo $id; ?>]'>
				<?php
				foreach ( $styles as $style => $style_title ) {
					?>
					<option value='<?php echo $style; ?>' <?php selected( $style, $state ); ?>><?php echo $style_title; ?></option>
					<?php
				}
				?>
				</select>
				<?php
				break;
			case 'note_adjust':
				$note_adjust_dir = ( $state >= 0 ) ? 'up' : 'down';
				?>
				<label><input id='<?php echo $label_for; ?>' class='<?php echo $label_for; ?> small-text' type='number' name='side_matter_options[<?php echo $id; ?>]' value='<?php echo abs( $state ); ?>' step='1' min='0' class='small-text' /> <?php echo $label; ?></label> <label class='note-adjust-dir'><input id='note_adjust_dir_up' type='radio' name='side_matter_options[note_adjust_dir]' value='up' <?php checked( 'up', $note_adjust_dir ); ?> /><?php _e( 'Up', 'side-matter' ); ?></label> <label class='note-adjust-dir'><input id='note_adjust_dir_down' type='radio' name='side_matter_options[note_adjust_dir]' value='down' <?php checked( 'down', $note_adjust_dir ); ?> /><?php _e( 'Down', 'side-matter' ); ?></label>
				<?php
				break;
			case 'is_responsive':
			case 'use_effects':
				?>
				<label><input id='<?php echo $label_for; ?>' class='<?php echo $label_for; ?>' type='checkbox' name='side_matter_options[<?php echo $id; ?>]' value='1' <?php checked( 1, $state ); ?> /> <?php echo $label; ?></label>
				<?php
				break;
			case 'pages_active':
				foreach ( $pages as $page => $page_label ) {
					?>
					<label><input class='side-matter-pages-active-<?php echo str_replace( '_', '-', $page ); ?>' type='checkbox' name='side_matter_options[<?php echo $id; ?>][<?php echo $page; ?>]' value='1' <?php checked( 1, $state[ $page ] ); ?>/> <?php echo $page_label; ?></label><br>
					<?php
				}
				break;
		}

	}

	public function validate_settings( $input ) { // Validate options menu input and return

		$defaults = $this->defaults;
		$options = $this->options;

		$options['user_colors']['colors_enabled'] = ( ! isset( $input['user_colors']['colors_enabled'] ) ) ? (int) 0 : (int) $input['user_colors']['colors_enabled'];
		foreach ( $defaults['user_colors']['colors'] as $color => $code ) { // Validate 3- or 6-character hex color code input
			$input_code = $input['user_colors']['colors'][ $color ];
			if ( $input_code[0] == '#' ) $input_code = substr( $input_code, 1 );
			$length = strlen( $input_code );
			$options['user_colors']['colors'][ $color ] = ( ( $length == 3 || $length == 6 ) && ctype_xdigit( $input_code ) ) ? '#' . $input_code : $defaults['user_colors']['colors'][ $color ]; // If not hex code of 3 or 6 characters, return default
		}
		$options['figure_style'] = ( ! isset( $input['figure_style'] ) ) ? $defaults['figure_style'] : $input['figure_style'];
		$options['is_responsive'] = ( ! isset( $input['is_responsive'] ) ) ? (int) 0 : (int) $input['is_responsive'];
		$options['use_effects'] = ( ! isset( $input['use_effects'] ) ) ? (int) 0 : (int) $input['use_effects'];

		if ( ! isset( $input['note_adjust'] ) ) {
			$options['note_adjust'] = (int) $defaults['note_adjust'];
		} else {
			$options['note_adjust'] = ( $input['note_adjust_dir'] == 'down' ) ? ( 0 - $input['note_adjust'] ) : abs( $input['note_adjust'] );
		}

		foreach ( $defaults['pages_active'] as $page => $setting ) {
			$options['pages_active'][ $page ] = ( ! isset( $input['pages_active'][ $page ] ) ) ? (int) 0 : (int) $input['pages_active'][ $page ];
		}

		if ( isset( $options['note_buffer'] ) ) unset( $options['note_buffer'] ); // Remove deprecated 'note_buffer'
		if ( isset( $options['html_class'] ) ) unset( $options['html_class'] ); // Don't write 'html_class' to DB

		return $options;

	}

	public function admin_add_quicktag() { // Add shortcode quicktag button to post editor menu
		if ( wp_script_is( 'quicktags' ) ) {
			?>
			<script type='text/javascript'>
				QTags.addButton( 'side_matter_ref', 'ref', '[ref]', '[/ref]', null, 'Side Matter note', 119 );
			</script>
			<?php
		}
	}

	public function admin_enqueue( $hook ) { // Enqueue script and stylesheet for Side Matter options menu
		global $wp_version;
		wp_enqueue_style( 'side-matter-admin', plugins_url( 'css/side-matter-admin.css', __FILE__ ), null, $this->version, 'screen' );
		if ( version_compare( $wp_version, 3.5, 'ge' ) ) { // Only enqueue Iris color picker style and dependency if WP version is 3.5 or higher
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'side-matter-admin-js', plugins_url( 'js/side-matter-admin.js', __FILE__ ), array( 'wp-color-picker' ), $this->version, true );
		} else {
			wp_enqueue_script( 'side-matter-admin-js', plugins_url( 'js/side-matter-admin.js', __FILE__ ), false, $this->version, true );
		}
	}

	public function add_plugins_link( $actions ) { // Link to options menu from Plugins screen
		$url = admin_url( 'themes.php?page=side-matter' );
		$settings_string = __( 'Settings', 'side-matter' );
		$array = array( 'settings' => "<a href='{$url}'>{$settings_string}</a>" );
		return array_merge( $array, $actions );
	}

	public function shortcode( $atts, $content = null ) { // Pass shortcode content to add_note(), and return formatted reference figure via format_ref()
		global $side_matter;
		if ( ! strpos( $content, '[ref]' ) === false ) { // Prevent chaos caused by [ref] within [ref]
			$content = str_replace( '[ref]', '&#91;ref&#93;', $content );
		}
		$content = do_shortcode( $content ); // Do a second pass for shortcodes within note text, like [video]
		if ( ! $side_matter->check_page() ) { // Only return figure on this page if permitted by user settings
			return '';
		} else {
			$note_id = $side_matter->add_note( $content ); // add_note() returns number of this note
			$ref = $side_matter->format_ref( $note_id ); // Get formatted reference figure from format_ref()
			return $ref;
		}
	}

	public function enqueue() { // Load JS and CSS components; embed user pref variables in script
		global $side_matter;
		if ( ! $side_matter->check_page() ) {
			return;
		} else {
			wp_enqueue_script( 'side-matter-js', plugins_url( 'js/side-matter.js', __FILE__ ), array( 'jquery' ), $this->version, true );
			wp_localize_script( 'side-matter-js', 'side_matter', array(
				'is_responsive' => __( $this->options['is_responsive'] ),
				'use_effects' => __( $this->options['use_effects'] ),
				'note_adjust' => __( $this->options['note_adjust'] ),
				'html_class' => __( $this->options['html_class'] )
				) );
			wp_enqueue_style( 'side-matter', plugins_url( 'css/side-matter.css', __FILE__ ), null, $this->version );
		}
	}

	public function add_widget() { // Register widget
		register_widget( 'Side_Matter_Widget' );
	}

	public function check_page() { // Check whether the current page is selected for sidenote display on plugin options menu
		extract( $this->options['pages_active'] );
		$check_pages = array(
			( is_front_page() && $front ),
			( is_home() && $home ),
			( is_single() && $post ),
			( is_page() && $page )
			);
		if ( in_array( 1, $check_pages ) ) {
			return true;
		} else {
			return false;
		}
	}

	public function has_notes() { // Return true if the current page contains notes
		return ! empty ( $this->notes );
	}

	public function get_figure( $note_id, $style ) { // Convert numeral to alternate format

		$figures = array(
			'armenian' => array( 'Ք' => 9000, 'Փ' => 8000, 'Ւ' => 7000, 'Ց' => 6000, 'Ր' => 5000, 'Տ' => 4000, 'Վ' => 3000, 'Ս' => 2000, 'Ռ' => 1000, 'Ջ' => 900, 'Պ' => 800, 'Չ' => 700, 'Ո' => 600, 'Շ' => 500, 'Ն' => 400, 'Յ' => 300, 'Մ' => 200, 'Ճ' => 100, 'Ղ' => 90, 'Ձ' => 80, 'Հ' => 70, 'Կ' => 60, 'Ծ' => 50, 'Խ' => 40, 'Լ' => 30, 'Ի' => 20, 'Ժ' => 10, 'Թ' => 9, 'Ը' => 8, 'Է' => 7, 'Զ' => 6, 'Ե' => 5, 'Դ' => 4, 'Գ' => 3, 'Բ' => 2, 'Ա' => 1 ),
			'georgian' => array( 'ჵ' => 10000, 'ჰ' => 9000, 'ჯ' => 8000, 'ჴ' => 7000, 'ხ' => 6000, 'ჭ' => 5000, 'წ' => 4000, 'ძ' => 3000, 'ც' => 2000, 'ჩ' => 1000, 'შ' => 900, 'ყ' => 800, 'ღ' => 700, 'ქ' => 600, 'ფ' => 500, 'ჳ' => 400, 'ტ' => 300, 'ს' => 200, 'რ' => 100, 'ჟ' => 90, 'პ' => 80, 'ო' => 70, 'ჲ' => 60, 'ნ' => 50, 'მ' => 40, 'ლ' => 30, 'კ' => 20, 'ი' => 10, 'თ' => 9, 'ჱ' => 8, 'ზ' => 7, 'ვ' => 6, 'ე' => 5, 'დ' => 4, 'გ' => 3, 'ბ' => 2, 'ა' => 1 ),
			'hebrew' => array( 'ת' => 400, 'ש' => 300, 'ר' => 200, 'ק' => 100, 'צ' => 90, 'פ' => 80, 'ע' => 70, 'ס' => 60, 'נ' => 50, 'מ' => 40, 'ל' => 30, 'כ' => 20, 'יט' => 19, 'יח' => 18, 'יז' => 17, 'טז' => 16, 'טו' => 15, 'י' => 10, 'ט' => 9, 'ח' => 8, 'ז' => 7, 'ו' => 6, 'ה' => 5, 'ד' => 4, 'ג' => 3, 'ב' => 2, 'א' => 1 ),
			'hiragana' => array( 'あ', 'い', 'う', 'え', 'お', 'か', 'き', 'く', 'け', 'こ', 'さ', 'し', 'す', 'せ', 'そ', 'た', 'ち', 'つ', 'て', 'と', 'な', 'に', 'ぬ', 'ね', 'の', 'は', 'ひ', 'ふ', 'へ', 'ほ', 'ま', 'み', 'む', 'め', 'も', 'や', 'ゆ', 'よ', 'ら', 'り', 'る', 'れ', 'ろ', 'わ', 'を', 'ん' ),
			'hiragana-iroha' => array( 'い', 'ろ', 'は', 'に', 'ほ', 'へ', 'と', 'ち', 'り', 'ぬ', 'る', 'を', 'わ', 'か', 'よ', 'た', 'れ', 'そ', 'つ', 'ね', 'な', 'ら', 'む', 'う', 'ゐ', 'の', 'お', 'く', 'や', 'ま', 'け', 'ふ', 'こ', 'え', 'て', 'あ', 'さ', 'き', 'ゆ', 'め', 'み', 'し', 'ゑ', 'ひ', 'も', 'せ', 'す', 'ん' ),
			'katakana' => array( 'ア', 'イ', 'ウ', 'エ', 'オ', 'カ', 'キ', 'ク', 'ケ', 'コ', 'サ', 'シ', 'ス', 'セ', 'ソ', 'タ', 'チ', 'ツ', 'テ', 'ト', 'ナ', 'ニ', 'ヌ', 'ネ', 'ノ', 'ハ', 'ヒ', 'フ', 'ヘ', 'ホ', 'マ', 'ミ', 'ム', 'メ', 'モ', 'ヤ', 'ユ', 'ヨ', 'ラ', 'リ', 'ル', 'レ', 'ロ', 'ワ', 'ヲ', 'ン' ),
			'katakana-iroha' => array( 'イ', 'ロ', 'ハ', 'ニ', 'ホ', 'ヘ', 'ト', 'チ', 'リ', 'ヌ', 'ル', 'ヲ', 'ワ', 'カ', 'ヨ', 'タ', 'レ', 'ソ', 'ツ', 'ネ', 'ナ', 'ラ', 'ム', 'ウ', 'ヰ', 'ノ', 'オ', 'ク', 'ヤ', 'マ', 'ケ', 'フ', 'コ', 'エ', 'テ', 'ア', 'サ', 'キ', 'ユ', 'メ', 'ミ', 'シ', 'ヱ', 'ヒ', 'モ', 'セ', 'ス', 'ン' ),
			'lower-alpha' => array( 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z' ),
			'lower-greek' => array( 'α', 'β', 'γ', 'δ', 'ε', 'ζ', 'η', 'θ', 'ι', 'κ', 'λ', 'μ', 'ν', 'ξ', 'ο', 'π', 'ρ', 'σ', 'τ', 'υ', 'φ', 'χ', 'ψ', 'ω' ),
			'lower-roman' => array( 'm' => 1000, 'cm' => 900, 'd' => 500, 'cd' => 400, 'c' => 100, 'xc' => 90, 'l' => 50, 'xl' => 40, 'x' => 10, 'ix' => 9, 'v' => 5, 'iv' => 4, 'i' => 1 )
			);

		switch ( $style ) {
			case 'none':
				return '';
				break;
			case 'armenian':
			case 'georgian':
			case 'hebrew':
			case 'lower-roman':
				return $this->get_additive( $note_id, $figures[ $style ] );
				break;
			case 'hiragana':
			case 'hiragana-iroha':
			case 'katakana':
			case 'katakana-iroha':
			case 'lower-alpha':
			case 'lower-greek':
				return $this->get_alpha( $note_id, $figures[ $style ] );
				break;
			case 'decimal':
			default:
				return $note_id;
				break;
		}

	}

	public function get_alpha( $note_id, $set ) { // Convert note number to alphabetic figure
		$count = count( $set );
		$value = $note_id--;
		$figure = '';
		while ( $value > 0 ) {
			$value--;
			$index = ( $value % $count );
			$figure = $set[ $index ] . $figure;
			$value = floor( $value / $count );
		}
		return $figure;
	}

	public function get_additive( $note_id, $set ) { // Convert note number to additive figure
		$value = $note_id--;
		$figure = '';
		while ( $value > 0 ) {
			$weight = reset( $set );
			$key = key( $set );
			unset( $set[ $key ] );
			$length = floor( $value / $weight );
			$figure .= str_repeat( $key, $length );
			$value -= ( $weight * $length );
		}
		return $figure;
	}

	public function add_note( $content ) { // Add current note's content to array $notes and return its number
		if ( count( $this->notes ) == 0 ) {
			$this->notes[1] = $content;
		} else {
			$this->notes[] = $content;
		}
		$note_id = count( $this->notes );
		return $note_id;
	}

	public function format_ref( $note_id ) { // Obtain reference figure from get_figure() and add HTML markup

		extract( $this->options );

		$ref_figure = ( $figure_style != 'decimal' ) ? $this->get_figure( $note_id, $figure_style ) : $note_id;

		$note_text = $this->notes[ $note_id ];

		$patterns = array( '/\n+/', '/\r+/', '/\s+/' );
		$title_text = preg_replace( $patterns, ' ', $note_text ); // Strip extraneous spaces and breaks from note text
		$title_text = strip_tags( $title_text ); // Strip HTML tags from note text
		$title_text = esc_attr( $title_text ); // Encodes <, >, &, ", and ' entities for use in title attribute
		$title_text = trim( $title_text ); // Trim whitespace from either end of note

		$figure_color_style = ( $user_colors['colors_enabled'] ) ? ' style="color: ' . $user_colors['colors']['figure_color'] . '"' : '';

		$open_sup = "<sup class='{$html_class} {$html_class}-sup' title='{$title_text}'>";
		$close_sup = '</sup>';
		if ( $figure_style == 'none' ) { // Don't print sup tag or figure if figure_style setting is 'none'
			$open_sup = $close_sup = $ref_figure = '';
		}

		$ref_output = "<a id='ref-{$note_id}' class='{$html_class} {$html_class}-ref' href='#note-{$note_id}'{$figure_color_style}>{$open_sup}{$ref_figure}{$close_sup}</a>";
		return $ref_output;

	}

	public function list_notes() { // Print HTML-formatted list of Side Matter notes

		static $lists_count = 0; // Keep static count of Side Matter lists in case of multiple widget instances

		if ( ! $this->check_page() || ! $this->has_notes() ) {
			return;
		} else {

			$options = $this->options;

			extract( $options );
			extract( $options['user_colors']['colors'] );

			$list_style_type = "list-style-type: {$figure_style};";
			$figure_color_style = "color: {$figure_color};";
			$text_color_style = "color: {$text_color};";
			$link_style_attr = ( $options['user_colors']['colors_enabled'] ) ? " style='{$figure_color_style}'" : '';

			$lists_count++; // Add this list to count
			$append_list = ( $lists_count > 1 ) ? '-' . $lists_count : ''; // If there are two (or more) Side Matter lists, append this string to list item IDs to prevent conflicts

			?>
			<ol class='<?php echo $html_class; ?> <?php echo $html_class; ?>-list' style='<?php echo $list_style_type; ?><?php if ( $options['user_colors']['colors_enabled'] ) echo ' ' . $figure_color_style; ?>'>
			<?php
			foreach ( $this->notes as $note_id => $note_text ) {
				$note_text = wpautop( $note_text ); // Re-enclose notes in paragraph tags to fix tags broken by shortcode
				?>
				<li id='note-<?php echo $note_id . $append_list; ?>' class='<?php echo $html_class; ?> <?php echo $html_class; ?>-note'>
					<div class='<?php echo $html_class; ?> <?php echo $html_class; ?>-text'<?php if ( $options['user_colors']['colors_enabled'] ) echo " style='{$text_color_style}'"; ?>>
						<?php echo $note_text; ?>
					</div>
				</li>
				<?php
			}
			?>
			</ol>
			<?php

		}

	}

}

class Side_Matter_Widget extends WP_Widget {

	public function __construct() { // Register widget name and description
		parent::__construct( 'side-matter', __( 'Side Matter', 'side-matter' ), array( 'classname' => 'widget_side_matter side-matter', 'description' => __( 'Display Side Matter notes in an ordered list', 'side-matter' ) ) );
	}

	public function widget( $args, $instance ) { // Widget output

		extract( $args );
		$title = apply_filters( 'widget_title', empty ( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		global $side_matter;
		if ( ! $side_matter->check_page() || ! $side_matter->has_notes() ) {
			return;
		} else {
			$class = $side_matter->options['html_class'];
			echo $before_widget;
			echo $before_title . $title . $after_title;
			do_action( 'side_matter_list_notes' );
			echo $after_widget;
		}

	}

	public function form( $instance ) { // Widget admin panel

		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = $instance['title'];

		?>
		<p><label for='<?php echo $this->get_field_id( 'title' ); ?>'><?php _e( 'Title:', 'side-matter' ); ?> <input class='widefat' id='<?php echo $this->get_field_id( 'title' ); ?>' name='<?php echo $this->get_field_name( 'title' ); ?>' type='text' value='<?php echo esc_attr( $title ); ?>' /></label></p>
		<?php

		$options_url = admin_url( 'themes.php?page=side-matter' );
		printf( __( '%sModify default settings using Side Matter\'s %soptions menu%s.%s', 'side-matter' ), '<p>', "<a href='{$options_url}'>", '</a>', '</p>' );

	}

}
