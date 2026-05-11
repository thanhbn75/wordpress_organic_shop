( function () {
	'use strict';

	var element = window.wp.element;
	var htmlEntities = window.wp.htmlEntities;
	var i18n = window.wp.i18n;
	var registry = window.wc.wcBlocksRegistry;
	var settings = window.wc.wcSettings;

	var getData = function () {
		return settings.getSetting( 'momo_sandbox_data', {} );
	};

	var decode = function ( value ) {
		return htmlEntities.decodeEntities( value || '' );
	};

	var Content = function () {
		return element.createElement( 'p', null, decode( getData().description ) );
	};

	registry.registerPaymentMethod( {
		name: 'momo_sandbox',
		label: element.createElement( 'span', null, decode( getData().title || i18n.__( 'MoMo Sandbox', 'wc-momo-sandbox' ) ) ),
		ariaLabel: i18n.__( 'MoMo sandbox payment method', 'wc-momo-sandbox' ),
		canMakePayment: function () {
			return true;
		},
		content: element.createElement( Content, null ),
		edit: element.createElement( Content, null ),
		supports: {
			features: getData().supports || [ 'products' ],
		},
	} );
}() );
