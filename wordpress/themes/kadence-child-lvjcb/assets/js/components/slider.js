'use strict';

/**
 * Global Slider/Carousel Component behavior.
 *
 * One implementation, applied to every [data-lvjcb-slider] instance on
 * the page — Recently Purchased Vehicles and Testimonials both use this
 * same script, never a per-section fork. Autoplay is deliberately not
 * implemented: the project's design direction is calm/restrained, and
 * the Master Workflow only requires autoplay "where appropriate" —
 * nothing in this project calls for it, so no autoplay machinery exists
 * to disable or guard against reduced-motion in the first place.
 */
( function () {

	var reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );

	document.querySelectorAll( '[data-lvjcb-slider]' ).forEach( function ( slider ) {

		var track = slider.querySelector( '.lvjcb-slider__track' );
		var prev  = slider.querySelector( '[data-lvjcb-slider-prev]' );
		var next  = slider.querySelector( '[data-lvjcb-slider-next]' );

		if ( ! track || ! prev || ! next ) {
			return;
		}

		function scrollByPage( direction ) {
			track.scrollBy( {
				left: track.clientWidth * direction,
				behavior: reducedMotion.matches ? 'auto' : 'smooth',
			} );
		}

		function updateControls() {
			var maxScroll = track.scrollWidth - track.clientWidth;
			prev.disabled = track.scrollLeft <= 1;
			next.disabled = track.scrollLeft >= maxScroll - 1;
		}

		prev.addEventListener( 'click', function () {
			scrollByPage( -1 );
		} );

		next.addEventListener( 'click', function () {
			scrollByPage( 1 );
		} );

		var scrollTimer;
		track.addEventListener( 'scroll', function () {
			window.clearTimeout( scrollTimer );
			scrollTimer = window.setTimeout( updateControls, 100 );
		} );

		window.addEventListener( 'resize', updateControls );

		// Handles "fewer items than visible slide count" and the
		// single-item case automatically: when content doesn't
		// overflow the track, maxScroll <= 0 and both controls
		// disable — no separate code path needed for that case.
		updateControls();
	} );
} )();
