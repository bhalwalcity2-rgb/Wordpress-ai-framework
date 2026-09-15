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

		var speed   = 1; // px per frame (~60px/s at 60fps)
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

		// Hover and focus pausing is a convenience. The toggle below is the
		// accessibility requirement: once a visitor stops the motion it stays
		// stopped, so moving the pointer away does not restart it.
		var stopped     = false;
		var pauseBtn    = slider.querySelector( '[data-lvjcb-slider-pause]' );
		var pauseLabel  = pauseBtn && pauseBtn.querySelector( '.lvjcb-slider__pause-label' );

		function softPause()  { if ( ! stopped ) { pause(); } }
		function softResume() { if ( ! stopped ) { resume(); } }

		if ( pauseBtn ) {
			pauseBtn.addEventListener( 'click', function () {
				stopped = ! stopped;
				pauseBtn.setAttribute( 'aria-pressed', stopped ? 'true' : 'false' );
				if ( pauseLabel ) {
					pauseLabel.textContent = stopped ? pauseBtn.getAttribute( 'data-label-play' ) || 'Play'
					                                 : pauseBtn.getAttribute( 'data-label-pause' ) || 'Pause';
				}
				if ( stopped ) { pause(); } else { resume(); }
			} );
		}

		slider.addEventListener( 'mouseenter', softPause );
		slider.addEventListener( 'mouseleave', softResume );
		slider.addEventListener( 'focusin',    softPause );
		slider.addEventListener( 'focusout',   softResume );
		slider.addEventListener( 'touchstart', softPause, { passive: true } );

		reducedMotion.addEventListener( 'change', function () {
			if ( reducedMotion.matches ) {
				stopAutoplay();
			} else if ( ! paused && ! stopped ) {
				startAutoplay();
			}
		} );

		// A slider scrolled off screen should not keep the compositor busy.
		if ( 'IntersectionObserver' in window ) {
			new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) { softResume(); } else { pause(); }
				} );
			}, { threshold: 0 } ).observe( slider );
		}

		startAutoplay();
	} );
} )();
