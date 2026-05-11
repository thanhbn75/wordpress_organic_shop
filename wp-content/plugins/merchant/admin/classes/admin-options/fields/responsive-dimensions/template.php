<?php
/**
 * Template: Responsive Dimensions field.
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

$settings       = wp_parse_args( $settings, array(
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
	'devices'    => array(
		'desktop' => 'dashicons-desktop',
		'tablet'  => 'dashicons-tablet',
		'mobile'  => 'dashicons-smartphone',
	),
) );
$default_values = array();
foreach ( $settings['devices'] as $device => $icon ) {
	$default_values[ $device ]['unit'] = reset( $settings['units'] );
	foreach ( $settings['dimensions'] as $dimension ) {
		$default_values[ $device ][ $dimension ] = 0;
	}
}
$value = wp_parse_args( $value, $default_values );
?>
<div class="merchant-module-page-settings-responsive">
	<ul class="merchant-module-page-settings-devices">
		<?php foreach ( $settings['devices'] as $device => $icon ) : ?>
			<li class="<?php echo esc_attr( $device ); ?>">
				<button type="button" class="preview-<?php echo esc_attr( $device ); ?> <?php echo $device === key( $settings['devices'] ) ? 'active' : '' ?>"
					data-device="<?php echo esc_attr( $device ); ?>">
					<i class="dashicons <?php echo esc_attr( $icon ); ?>"></i>
				</button>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php foreach ( $settings['devices'] as $device => $icon ) : ?>
		<div class="merchant-module-page-settings-device-container <?php echo $device === key( $settings['devices'] ) ? 'active' : '' ?>" data-device="<?php echo esc_attr( $device ); ?>">
			<div class="merchant-module-page-settings-unit">
				<select name="merchant[<?php echo esc_attr( $settings['id'] ); ?>][<?php echo esc_attr( $device ) ?>][unit]">
					<?php foreach ( $settings['units'] as $key => $option ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value[ esc_attr( $device ) ]['unit'], $key, true ); ?>><?php echo esc_html( $option ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="merchant-module-page-settings-dimensions">
				<?php foreach ( $settings['dimensions'] as $dimension ) : ?>
					<div>
						<input id="merchant-<?php echo esc_attr( $settings['id'] ); ?>-<?php echo esc_attr( $device ) ?>-<?php echo esc_attr( $dimension ) ?>"
							type="number"
							name="merchant[<?php echo esc_attr( $settings['id'] ); ?>][<?php echo esc_attr( $device ) ?>][<?php echo esc_attr( $dimension ) ?>]"
							value="<?php echo esc_attr( $value[ $device ][ $dimension ] ); ?>"/>
						<label for="merchant-<?php echo esc_attr( $settings['id'] ); ?>-<?php echo esc_attr( $device ) ?>-<?php echo esc_attr( $dimension ) ?>">
							<?php echo esc_html( ucfirst( $dimension ) ); ?>
						</label>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
<?php
