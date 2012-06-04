<?php
/**
 * WPDT_Shortcodes
 *
 * @version	0.1
 * @package wpdt-core
 */

if (!class_exists("WPDT_Shortcodes")) :

define('WPDT_SHORTCODES_VERSION', 0.1);

/**
 * WPDT_Shortcodes
 *
 * Tools to handle processing template tags in WordPress shortcode format
 * 
 * @author	Christopher Frazier (chris@wpdevtools.com), David Sutoyo (david@wpdevtools.com)
 */
class WPDT_Shortcodes
{

	/**
	 * The default date time format used when displaying times
	 *
	 * @var string
	 */
	public $datetime_format = 'F j, Y, g:i a';

	/**
	 * Handles replacement of template tags (ie. data related shortcodes)
	 *
	 * Example:
	 * [tag format="" length="" value="" type="" min="" max=""] Replacement text [/tag]
	 * 
	 * Valid parameters:
	 * 
	 * format	
	 * 	Date/time takes standard UNIX date format
	 * 	Numbers take integer number of decimal places (not implemented yet)
	 * 	Strings take case assignments: upper, lower, ucfirst, ucwords
	 * 
	 * length
	 * 	Number of characters to display.  Negative values truncate from the end of the resulting string
	 * 
	 * value
	 * 	If the value is found, replace the tag, otherwise remove this instance of the tag
	 * 	Should take wildcards for strings
	 * 	Should take ranges for date/time and numbers
	 * 
	 * type
	 * 	Used to force a data type.  For example, a string that is actually a date/time
	 *
	 * @author	Christopher Frazier
	 * @param	string	$content	A string with template tags to be replaced.
	 * @param	array	$data		An associative array with the data to be replaced.  Data types are taken into consideration during parsing.
	 * @param	array	$enclosure	OPTIONAL - A two value, indexed array of the characters to use as template tag enclosures.  By default, the parser uses square brackets ([ and ]).
	 * @return	string	The $content string with the template tags replaced.
	 */
	public function replace_template_tags ($content = '', $data = array(), $enclosure = array("[","]"))
	{
		// Make the enclosure strings safe for regex
		$enclosure = preg_replace("/([\[\}\]\{\}\<\>\(\)])/", "\\\\$1", $enclosure);
		
		$processed_content = $content;

		// Iterate through the data array
		foreach ($data as $key => $value) {

			// Establish the base data type
			if (strtotime($value)) {
				$type = 'datetime';
			}
			else if (is_numeric($value)) {
				$type = 'numeric';
				$value = floatval($value);
			}
			else if (is_string($value)) {
				$type = 'string';
			}
			else {
				$type = false;
			}

			// Grab all of the tags in the content that match the key
			$tag_search = "/" . $enclosure[0] . $key . "([^" . $enclosure[1] . "]*)(" . $enclosure[1] . "([^\Z]*)" . $enclosure[0] . "\/" . $key . $enclosure[1] . "|" . $enclosure[1] . ")/";
			preg_match_all($tag_search, $processed_content, $tags);
			
			// echo "<pre>"; print_r($tags); echo "</pre>";
			
			// Iterate through the tags found
			for ($i_tags = 0; $i_tags < count($tags[0]); $i_tags++) {
			
				// Set up an associative array with the values found
				$tag = array(
					'name' => $key,
					'id' => $i_tags,
					'full' => $tags[0][$i_tags],
					'param_full' => $tags[1][$i_tags],
					'content' => $tags[3][$i_tags],
					'params' => array(
						'format' => null,
						'length' => null,
						'max' => null,
						'min' => null,
						'value' => null,
						'type' => $type,
					)
				);

				// Grab the explicit parameters for this tag
				$param_search = "/(([\S]*)=\"([^\"]*)\")/";
				preg_match_all($param_search, $tags[1][$i_tags], $params_regex);

				for ($i_params = 0; $i_params < count($params_regex[0]); $i_params++) {
					$tag['params'][$params_regex[2][$i_params]] = $params_regex[3][$i_params];
				}

				// echo "<pre>"; print_r($tag); echo "</pre>";

				// Find out if the tag value matches the data value
				$matches = true;

				switch ($tag['params']['type']) {
					case 'datetime' :
						// Check for date time, value match, min and max
						if ($tag['params']['value'] != null && !strtotime($tag['params']['value'])) { $matches = false; }
						if ($tag['params']['value'] != null && strtotime($tag['params']['value']) != strtotime($value)) { $matches = false; }
						if ($tag['params']['min'] != null && strtotime($tag['params']['min']) > strtotime($value)) { $matches = false; }
						if ($tag['params']['max'] != null && strtotime($tag['params']['max']) < strtotime($value)) { $matches = false; }
						break;
					case 'numeric' :
						// Check for number, value match, min and max
						if ($tag['params']['value'] != null && !is_numeric($tag['params']['value'])) { $matches = false; }
						if ($tag['params']['value'] != null && floatval($tag['params']['value']) != $value) { $matches = false; }
						if ($tag['params']['min'] != null && floatval($tag['params']['min']) > $value) { $matches = false; }
						if ($tag['params']['max'] != null && floatval($tag['params']['max']) < $value) { $matches = false; }
						break;
					case 'string' :
						if ($tag['params']['value'] != null && !preg_match("/" . preg_quote($tag['params']['value']) . "/", $value)) { $matches = false; }
						break;
				}

				// If the value matches, then process the content
				if ($matches) {

					// Check for tag content
					if ($tag['content']) { $replacement_value = $tag['content']; } else { $replacement_value = $value; }

					// Replace tag with the data value
					if ($tag['params']['format'] != null) {
						switch ($tag['params']['type']) {
							case 'datetime' :
								// Handle datetime formatting
								$replacement_value = date($tag['params']['format'], strtotime($replacement_value));
								break;
							case 'string' :
								// Handle case formatting
								switch (strtolower($tag['params']['format'])) {
									case 'upper':
										$replacement_value = strtoupper($replacement_value);
										break;
									case 'lower':
										$replacement_value = strtolower($replacement_value);
										break;
									case 'ucfirst':
										$replacement_value = ucfirst($replacement_value);
										break;
									case 'ucwords':
										$replacement_value = ucwords($replacement_value);
										break;
								}
								break;
						}
					}

				} else {

					// Replace the tag with an empty string
					$replacement_value = '';

				}

				// Truncate the string if length is set
				if ($tag['params']['length'] != null) { $replacement_value = substr($replacement_value, 0, intval($tag['params']['length'])); }

				// Replace the tag with the replacement value
				$processed_content = str_replace($tag['full'], $replacement_value, $processed_content);
			}

		}
		
		return $processed_content;
	}

} // WPDT_Shortcodes


endif; // Class check