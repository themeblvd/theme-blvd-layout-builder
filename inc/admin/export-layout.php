<?php echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . "\" ?>\n"; ?>
<layout>
	<info>
		<name><?php echo $layout->post_title; ?></name>
		<id><?php echo $layout->post_name; ?></id>
	</info>
	<data>
		<?php
		$count = 1;
		foreach ( $meta as $key => $value ) {

			if ( $count > 1 ) {
				echo "\t\t";
			}

			$value = get_post_meta( $layout_id, $key, true );
			$value = apply_filters( 'themeblvd_export_layout_meta', $value, $key );
			$value = base64_encode(maybe_serialize($value));

			echo "<meta>\n";
			echo "\t\t\t<key>$key</key>\n";
			echo "\t\t\t<value><![CDATA[$value]]></value>\n";
			echo "\t\t</meta>\n";

			$count++;
		}
		?>
	</data>
</layout>