<?php
/**
 * Template: Upload field.
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

$settings  = wp_parse_args( $settings, array(
	'label' => esc_html__( 'Select Image', 'merchant' ),
) );
$drag_drop = $settings['drag_drop'] ?? false;

echo '<div class="merchant-upload-wrapper">';
if ( ! empty( $value ) ) :
	$image = wp_get_attachment_image_src( $value, 'thumbnail' );
	if ( ! empty( $image ) && ! empty( $image[0] ) ) :
		echo '<div class="merchant-upload-image">';
		echo '<i class="merchant-upload-remove dashicons dashicons-no-alt"></i>';
		printf( '<img src="%s" />', esc_url( $image[0] ) );
		echo '</div>';
	endif;
endif;
echo '</div>';
?>
<div class="merchant-upload-button-wrapper<?php echo esc_attr( $drag_drop ? ' merchant-upload-button-drag-drop' : '' ); ?>">
	<?php if ( $drag_drop ) : ?>
		<img src="<?php echo esc_url( esc_url( MERCHANT_URI . '/assets/images/upload-icon.svg' ) ); ?>" alt="<?php echo esc_attr__( 'Upload image', 'merchant' ); ?>"/>
	<?php endif; ?>
	<a href="#" class="merchant-upload-button"><?php echo esc_html( $settings['label'] ?? '' ); ?></a>
</div>
<input type="hidden" class="merchant-upload-input" name="merchant[<?php echo esc_attr( $settings['id'] ); ?>]" value="<?php echo esc_attr( $value ); ?>"/>
