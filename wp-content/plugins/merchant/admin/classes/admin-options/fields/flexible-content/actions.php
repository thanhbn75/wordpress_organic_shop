<?php
/**
 * Template: Flexible Content — layout actions (duplicate, delete).
 *
 * @var array<string, mixed>  $settings      Field settings.
 * @var string $layout_type   The layout type identifier.
 * @var bool   $has_duplicate  Whether duplication is enabled.
 * @var bool   $has_accordion  Whether accordion is enabled.
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="layout-actions<?php echo $has_accordion ? esc_attr( ' layout-actions-has_accordion' ) : ' layout-actions-no_accordion'; ?>">
	<a href="#" class="layout-actions__toggle">
		<svg xmlns="http://www.w3.org/2000/svg" width="14" height="4" viewBox="0 0 14 4" fill="none">
			<path d="M1.5 0.763184C2.32843 0.763183 3 1.43476 3 2.26318C3 3.09161 2.32843 3.76318 1.5 3.76318C0.671573 3.76318 -2.93554e-08 3.09161 -6.55671e-08 2.26318C-1.01779e-07 1.43476 0.671573 0.763184 1.5 0.763184Z" fill="#4A4A4A"/>
			<path d="M7 0.763183C7.82843 0.763183 8.5 1.43476 8.5 2.26318C8.5 3.09161 7.82843 3.76318 7 3.76318C6.17157 3.76318 5.5 3.09161 5.5 2.26318C5.5 1.43476 6.17157 0.763183 7 0.763183Z" fill="#4A4A4A"/>
			<path d="M12.5 0.763183C13.3284 0.763183 14 1.43476 14 2.26318C14 3.09161 13.3284 3.76318 12.5 3.76318C11.6716 3.76318 11 3.09161 11 2.26318C11 1.43476 11.6716 0.763183 12.5 0.763183Z" fill="#4A4A4A"/>
		</svg>
	</a>
	<div class="layout-actions__inner" style="display: none;">
		<?php if ( $has_duplicate ) : ?>
			<a
				href="#"
				class="customize-control-flexible-content-duplicate"
				data-id="<?php echo esc_attr( $settings['id'] ); ?>"
				data-layout="<?php echo esc_attr( $layout_type ); ?>"
				title="<?php echo esc_attr__( 'Duplicate', 'merchant' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 19 22" fill="none">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M2 1.76318H13C13.2761 1.76318 13.5 1.98704 13.5 2.26318V13.2632C13.5 13.5393 13.2761 13.7632 13 13.7632H2C1.72386 13.7632 1.5 13.5393 1.5 13.2632V2.26318C1.5 1.98704 1.72386 1.76318 2 1.76318ZM0 2.26318C0 1.15861 0.895431 0.263184 2 0.263184H13C14.1046 0.263184 15 1.15861 15 2.26318V13.2632C15 14.3678 14.1046 15.2632 13 15.2632H2C0.89543 15.2632 0 14.3678 0 13.2632V2.26318ZM17 5.26318V16.0132C17 16.7035 16.4404 17.2632 15.75 17.2632H3V18.7632H15.75C17.2688 18.7632 18.5 17.532 18.5 16.0132V5.26318H17Z" fill="#757575"/>
				</svg>
				<span><?php echo esc_html__( 'Duplicate', 'merchant' ); ?></span>
			</a>
		<?php endif; ?>

		<a class="customize-control-flexible-content-delete" href="#">
			<svg xmlns="http://www.w3.org/2000/svg" width="14" height="17" viewBox="0 0 14 17" fill="none">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M7 0.263184C5.18578 0.263184 3.67247 1.55151 3.32502 3.26318H0V4.76318H1.2699L2.08782 13.7602C2.21659 15.1767 3.40421 16.2613 4.82652 16.2613H9.17366C10.596 16.2613 11.7836 15.1767 11.9124 13.7602L12.7303 4.76318H14V3.26318H10.675C10.3275 1.5515 8.81422 0.263184 7 0.263184ZM7 1.76318C6.02034 1.76318 5.18691 2.38929 4.87803 3.26318H9.12197C8.81309 2.38929 7.97966 1.76318 7 1.76318ZM11.2241 4.76318H2.77609L3.58166 13.6244C3.64019 14.2683 4.18002 14.7613 4.82652 14.7613H9.17366C9.82017 14.7613 10.36 14.2683 10.4185 13.6244L11.2241 4.76318Z" fill="#757575"/>
			</svg>
			<span><?php echo esc_html__( 'Delete', 'merchant' ); ?></span>
		</a>
	</div>
</div>
