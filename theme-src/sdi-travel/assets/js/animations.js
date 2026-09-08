/**
 * SDI Travel — scroll-reveal for .sdi-animate elements.
 * Progressive enhancement: elements are visible by default in CSS unless
 * this script confirms IntersectionObserver support and un-reveals them
 * first, so content never hides behind a script that fails to load.
 */
( function () {
	'use strict';

	if ( ! ( 'IntersectionObserver' in window ) ) {
		return;
	}

	var targets = document.querySelectorAll( '.sdi-animate' );

	if ( ! targets.length ) {
		return;
	}

	var observer = new IntersectionObserver(
		function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-visible' );
					observer.unobserve( entry.target );
				}
			} );
		},
		{ threshold: 0.15, rootMargin: '0px 0px -40px 0px' }
	);

	targets.forEach( function ( target ) {
		observer.observe( target );
	} );
} )();
