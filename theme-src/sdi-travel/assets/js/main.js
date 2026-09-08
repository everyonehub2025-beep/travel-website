/**
 * SDI Travel — site chrome behavior (mobile nav toggle).
 */
( function () {
	'use strict';

	function initMobileNav() {
		var toggle = document.querySelector( '.sdi-header__toggle' );
		var nav    = document.querySelector( '.sdi-header__nav' );

		if ( ! toggle || ! nav ) {
			return;
		}

		toggle.addEventListener( 'click', function () {
			var isOpen = nav.classList.toggle( 'is-open' );
			toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initMobileNav );
	} else {
		initMobileNav();
	}
} )();
