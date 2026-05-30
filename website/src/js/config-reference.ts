import $ from 'jquery';
import ko from '@tko/build.knockout';
import {MainMenuViewModel} from './MainMenuViewModel';

const activeClass = 'bg-blue-500 text-white';
const inactiveClass = 'hover:bg-gray-50';

function initScrollSpy(): void {
	const sidebar = document.querySelector('.sidebar');
	if (!sidebar) {
		return;
	}

	const anchorLinks = sidebar.querySelectorAll<HTMLAnchorElement>('a[href^="#"]');
	if (anchorLinks.length === 0) {
		return;
	}

	// Build a map of section id -> sidebar link
	const linkMap = new Map<string, HTMLAnchorElement>();
	Array.from(anchorLinks).forEach((link) => {
		const id = link.getAttribute('href')!.slice(1);
		if (id) {
			linkMap.set(id, link);
		}
	});

	// Collect all h2 elements that have an id matching a sidebar link
	const sections: HTMLElement[] = [];
	linkMap.forEach((_link, id) => {
		const el = document.getElementById(id);
		if (el) {
			sections.push(el);
		}
	});

	if (sections.length === 0) {
		return;
	}

	let currentActive: string | null = null;
	let paused = false;
	let resumeTimer: number | null = null;

	function setActive(id: string | null): void {
		if (id === currentActive) {
			return;
		}

		// Remove active from previous
		if (currentActive && linkMap.has(currentActive)) {
			const prev = linkMap.get(currentActive)!;
			activeClass.split(' ').forEach((cls) => prev.classList.remove(cls));
			inactiveClass.split(' ').forEach((cls) => prev.classList.add(cls));
		}

		// Add active to new
		if (id && linkMap.has(id)) {
			const next = linkMap.get(id)!;
			inactiveClass.split(' ').forEach((cls) => next.classList.remove(cls));
			activeClass.split(' ').forEach((cls) => next.classList.add(cls));

			// Scroll the sidebar to keep the active link visible
			const sidebarEl = next.closest('.sidebar');
			if (sidebarEl) {
				const linkRect = next.getBoundingClientRect();
				const sidebarRect = sidebarEl.getBoundingClientRect();
				if (linkRect.top < sidebarRect.top || linkRect.bottom > sidebarRect.bottom) {
					next.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
				}
			}
		}

		currentActive = id;
	}

	// When a sidebar link is clicked, immediately highlight it
	// and pause the observer so intermediate sections don't flash
	Array.from(anchorLinks).forEach((link) => {
		link.addEventListener('click', () => {
			const id = link.getAttribute('href')!.slice(1);
			if (!id) {
				return;
			}

			paused = true;
			setActive(id);

			// Resume after scrolling settles
			if (resumeTimer !== null) {
				clearTimeout(resumeTimer);
			}
			resumeTimer = window.setTimeout(() => {
				paused = false;
				resumeTimer = null;
			}, 800);
		});
	});

	// Use IntersectionObserver to track which sections are visible
	const visibleSections = new Set<string>();

	const observer = new IntersectionObserver((entries) => {
		entries.forEach((entry) => {
			const id = entry.target.id;
			if (entry.isIntersecting) {
				visibleSections.add(id);
			} else {
				visibleSections.delete(id);
			}
		});

		if (paused) {
			return;
		}

		// Pick the topmost visible section (in document order)
		for (const section of sections) {
			if (visibleSections.has(section.id)) {
				setActive(section.id);
				return;
			}
		}

		// If no section heading is visible, find which section we're inside
		// by picking the last heading that's above the viewport
		const scrollTop = window.scrollY + 100; // offset for fixed nav
		let lastAbove: string | null = null;
		for (const section of sections) {
			if (section.offsetTop <= scrollTop) {
				lastAbove = section.id;
			}
		}
		if (lastAbove) {
			setActive(lastAbove);
		}
	}, {
		rootMargin: '-80px 0px -60% 0px',
	});

	sections.forEach((section) => observer.observe(section));

	// Handle initial hash
	const hash = window.location.hash.slice(1);
	if (hash && linkMap.has(hash)) {
		setActive(hash);
	}
}

$(() => {
	ko.applyBindings({
		mainMenu: new MainMenuViewModel(),
	});

	initScrollSpy();
});
