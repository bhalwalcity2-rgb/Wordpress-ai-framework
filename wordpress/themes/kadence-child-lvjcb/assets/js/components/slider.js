'use strict';

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
		updateControls();

		// --- Autoplay ---
		var autoplayAttr = slider.getAttribute( 'data-lvjcb-slider-autoplay' );
		if ( ! autoplayAttr || reducedMotion.matches ) {
			return;
		}

		var interval  = parseInt( autoplayAttr, 10 ) || 4000;
		var timerId   = null;
		var paused    = false;

		function autoAdvance() {
			var maxScroll = track.scrollWidth - track.clientWidth;
			if ( track.scrollLeft >= maxScroll - 1 ) {
				track.scrollTo( { left: 0, behavior: reducedMotion.matches ? 'auto' : 'smooth' } );
			} else {
				scrollByPage( 1 );
			}
		}

		function startAutoplay() {
			if ( timerId || paused ) {
				return;
			}
			timerId = window.setInterval( autoAdvance, interval );
		}

		function stopAutoplay() {
			if ( timerId ) {
				window.clearInterval( timerId );
				timerId = null;
			}
		}

		function pause()  { paused = true;  stopAutoplay(); }
		function resume() { paused = false; startAutoplay(); }

		slider.addEventListener( 'mouseenter', pause );
		slider.addEventListener( 'mouseleave', resume );
		slider.addEventListener( 'focusin',    pause );
		slider.addEventListener( 'focusout',   resume );
		slider.addEventListener( 'touchstart', pause, { passive: true } );

		reducedMotion.addEventListener( 'change', function () {
			if ( reducedMotion.matches ) {
				stopAutoplay();
			} else if ( ! paused ) {
				startAutoplay();
			}
		} );

		startAutoplay();
	} );
} )();
