<?php
/**
 * Template: Date Time field.
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

// All options are documented here: https://air-datepicker.com/docs
$options = array(
	'dateFormat' => 'MM-dd-yyyy',
	'timepicker' => true,
	'timeFormat' => 'hh:mm AA',
	'minDate'    => 'today',
	'timeZone'   => wp_timezone_string(),
);

if ( isset( $settings['options'] ) ) {
	$settings['options'] = wp_parse_args( $settings['options'], $options );
} else {
	$settings['options'] = $options;
}
?>
<div class="merchant-datetime-field" data-options="<?php echo esc_attr( (string) wp_json_encode( $settings['options'] ) ); ?>">
	<input type="text" name="merchant[<?php echo esc_attr( $settings['id'] ); ?>]" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo isset( $settings['placeholder'] ) ? esc_attr( $settings['placeholder'] ) : ''; ?>"/>
</div>
