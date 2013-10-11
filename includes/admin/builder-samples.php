<?php
/**
 * Get all sample layouts.
 *
 * @since 1.0.0
 *
 * @return array
 */

function themeblvd_get_sample_layouts() {
	$api = Theme_Blvd_Builder_API::get_instance();
	return $api->get_layouts();
}

/**
 * Sample layout previews when selecting one.
 *
 * @since 1.0.0
 *
 * @return string $output HTML to display
 */
function themeblvd_builder_sample_previews() {

	// Get sample layouts
	$samples = themeblvd_get_sample_layouts();

	// Construct output
	$output = '<div class="sample-layouts">';
	foreach( $samples as $sample ) {
		$output .= '<div id="sample-'.$sample['id'].'">';
		$output .= '<img src="'.$sample['preview'].'" />';
		if ( isset( $sample['credit'] ) ) {
			$output .= '<p class="note">'.$sample['credit'].'</p>';
		}
		$output .= '</div>';
	}
	$output .= '</div><!-- .sample-layouts (end) -->';

	return $output;
}