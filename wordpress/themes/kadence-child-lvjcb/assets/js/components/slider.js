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

		// --- Continuous autoplay ---
		var autoplayAttr = slider.getAttribute( 'data-lvjcb-slider-autoplay' );
		if ( ! autoplayAttr || reducedMotion.matches ) {
			return;
		}

		var speed   = 0.5; // px per frame (~30px/s at 60fps)
		var rafId   = null;
		var paused  = false;

		function tick() {
			var maxScroll = track.scrollWidth - track.clientWidth;
			if ( maxScroll <= 0 ) {
				return;
			}
			track.scrollLeft += speed;
			if ( track.scrollLeft >= maxScroll ) {
				track.scrollLeft = 0;
			}
			updateControls();
			rafId = requestAnimationFrame( tick );
		}

		function startAutoplay() {
			if ( rafId || paused ) {
				return;
			}
			rafId = requestAnimationFrame( tick );
		}

		function stopAutoplay() {
			if ( rafId ) {
				cancelAnimationFrame( rafId );
				rafId = null;
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
