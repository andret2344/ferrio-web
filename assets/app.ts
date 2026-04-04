import './styles/app.css';

function initTimezoneCookie(): void {
	const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
	if (tz) {
		document.cookie = `timezone=${tz};path=/;max-age=${365 * 24 * 60 * 60};SameSite=Lax;Secure`;
	}
}

function initThemeToggle(): void {
	const btn = document.getElementById('theme-toggle');
	if (!btn) {
		return;
	}

	btn.addEventListener('click', () => {
		const isDark = document.documentElement.classList.toggle('dark');
		localStorage.setItem('theme', isDark ? 'dark' : 'light');
	});
}

function initMobileMenu(): void {
	const menuBtn = document.getElementById('mobile-menu-btn');
	const menu = document.getElementById('mobile-menu');
	const overlay = document.getElementById('mobile-menu-overlay');
	const iconOpen = document.getElementById('menu-icon-open');
	const iconClose = document.getElementById('menu-icon-close');

	if (!menuBtn || !menu) {
		return;
	}

	function toggleMenu(): void {
		const isHidden = menu!.classList.contains('hidden');
		menu!.classList.toggle('hidden', !isHidden);
		iconOpen?.classList.toggle('hidden', isHidden);
		iconClose?.classList.toggle('hidden', !isHidden);
	}

	menuBtn.addEventListener('click', toggleMenu);
	overlay?.addEventListener('click', toggleMenu);
}

function initCarousel(): void {
	const carousel = document.getElementById('carousel');
	if (!carousel) {
		return;
	}

	const slides = carousel.querySelectorAll<HTMLElement>('.carousel-slide');
	const dotsContainer = document.getElementById('carousel-dots');
	const dots = carousel.querySelectorAll<HTMLElement>('.carousel-dot');
	const prevBtn = document.getElementById('carousel-prev');
	const nextBtn = document.getElementById('carousel-next');
	const count = slides.length;

	if (count <= 1) {
		return;
	}

	// Activate carousel mode: remove stacked spacing, show controls
	carousel.classList.remove('space-y-6');
	prevBtn?.classList.remove('hidden');
	prevBtn?.classList.add('flex');
	nextBtn?.classList.remove('hidden');
	nextBtn?.classList.add('flex');
	dotsContainer?.classList.remove('hidden');
	dotsContainer?.classList.add('flex');

	// Read initial index from hash
	let currentIndex: number = 0;
	const hash: string = globalThis.location.hash.slice(1);
	if (hash) {
		const parsed: number = Number.parseInt(hash, 10);
		if (!Number.isNaN(parsed) && parsed >= 0 && parsed < count) {
			currentIndex = parsed;
		}
	}

	const prefersReducedMotion = globalThis.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function showSlide(index: number): void {
		slides.forEach((slide, i) =>
			slide.classList.toggle('hidden', i !== index));

		const activeDotClasses = ['bg-gradient-to-r', 'from-amber-600', 'to-orange-600', 'w-6', 'sm:w-8'];
		const inactiveDotClasses = ['bg-gray-300', 'dark:bg-gray-600', 'group-hover:bg-gray-400', 'dark:group-hover:bg-gray-500'];

		dots.forEach((dot, i) => {
			const span = dot.querySelector('span');
			if (!span) {
				return;
			}
			if (i === index) {
				span.classList.remove(...inactiveDotClasses);
				span.classList.add(...activeDotClasses);
			} else {
				span.classList.remove(...activeDotClasses);
				span.classList.add(...inactiveDotClasses);
			}
		});

		currentIndex = index;
		if (!prefersReducedMotion) {
			history.replaceState(null, '', `${globalThis.location.pathname}#${index}`);
		}
	}

	// Show initial slide (hides others)
	showSlide(currentIndex);

	prevBtn?.addEventListener('click', () => showSlide(currentIndex === 0 ? count - 1 : currentIndex - 1));

	nextBtn?.addEventListener('click', () => showSlide(currentIndex === count - 1 ? 0 : currentIndex + 1));

	dots.forEach(dot =>
		dot.addEventListener('click', () => {
			const index = Number.parseInt(dot.dataset.index ?? '0', 10);
			showSlide(index);
		}));

	// Arrow key navigation — only when no input/textarea is focused
	document.addEventListener('keydown', (e: KeyboardEvent) => {
		const tag = (e.target as HTMLElement).tagName;
		if (['INPUT', 'TEXTAREA', 'SELECT'].includes(tag)) {
			return;
		}

		if (e.key === 'ArrowLeft') {
			showSlide(currentIndex === 0 ? count - 1 : currentIndex - 1);
		} else if (e.key === 'ArrowRight') {
			showSlide(currentIndex === count - 1 ? 0 : currentIndex + 1);
		}
	});
}

document.addEventListener('DOMContentLoaded', () => {
	initTimezoneCookie();
	initThemeToggle();
	initMobileMenu();
	initCarousel();
});
