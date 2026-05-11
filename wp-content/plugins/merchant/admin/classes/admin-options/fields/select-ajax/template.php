<?php
/**
 * Template: Select Ajax field.
 *
 * @var array<string, mixed>  $settings  Field configuration.
 * @var mixed  $value     Current saved value.
 * @var string $module_id Module ID.
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = wp_parse_args( $settings, array(
	'source' => 'post',
) );$ids = ( is_array( $value ) && ! empty( $value ) ) ? $value : (array) $value;

if ( isset( $settings['multiple'] ) ) {
	$multiple = $settings['multiple'] === true ? 'multiple' : '';
} else {
	$multiple = 'multiple';
}

$placeholder = $settings['placeholder'] ?? '';
?>
<select
	name="merchant[<?php echo esc_attr( $settings['id'] ); ?>]<?php echo esc_attr( $multiple ? '[]' : '' ); ?>"
	<?php echo esc_attr( $multiple ); ?>
	data-source="<?php echo esc_attr( $settings['source'] ); ?>"
	data-placeholder="<?php echo esc_attr( $placeholder ); ?>">
	<?php
	if ( ! empty( $ids ) ) {
		foreach ( $ids as $item_id ) {
			switch ( $settings['source'] ) {
				case 'post':
				case 'product':
					$post_obj = get_post( $item_id );
					if ( ! empty( $post_obj ) ) {
						echo '<option value="' . esc_attr( (string) $post_obj->ID ) . '" selected>' . esc_html( $post_obj->post_title ) . '</option>';
					}
					break;
				case 'user':
					$user = get_user_by( 'ID', $item_id );
					if ( $user ) {
						echo '<option value="' . esc_attr( (string) $user->ID ) . '" selected>' . esc_html( $user->display_name ) . '</option>';
					}
					break;
				case 'options':
					break;
			}
		}
	}
	if ( $settings['source'] === 'options' ) {
		$value = ! empty( $value ) ? $value : '';

		foreach ( $settings['options'] as $option ) {
			if ( isset( $option['options'] ) ) {
				echo '<optgroup label="' . esc_attr( $option['text'] ) . '">';
				foreach ( $option['options'] as $child_option ) {
					$selected_value = is_array( $value ) ? in_array( $child_option['id'], $value, true ) : $child_option['id'];
					echo '<option value="' . esc_attr( $child_option['id'] ) . '" ' . selected( $selected_value, is_array( $value ) ? true : $value, false ) . '>' . esc_html( $child_option['text'] ) . '</option>';
				}
				echo '</optgroup>';
			} else {
				$selected_value = is_array( $value ) ? in_array( $option['id'], $value, true ) : $option['id'];
				echo '<option value="' . esc_attr( $option['id'] ) . '" ' . selected( $selected_value, is_array( $value ) ? true : $value, false ) . '>' . esc_html( $option['text'] ) . '</option>';
			}
		}
	} ?>
</select>
<?php
