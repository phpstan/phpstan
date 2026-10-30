import ko from '@tko/build.reference';
import {MainMenuViewModel, Theme} from './MainMenuViewModel';
import {bindElement, mount} from './tsx';

// The navigation, sidebar and footer of every page. Their links are rendered
// by Eleventy, so the panels that show and hide them only get bindings; the
// controls are TSX.
export function initMainMenu(): MainMenuViewModel {
	const menu = new MainMenuViewModel();
	const mainMenuClosed = ko.pureComputed(() => !menu.mainMenuOpen());
	const sidebarClosed = ko.pureComputed(() => !menu.sidebarOpen());

	mount('main-menu-button', (
		<button ko-click={() => menu.toggleMainMenu()} class="ml-2 inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-600 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-950 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-950 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
			<svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
				<path ko-css={{hidden: menu.mainMenuOpen, 'inline-flex': mainMenuClosed}} class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
				<path ko-css={{hidden: mainMenuClosed, 'inline-flex': menu.mainMenuOpen}} class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
			</svg>
		</button>
	));
	bindElement('main-menu-panel', {css: {block: menu.mainMenuOpen, hidden: mainMenuClosed}});

	bindElement('sidebar', {
		css: {hidden: sidebarClosed},
		event: {click: (_, event) => menu.handleSidebarClick(event)},
	});
	mount('sidebar-close', (
		<a href="#" class="md:hidden text-gray-400 dark:text-gray-600 hover:text-gray-500 dark:hover:text-gray-400" ko-click={() => menu.closeSidebar()}>
			<svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
			</svg>
		</a>
	));
	mount('sidebar-open', (
		<a href="#" class="text-sm text-gray-400 md:hidden no-underline hover:underline" ko-click={() => menu.openSidebar()}>Menu</a>
	));

	mount('theme-switcher', (
		<div class="flex rounded border border-gray-400 w-36 text-sm text-gray-400">
			{themeOption(menu, 'auto', 'font-medium text-base', 'border-r', 'Theme follows system-level setting', (
				<span>A</span>
			))}
			{themeOption(menu, 'light', '', 'border-r', 'Theme will always use light colors', (
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
					<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
				</svg>
			))}
			{themeOption(menu, 'dark', '', '', 'Theme will always use dark colors', (
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
					<path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
				</svg>
			))}
		</div>
	));

	return menu;
}

function themeOption(menu: MainMenuViewModel, theme: Theme, className: string, inactiveBorder: string, title: string, icon: JSX.Element): JSX.Element {
	const active = ko.pureComputed(() => menu.theme() === theme);
	const inactive = ko.pureComputed(() => menu.theme() !== theme);

	return (
		<div class={`flex basis-0 flex-grow items-center justify-center py-1 ${className}`} ko-click={() => menu.switchTheme(theme)} ko-css={{'cursor-default text-white bg-gray-400': active, [`cursor-pointer hover:text-gray-500 border-gray-400 ${inactiveBorder}`]: inactive}} title={title}>
			{icon}
		</div>
	);
}
