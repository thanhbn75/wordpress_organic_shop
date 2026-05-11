<?php
/**
 * Template: Flexible Content field — main container.
 *
 * Includes: row.php for each live row + hidden JS-clone template rows.
 *
 * @var array<string, mixed>  $settings  Field configuration.
 * @var mixed  $value     Current saved value.
 * @var string $module_id Module ID.
 * @var Merchant_Field_Flexible_Content $field     The field instance.
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


$values   = ( is_array( $value ) && ! empty( $value ) ) ? $value : array();
$empty    = empty( $values ) ? 'empty' : '';

$settings = wp_parse_args( $settings, array(
	'sorting'   => false,
	'style'     => 'default',
	'accordion' => false,
) );

$classes = array(
	'merchant-flexible-content-control',
	"{$settings['style']}-style",
);

$has_sorting = (bool) ( $settings['sorting'] ?? true );
if ( ! $has_sorting ) {
	$classes[] = 'disable-sorting';
}

$has_accordion = (bool) ( $settings['accordion'] ?? false );
if ( $has_accordion ) {
	$classes[] = 'has-accordion';
}

$has_duplicate = (bool) ( $settings['duplicate'] ?? false );
if ( $has_duplicate ) {
	$classes[] = 'has-duplicate';
}

$row_template = __DIR__ . '/row.php';
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
	data-id="<?php echo esc_attr( $settings['id'] ) ?>">

	<?php // Hidden clone templates for JS. ?>
	<div class="layouts" data-id="<?php echo esc_attr( $settings['id'] ) ?>">
		<?php
		foreach ( $settings['layouts'] as $layout_type => $layout_config ) :
			$layout     = $layout_type;
			$option_key = 0;
			$option     = $value;
			$is_clone   = true;

			include $row_template;
		endforeach;
		?>
	</div>

	<?php // Live rows. ?>
	<div class="merchant-flexible-content <?php echo esc_attr( $empty ); ?> sortable">
		<?php
		$active_index = $has_accordion ? $field->get_active_index( $values ) : 0;

		foreach ( $values as $option_key => $option ) :
			$layout        = $option['layout'] ?? '';
			$layout_config = $settings['layouts'][ $layout ] ?? array();
			$is_clone      = false;
			$deferred      = $field->is_row_deferred( (int) $option_key, $active_index, $has_accordion, $is_clone );

			include $row_template;
		endforeach;
		?>
	</div>

	<div class="customize-control-flexible-content-add-wrapper">
		<div class="customize-control-flexible-content-add-list">
			<?php foreach ( $settings['layouts'] as $layout_type => $layout ) : ?>
				<a href="#"
					class="customize-control-flexible-content-add"
					data-id="<?php echo esc_attr( $settings['id'] ) ?>"
					data-layout="<?php echo esc_attr( $layout_type ) ?>">
					<?php echo esc_attr( $layout['title'] ) ?>
				</a>
			<?php endforeach; ?>
		</div>
		<button class="button customize-control-flexible-content-add-button" type="button"><?php
			echo esc_html( $settings['button_label'] ); ?></button>
	</div>
</div>
