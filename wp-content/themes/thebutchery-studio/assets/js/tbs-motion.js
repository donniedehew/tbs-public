(() => {
	'use strict';

	const initTbsMotion = () => {
		const reducedMotion = window.matchMedia(
			'(prefers-reduced-motion: reduce)'
		).matches;

		const librariesReady =
			window.gsap &&
			window.ScrollTrigger &&
			window.SplitText &&
			window.Lenis;

		if (!librariesReady) {
			console.error(
				'[TBS Motion] No se han cargado todas las librerías.'
			);

			return;
		}

		gsap.registerPlugin(ScrollTrigger, SplitText);

		if (reducedMotion) {
			document.documentElement.classList.add(
				'tbs-motion-disabled'
			);

			console.info(
				'[TBS Motion] Animaciones reducidas por preferencia del usuario.'
			);

			return;
		}

		const lenis = new Lenis({
			smoothWheel: true,
			syncTouch: false,
		});

		lenis.on('scroll', ScrollTrigger.update);

		gsap.ticker.add((time) => {
			lenis.raf(time * 1000);
		});

		gsap.ticker.lagSmoothing(0);

		document.documentElement.classList.add(
			'tbs-motion-ready'
		);

		// Lo dejamos accesible para pruebas y ajustes posteriores.
		window.tbsMotion = {
			lenis,
		};

		console.info(
			'[TBS Motion] Lenis + GSAP funcionando.'
		);
	};

	if (document.readyState === 'loading') {
		document.addEventListener(
			'DOMContentLoaded',
			initTbsMotion,
			{ once: true }
		);
	} else {
		initTbsMotion();
	}
})();