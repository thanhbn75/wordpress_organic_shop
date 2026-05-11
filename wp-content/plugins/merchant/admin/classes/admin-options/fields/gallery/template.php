<?php
/**
 * Template: Gallery field.
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
	'label' => esc_html__( 'Select Images', 'merchant' ),
) );$images   = ( ! empty( $value ) ) ? explode( ',', $value ) : array();

echo '<div class="merchant-gallery-images">';

if ( ! empty( $images ) ) :
	foreach ( $images as $image_id ) :
		$image = wp_get_attachment_image_src( (int) $image_id, 'thumbnail' );
		if ( ! empty( $image ) && ! empty( $image[0] ) ) :
			printf( '<div class="merchant-gallery-image" data-item-id="%s">', esc_attr( $image_id ) );
			echo '<i class="merchant-gallery-remove dashicons dashicons-no-alt"></i>';
			printf( '<img src="%s" />', esc_url( $image[0] ) );
			echo '</div>';
		endif;
	endforeach;
endif;

echo '</div>';
?>
<a href="#" class="merchant-gallery-button"><?php echo esc_html( $settings['label'] ); ?></a>
<input type="hidden" class="merchant-gallery-input" name="merchant[<?php echo esc_attr( $settings['id'] ); ?>]" value="<?php echo esc_attr( $value ); ?>"/>
<?php
