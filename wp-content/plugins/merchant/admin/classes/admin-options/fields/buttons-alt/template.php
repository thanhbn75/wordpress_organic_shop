<?php
/**
 * Template: Buttons Alt field.
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
<div class="merchant-buttons">
	<?php if ( ! empty( $settings['options'] ) ) :
		foreach ( $settings['options'] as $key => $option ) : ?>
			<label class="merchant-button-<?php echo esc_attr( $key ); ?>">
				<input type="radio" name="merchant[<?php echo esc_attr( $settings['id'] ); ?>]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $value, $key, true ); ?>/>
				<span><?php echo esc_html( $option ); ?></span>
			</label>
		<?php endforeach;
	endif; ?>
</div>
<?php
