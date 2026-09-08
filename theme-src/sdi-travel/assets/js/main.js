/**
 * SDI Travel — site chrome behavior: mobile nav toggle, and the desktop
 * primary nav's responsive "More ▾" overflow collapse (menu items that no
 * longer fit the header width move into a dropdown, in original order).
 */
( function () {
	'use strict';

	var MOBILE_BREAKPOINT = 900;

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

	function initNavOverflow() {
		var nav      = document.getElementById( 'sdi-primary-nav' );
		var menu     = nav ? nav.querySelector( '.sdi-primary-menu' ) : null;
		var more     = nav ? nav.querySelector( '.sdi-nav-more' ) : null;
		var moreMenu = more ? more.querySelector( '.sdi-nav-more__menu ul' ) : null;
		var moreBtn  = more ? more.querySelector( '.sdi-nav-more__toggle' ) : null;

		if ( ! nav || ! menu || ! more || ! moreMenu || ! moreBtn ) {
			return;
		}

		var collapsing = false;

		function collapse() {
			if ( collapsing ) {
				return;
			}
			collapsing = true;

			if ( window.innerWidth <= MOBILE_BREAKPOINT ) {
				collapsing = false;
				return;
			}

			// Restore every overflowed item back into the primary list, in order,
			// before re-measuring — otherwise the collapse only ever grows.
			while ( moreMenu.firstChild ) {
				menu.insertBefore( moreMenu.firstChild, more );
			}

			more.hidden = true;

			var available = nav.clientWidth;
			var items     = Array.prototype.slice.call( menu.children ).filter( function ( el ) {
				return el !== more;
			} );

			function totalWidth() {
				var w = more.hidden ? 0 : more.offsetWidth;
				items.forEach( function ( el ) {
					if ( el.parentNode === menu ) {
						w += el.offsetWidth;
					}
				} );
				return w;
			}

			var guard = items.length + 1;
			while ( totalWidth() > available && guard > 0 ) {
				var visible = items.filter( function ( el ) {
					return el.parentNode === menu;
				} );
				if ( visible.length <= 1 ) {
					break;
				}
				var last = visible[ visible.length - 1 ];
				more.hidden = false;
				moreMenu.insertBefore( last, moreMenu.firstChild );
				guard--;
			}

			collapsing = false;
		}

		moreBtn.addEventListener( 'click', function () {
			var isOpen = more.classList.toggle( 'is-open' );
			moreBtn.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
		} );

		document.addEventListener( 'click', function ( event ) {
			if ( more.classList.contains( 'is-open' ) && ! more.contains( event.target ) ) {
				more.classList.remove( 'is-open' );
				moreBtn.setAttribute( 'aria-expanded', 'false' );
			}
		} );

		var resizeTimer;
		window.addEventListener( 'resize', function () {
			window.clearTimeout( resizeTimer );
			resizeTimer = window.setTimeout( collapse, 120 );
		} );

		// Fonts loading late can change label widths after first paint.
		window.setTimeout( collapse, 50 );
		window.setTimeout( collapse, 400 );
		collapse();
	}

	function init() {
		initMobileNav();
		initNavOverflow();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
