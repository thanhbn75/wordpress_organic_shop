<?php
/**
 * Template: Dimensions field.
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

$settings      = wp_parse_args( $settings, array(
	'units'      => array(
		'px'  => 'px',
		'rem' => 'rem',
		'em'  => 'em',
		'vw'  => 'vw',
		'vh'  => 'vh',
		'%'   => '%',
	),
	'dimensions' => array(
		'top',
		'right',
		'bottom',
		'left',
	),
) );
$default_value = array(
	'unit' => reset( $settings['units'] ),
);
foreach ( $settings['dimensions'] as $dimension ) {
	$default_value[ $dimension ] = 0;
}
$value = wp_parse_args( $value, $default_value );
?>
<div class="merchant-module-page-settings-unit">
	<select name="merchant[<?php echo esc_attr( $settings['id'] ); ?>][unit]">
		<?php foreach ( $settings['units'] as $key => $option ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value['unit'], $key, true ); ?>><?php echo esc_html( $option ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
<div class="merchant-module-page-settings-dimensions">
	<?php foreach ( $settings['dimensions'] as $dimension ) : ?>
		<div>
			<input id="merchant-<?php echo esc_attr( $settings['id'] ); ?>-<?php echo esc_attr( $dimension ) ?>"
				type="number"
				name="merchant[<?php echo esc_attr( $settings['id'] ); ?>][<?php echo esc_attr( $dimension ) ?>]"
				value="<?php echo esc_attr( $value[ $dimension ] ); ?>"/>
			<label for="merchant-<?php echo esc_attr( $settings['id'] ); ?>-<?php echo esc_attr( $dimension ) ?>">
				<?php echo esc_html( ucfirst( $dimension ) ); ?>
			</label>
		</div>
	<?php endforeach; ?>
</div>
<?php
