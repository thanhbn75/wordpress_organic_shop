<?php
/**
 * Template: Select Size Chart field.
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

$options = array(
	'' => esc_html__( 'Default', 'merchant' ),
);

/** @var WP_Post[] $size_chart_posts */
$size_chart_posts = get_posts( array(
	'post_type'      => 'merchant_size_chart',
	'posts_per_page' => - 1,
	'post_status'    => 'publish',
) );

if ( ! empty( $size_chart_posts ) ) {
	foreach ( $size_chart_posts as $_post ) {
		$options[ $_post->ID ] = $_post->post_title;
	}
}

?>
	<select name="merchant[<?php echo esc_attr( $settings['id'] ); ?>]">
		<?php foreach ( $options as $key => $option ) : ?>
			<option value="<?php echo esc_attr( (string) $key ); ?>" <?php selected( $value, $key, true ); ?>><?php echo esc_html( $option ); ?></option>
		<?php endforeach; ?>
	</select>
<?php
