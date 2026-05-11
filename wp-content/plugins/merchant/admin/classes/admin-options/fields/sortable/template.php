<?php
/**
 * Template: Sortable field.
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
<div class="merchant-sortable">
	<ul class="merchant-sortable-list ui-sortable">
		<?php foreach ( $value as $option_key ) :
			$option_val = $settings['options'][ $option_key ];
			if ( in_array( $option_key, $value, true ) ) : ?>
				<li class="merchant-sortable-item" data-value="<?php echo esc_attr( $option_key ); ?>">
					<i class='dashicons dashicons-menu'></i>
					<i class="dashicons dashicons-visibility visibility"></i>
					<?php echo esc_html( $option_val ); ?>
				</li>
			<?php endif;
		endforeach; ?>

		<?php foreach ( $settings['options'] as $option_key => $option_val ) :
			if ( ! in_array( $option_key, $value, true ) ) :
				$invisible = ' invisible'; ?>
				<li class="merchant-sortable-item<?php echo esc_attr( $invisible ); ?>" data-value="<?php echo esc_attr( $option_key ); ?>">
					<i class='dashicons dashicons-menu'></i>
					<i class="dashicons dashicons-visibility visibility"></i>
					<?php echo esc_html( $option_val ); ?>
				</li>
			<?php endif;
		endforeach; ?>
	</ul>

	<input class="merchant-sortable-input" type="hidden" name="merchant[<?php echo esc_attr( $settings['id'] ); ?>]" value="<?php echo esc_attr( (string) wp_json_encode( $value ) ); ?>"/>
</div>
<?php
