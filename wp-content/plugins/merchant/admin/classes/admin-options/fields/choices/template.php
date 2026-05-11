<?php
/**
 * Template: Choices field.
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

$multiple = isset( $settings['multiple'] ) ? $settings['multiple'] : false;
?>
<div class="merchant-choices merchant-choices-<?php echo esc_attr( $settings['id'] ) ?>">
	<?php if ( ! empty( $settings['options'] ) ) :
		foreach ( $settings['options'] as $key => $option ) : ?>
			<label>
				<input type="<?php echo $multiple ? 'checkbox' : 'radio' ?>" name="merchant[<?php echo esc_attr( $settings['id'] ); ?>]<?php echo $multiple ? '[]' : '' ?>" value="<?php echo esc_attr( $key ); ?>" <?php
				$multiple && is_array( $value ) ? checked( in_array( $key, $value, true ), true ) : checked( $value, $key, true ); ?>/>
				<?php if ( ! empty( $option['svg'] ) ) : ?>
					<span class="merchant-svg">
						<?php echo wp_kses( Merchant_SVG_Icons::get_svg_icon( $option['svg'] ), merchant_kses_allowed_tags( array(), false ) ); ?>
						<?php if ( ! empty( $option['label'] ) ) : ?>
							<span class="merchant-tooltip"><?php echo esc_html( $option['label'] ); ?></span>
						<?php endif; ?>
					</span>
				<?php else : ?>
					<figure>
						<?php if ( ! empty( $option['image'] ) ) :
							$option_title = $option['title'] ?? '';
							?>
							<img src="<?php echo esc_url( sprintf( $option['image'], MERCHANT_URI . 'assets/images' ) ); ?>" alt="<?php echo esc_attr( $option_title ); ?>" title="<?php echo esc_attr( $option_title ); ?>"/>
						<?php else : ?>
							<img src="<?php echo esc_url( sprintf( $option, MERCHANT_URI . 'assets/images' ) ); ?>"/>
						<?php endif; ?>
						<?php if ( ! empty( $option['label'] ) ) : ?>
							<span class="merchant-tooltip"><?php echo esc_html( $option['label'] ); ?></span>
						<?php endif; ?>
					</figure>
				<?php endif; ?>
			</label>
		<?php endforeach;
	endif; ?>
</div>
<?php
