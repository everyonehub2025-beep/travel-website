/**
 * SDI Trust Core — public behavior.
 *
 * Progressive enhancement only: the dashboard's tab links are real URLs
 * (?sdi_tab=...) that work with JavaScript disabled. This script just
 * avoids the full page reload when JS is available.
 */
( function () {
	'use strict';

	function initDashboardTabs() {
		var dashboards = document.querySelectorAll( '.sdi-dashboard' );

		dashboards.forEach( function ( dashboard ) {
			var tabs   = dashboard.querySelectorAll( '[data-sdi-tab]' );
			var panels = dashboard.querySelectorAll( '[data-sdi-panel]' );

			tabs.forEach( function ( tab ) {
				tab.addEventListener( 'click', function ( event ) {
					var target = tab.getAttribute( 'data-sdi-tab' );

					if ( ! target ) {
						return;
					}

					event.preventDefault();

					tabs.forEach( function ( t ) {
						var isActive = t === tab;
						t.classList.toggle( 'is-active', isActive );
						t.setAttribute( 'aria-selected', isActive ? 'true' : 'false' );
					} );

					panels.forEach( function ( panel ) {
						panel.classList.toggle( 'is-active', panel.getAttribute( 'data-sdi-panel' ) === target );
					} );

					dashboard.setAttribute( 'data-active-tab', target );

					if ( window.history && window.history.replaceState ) {
						var url = new URL( window.location.href );
						url.searchParams.set( 'sdi_tab', target );
						window.history.replaceState( null, '', url.toString() );
					}
				} );
			} );
		} );
	}

	function initCopyButtons() {
		document.querySelectorAll( '[data-sdi-copy]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				var text = button.getAttribute( 'data-sdi-copy' );
				var done = function () {
					var original = button.textContent;
					button.textContent = button.getAttribute( 'data-sdi-copied-label' ) || 'Copied!';
					window.setTimeout( function () {
						button.textContent = original;
					}, 2000 );
				};

				if ( navigator.clipboard && window.isSecureContext ) {
					navigator.clipboard.writeText( text ).then( done ).catch( function () {} );
				} else {
					var input = document.createElement( 'textarea' );
					input.value = text;
					input.style.position = 'fixed';
					input.style.left = '-9999px';
					document.body.appendChild( input );
					input.select();
					try {
						document.execCommand( 'copy' );
						done();
					} catch ( err ) {
						// Clipboard unavailable — the link text is still visible to select manually.
					}
					document.body.removeChild( input );
				}
			} );
		} );
	}

	function init() {
		initDashboardTabs();
		initCopyButtons();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
