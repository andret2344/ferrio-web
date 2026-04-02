import './styles/app.css';

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
	const dots = carousel.querySelectorAll<HTMLElement>('.carousel-dot');
	const prevBtn = document.getElementById('carousel-prev');
	const nextBtn = document.getElementById('carousel-next');
	const count = slides.length;

	if (count <= 1) {
		return;
	}

	// Read initial index from hash
	let currentIndex: number = 0;
	const hash: string = globalThis.location.hash.slice(1);
	if (hash) {
		const parsed: number = Number.parseInt(hash, 10);
		if (!Number.isNaN(parsed) && parsed >= 0 && parsed < count) {
			currentIndex = parsed;
		}
	}

	function showSlide(index: number): void {
		slides.forEach((slide, i) => {
			slide.classList.toggle('hidden', i !== index);
		});

		dots.forEach((dot, i) => {
			if (i === index) {
				dot.className = 'carousel-dot w-2 h-2 rounded-full transition-all duration-200 cursor-pointer bg-gradient-to-r from-amber-600 to-orange-600 w-6 sm:w-8';
			} else {
				dot.className = 'carousel-dot w-2 h-2 rounded-full transition-all duration-200 cursor-pointer bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500';
			}
		});

		currentIndex = index;
		history.replaceState(null, '', `${globalThis.location.pathname}#${index}`);
	}

	// Show initial slide
	showSlide(currentIndex);

	prevBtn?.addEventListener('click', () => {
		showSlide(currentIndex === 0 ? count - 1 : currentIndex - 1);
	});

	nextBtn?.addEventListener('click', () => {
		showSlide(currentIndex === count - 1 ? 0 : currentIndex + 1);
	});

	dots.forEach((dot) => {
		dot.addEventListener('click', () => {
			const index = Number.parseInt(dot.dataset.index ?? '0', 10);
			showSlide(index);
		});
	});

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

function initLanguagePicker(): void {
	const picker = document.getElementById('language-picker');
	if (!picker) {
		return;
	}

	picker.querySelectorAll<HTMLButtonElement>('button[data-lang]').forEach((btn) => {
		btn.addEventListener('click', () => {
			const lang: string = btn.dataset.lang ?? 'en';
			document.cookie = `language=${lang};path=/;max-age=${365 * 24 * 60 * 60};SameSite=Lax`;
			localStorage.setItem('language', lang);
			globalThis.location.reload();
		});
	});
}

document.addEventListener('DOMContentLoaded', () => {
	initThemeToggle();
	initMobileMenu();
	initCarousel();
	initLanguagePicker();
});
