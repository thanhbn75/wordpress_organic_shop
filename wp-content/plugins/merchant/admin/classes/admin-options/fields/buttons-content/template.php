<?php
/**
 * Template: Buttons Content field.
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
<div class="merchant-buttons-content">
	<?php if ( ! empty( $settings['options'] ) ) :
		foreach ( $settings['options'] as $key => $option ) : ?>
			<label class="merchant-button-content-<?php echo esc_attr( $key ); ?>">
				<input
					type="radio"
					name="merchant[<?php echo esc_attr( $settings['id'] ); ?>]"
					value="<?php echo esc_attr( $key ); ?>"
					<?php checked( $value, $key, true ); ?>
				/>
				<span class="merchant-buttons-inner-content">
					<?php if ( $option['icon'] ) : ?>
						<img src="<?php echo esc_url( $option['icon'] ); ?>" alt="">
					<?php endif; ?>
					<span>
						<span><?php echo esc_html( $option['title'] ?? '' ); ?></span>
						<span><?php echo esc_html( $option['desc'] ?? '' ); ?></span>
					</span>
				</span>
			</label>
		<?php endforeach;
	endif; ?>
</div>
<?php
