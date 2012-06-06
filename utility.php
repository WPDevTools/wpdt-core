<?php
/**
 * WPDT_Utility
 *
 * @version	0.1
 * @package wpdt-core
 */

if (!class_exists("WPDT_Utility")) :

define('WPDT_UTILITY_VERSION', 0.1);

/**
 * WPDT_Utility
 *
 * Theme and plugin utility methods and structures
 * 
 * @author	Christopher Frazier (chris@wpdevtools.com), David Sutoyo (david@wpdevtools.com)
 */
class WPDT_Utility
{

	/**
	 * A simple mobile detection script
	 *
	 * @returns string	the name of the mobile agent
	 **/
	public function is_mobile () {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$mobile_agents = array(
			'iPhone',
			'iPod',
			'incognito',
			'webmate',
			'Android',
			'BlackBerry',
			'webOS',
			's8000',
			'bada',
			'IEMobile/7.0',
			'Googlebot-Mobile',
			'AdsBot-Google',
		);
		
		foreach( $mobile_agents as $agent ) {
			$mobile_agent = preg_quote( $agent );
			if ( preg_match( "#$mobile_agent#i", $user_agent ) ) {
				return $agent;
			} else {
				return false;
			}
		}
	}

	/**
	 * Collects a set of posts, pages, media items or custom content items and merges the metadata into an array
	 *
	 * @author Christopher Frazier
	 * @param $query	An array 
	 * @return array	An array of content items
	 */
	public function get_content($query)
	{
		$items = get_posts($query);
		
		foreach ($items as &$item) {

			// Convert the item to an array for easier data manipulation
			$item = get_object_vars($item);
			
			// Get the all-important permalink
			$item['permalink'] = get_permalink($item['ID']);
			if ($item['post_type'] == 'attachment') { $item['permalink'] = $item['guid']; }
			
			// Pull in the author information
			$author = get_object_vars(get_userdata($item['post_author']));
			$item['user_email'] = $author['user_email'];
			$item['user_nicename'] = $author['user_nicename'];
			$item['display_name'] = $author['display_name'];
			$item['first_name'] = $author['first_name'];
			$item['last_name'] = $author['last_name'];
			$item['nickname'] = $author['nickname'];
			
			// Get the custom content for this data type
			$custom = get_post_custom($item['ID']);
			
			// Unserialize attachment custom meta
			if (array_key_exists('_wp_attachment_metadata', $custom)) {
				$custom = unserialize($custom['_wp_attachment_metadata'][0]);
			}
			
			$item = array_merge($item, $custom);

			foreach ($item as $item_key => $item_value) {
				// Process array data
				if (is_array($item_value)) {
					$item[$item_key] = join($item_value, ",");
				}
				
				// Remove private variables
				if (strpos($item_key, '_') === 0) { unset($item[$item_key]); }
			}
			
			// Merge the custom content meta into the main array

			// Pull in the thumbnail or an icon if the thumb isn't available
			$thumbnail = wp_get_attachment_image_src($item['ID'], 'thumbnail', true);
			$item['thumbnail_src'] = $thumbnail[0];
			$item['thumbnail_width'] = $thumbnail[1];
			$item['thumbnail_height'] = $thumbnail[2];
			
			// For security, remove some information
			unset(
				$item['post_password']
			);
									
		}

		return $items;

	}

} // WPDT_Utility


endif; // Class check