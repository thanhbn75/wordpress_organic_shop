<?php
/**
 * Template: Image Picker field.
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

?>
<div class="merchant-image-picker">
	<?php if ( ! empty( $settings['options'] ) ) :
		foreach ( $settings['options'] as $key => $option ) : ?>
			<label>
				<input type="radio" name="merchant[<?php echo esc_attr( $settings['id'] ); ?>]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $value, $key, true ); ?>/>
				<?php if ( isset( $option['image'] ) ) { ?>
					<img src="<?php echo esc_url( $option['image'] ) ?>" alt="">
				<?php } ?>
				<?php if ( isset( $option['title'] ) ) { ?>
					<span class="tool-tip-text"><?php echo esc_html( $option['title'] ) ?></span>
				<?php } ?>
			</label>
		<?php endforeach;
	endif; ?>
</div>
<?php
