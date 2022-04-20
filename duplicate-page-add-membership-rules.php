<?php
/*
Plugin Name: Duplicate Page: Add Membership Rules
Description: Copies across membership plans when duplicating posts
Version: 0.1
Author: The team at PIE
Author URI: http://pie.co.de
License:     GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

/* PIE\DuplicatePageAddMembershipRules is free software: you can redistribute it and/or modify it under the terms of the GNU General License as published by the Free Software Foundation, either version 2 of the License, or any later version.

PIE\DuplicatePageAddMembershipRules is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General License for more details.

You should have received a copy of the GNU General License along with PIE\DuplicatePageAddMembershipRules. If not, see https://www.gnu.org/licenses/gpl-3.0.en.html */

namespace PIE\DuplicatePageAddMembershipRules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( is_admin() ) {
	/**
	 * Retrieve and update membership rules with new post ID
	 *
	 * @param  int $new_post_id
	 * @param  int $original_post
	 * @return void
	 */
	function duplicate_membership_plans( $new_post_id, $original_post ) {
		$wc_memberships_rules = $custom_memberships_rules = get_option('wc_memberships_rules');

		if ( ! is_array( $wc_memberships_rules ) ) return;

		foreach ( $wc_memberships_rules as &$membership_rule ) {
			if (
				! is_array( $membership_rule ) ||
				! array_key_exists( 'object_ids', $membership_rule ) ||
				! is_array( $membership_rule['object_ids'] ) ||
				! in_array( $original_post->ID, $membership_rule['object_ids'] )
			) continue;

			$membership_rule['object_ids'][] = $new_post_id;
			sort( $membership_rule['object_ids'] );
		}

		if ( $wc_memberships_rules !== $custom_memberships_rules ){
			set_transient( 'wc_memberships_rules_custom_backup_' . time(), $custom_memberships_rules, ( 60*60*24*42 ) );
			update_option( 'wc_memberships_rules_custom_most_recent_backup', $custom_memberships_rules );
			update_option( 'wc_memberships_rules', $wc_memberships_rules );
		}
	}
	add_action( 'dp_duplicate_page', __NAMESPACE__ . '\duplicate_membership_plans', 10, 2 );
	add_action( 'dp_duplicate_post', __NAMESPACE__ . '\duplicate_membership_plans', 10, 2 );
}
