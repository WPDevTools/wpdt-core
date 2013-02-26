<?php
/**
 * WPDT_Options
 *
 * @version	0.1
 * @package wpdt-core
 */

if (!class_exists("WPDT_Options")) :

define('WPDT_OPTIONS_VERSION', 0.1);

/**
 * WPDT_Options
 *
 * Theme and Plugin Options Page Utilities
 * 
 * @author	Christopher Frazier (chris@wpdevtools.com), David Sutoyo (david@wpdevtools.com)
 */
class WPDT_Options
{

	/**
	 * Empty callback for add_settings_section
	 *
	 **/
	public function section() {
	}

	/**
	 * Generates the labels and fields for a checkbox
	 *
	 **/
	public function checkbox( $args ) {
		$settings = get_option( $args['theme_options'] );
	
		if (!isset($args['label_for'])) { die('Error: Please use an unique id for label_for'); }

		// jQuery does not like brackets
		$option_id = $args['theme_options'] . '_' . $args['label_for'];
		$option_name = $args['theme_options'] . '[' . $args['label_for'] . ']';

		echo '<input id="' . $option_id . '" type="checkbox" name="' . $option_name . '" value="true"';
		checked(TRUE, (bool) $settings[$args['label_for']]);
		echo ' />';

		if (isset($args['description'])) {
			echo '&nbsp;' . $args['description'];
		}
	}

	/**
	 * Generates the labels and fields for a colorpicker
	 *
	 **/
	public function colorpicker( $args ) {
		$settings = get_option( $args['theme_options'] );
	
		if (!isset($args['label_for'])) { die('Error: Please use an unique id for label_for'); }

		// jQuery does not like brackets
		$option_id = $args['theme_options'] . '_' . $args['label_for'];
		$option_name = $args['theme_options'] . '[' . $args['label_for'] . ']'; ?>
	
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#<?php echo $option_id; ?>').ColorPicker({
				onSubmit: function(hsb, hex, rgb) {
					$('#<?php echo $option_id; ?>').val('#'+hex);
				},
				onBeforeShow: function () {
					$(this).ColorPickerSetColor(this.value);
					return false;
				},
				onChange: function (hsb, hex, rgb) {
					$('#cp-<?php echo $option_id; ?> div').css({'backgroundColor':'#'+hex, 'backgroundImage': 'none', 'borderColor':'#'+hex});
					$('#cp-<?php echo $option_id; ?>').prevAll('input').attr('value', '#'+hex);
				}
			})
			.bind('keyup', function(){
				$(this).ColorPickerSetColor(this.value);
			});
		});
		</script>
		<input type="text" class="regular-text cp-input" id="<?php echo $option_id; ?>" name="<?php echo $option_name; ?>" value="<?php esc_attr_e( $settings[$args['label_for']] ); ?>" />
		<div id="cp-<?php echo $option_id; ?>" class="cp-box">
			<div style="background:<?php echo $settings[$args['label_for']]; ?>;border-color:<?php echo $settings[$args['label_for']]; ?>"> 
			</div>
		</div>
	
		<br /><span class="description cp-description"><?php echo $args['description']; ?></span>
		<?php
	}
	
	/**
	 * Generates the labels and fields for a select dropdown
	 *
	 **/
	
	public function select( $args ) {
		$settings = get_option( $args['theme_options'] );
	
		if (!isset($args['label_for'])) { die('Error: Please use an unique id for label_for'); }

		// jQuery does not like brackets
		$option_id = $args['theme_options'] . '_' . $args['label_for'];
		$option_name = $args['theme_options'] . '[' . $args['label_for'] . ']';

		echo '<select id="' . $option_id . '" name="' . $option_name . '">';
		echo "\n";
		foreach( $args['options'] as $key => $option) {
			echo '<option value="' . $key . '"';		    
			selected( $key == $settings[$args['label_for']] );
			echo '>' . $option . '</option>';
			echo "\n";
		}
		echo '</select>';
	
		if (isset($args['description'])) {
			echo '<br /><span class="description">' . $args['description'] . '</span>';
		}
	}

	/**
	 * Generates the labels and fields for a textarea
	 *
	 **/
	public function textarea( $args ) {
		$settings = get_option( $args['theme_options'] );
	
		if (!isset($args['label_for'])) { die('Error: Please use an unique id for label_for'); }
	
		// jQuery does not like brackets
		$option_id = $args['theme_options'] . '_' . $args['label_for'];
		$option_name = $args['theme_options'] . '[' . $args['label_for'] . ']';
	
		if ( isset( $settings[$args['label_for']] ) ) {
			echo '<textarea class="large-text-code" rows="5" cols="40" id="' . $option_id . '" name="' . $option_name . '">' . $settings[$args['label_for']] . '</textarea>';
		} else {
			echo '<textarea class="large-text-code" rows="5" cols="40" id="' . $option_id . '" name="' . $option_name . '"></textarea>';
		}
	
		if (isset($args['description'])) {
			echo '<br /><span class="description">' . $args['description'] . '</span>';
		}
	}

	/**
	 * Generates the labels and fields for a text field
	 *
	 **/
	public function textfield( $args ) {
		$settings = get_option( $args['theme_options'] );
	
		if (!isset($args['label_for'])) { die('Error: Please use an unique id for label_for'); }
	
		// jQuery does not like brackets
		$option_id = $args['theme_options'] . '_' . $args['label_for'];
		$option_name = $args['theme_options'] . '[' . $args['label_for'] . ']';

		if (isset($args['placeholder'])) {
			echo '<input type="text" class="regular-text" id="' . $option_id . '" name="' . $option_name . '" value="' . esc_attr( $settings[$args['label_for']] ) . '" placeholder="' . $args['placeholder'] . '" />';
		} else {
			echo '<input type="text" class="regular-text" id="' . $option_id . '" name="' . $option_name . '" value="' . esc_attr( $settings[$args['label_for']] ) . '" />';
		}
	
		if (isset($args['description'])) {
			echo '<span class="description">' . $args['description'] . '</span>';
		}
	}

	/**
	 * Generates the labels and fields for a media upload field
	 *
	 **/
	public function upload( $args ) {
		$settings = get_option( $args['theme_options'] );

		if (!isset($args['label_for'])) { die('Error: Please use an unique id for label_for'); }

		// jQuery does not like brackets
		$option_id = $args['theme_options'] . '_' . $args['label_for'];
		$option_name = $args['theme_options'] . '[' . $args['label_for'] . ']';
		
		if (isset($settings[$args['label_for']])) {
			echo '<img src="' . esc_attr( $settings[$args['label_for']] ) . '" /><br />';
		}
		if (!isset($args['uploader_title'])) {
			$args['uploader_title'] = 'Insert Media';
		}
		if (!isset($args['uploader_button_text'])) {
			$args['uploader_button_text'] = 'Select';
		}
		
		echo '<input type="text" class="wpdt-media-upload regular-text" id="' . $option_id . '" name="' . $option_name . '" value="' . esc_attr( $settings[$args['label_for']] ) . '" />';
		echo '<input id="' . $option_id . '-upload" data-uploader-title="' . $args['uploader_title'] . '" data-uploader-button-text="' . $args['uploader_button_text'] . '" class="wpdt-media-upload-button button" type="button" value="Upload Image" />';
	
		if (isset($args['description'])) {
			echo '<br />' . $args['description'];
		}
	}

	/**
	 * Sanitize a color represented in hexidecimal notation.
	 * From: http://wordpress.org/extend/plugins/options-framework/
	 *
	 * @param string Color in hexidecimal notation. "#" may or may not be prepended to the string.
	 * @param string The value that this function should return if it cannot be recognized as a color.
	 * @return string
	 *
	 */
	public function sanitize_hex( $hex, $default = '' ) {
		if ( WPDT_Options::validate_hex( $hex ) ) {
			return $hex;
		}
		return $default;
	}

	/**
	 * Is a given string a color formatted in hexidecimal notation?
	 * From: http://wordpress.org/extend/plugins/options-framework/
	 *
	 * @param string Color in hexidecimal notation. "#" may or may not be prepended to the string.
	 * @return bool
	 */
	public function validate_hex( $hex ) {
		$hex = trim( $hex );
		/* Strip recognized prefixes. */
		if ( 0 === strpos( $hex, '#' ) ) {
			$hex = substr( $hex, 1 );
		}
		elseif ( 0 === strpos( $hex, '%23' ) ) {
			$hex = substr( $hex, 3 );
		}
		/* Regex match. */
		if ( 0 === preg_match( '/^[0-9a-fA-F]{6}$/', $hex ) ) {
			return false;
		}
		else {
			return true;
		}
	}

} // WPDT_Options


endif; // Class check