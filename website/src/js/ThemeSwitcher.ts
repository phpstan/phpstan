import * as ko from 'knockout';

declare global {
	// eslint-disable-next-line no-var
	var ThemeSwitcher: {
		readonly storageKey: string;
		reloadTheme(): void;
	};
}

const Themes = {
	Auto: 'auto',
	Light: 'light',
	Dark: 'dark',
} as const;
type Theme = typeof Themes[keyof typeof Themes];

class ThemeSwitcherViewModel {
	public themes = Object.entries(Themes);

	public themeIcons: {
		readonly [K in Theme]: string
	} = {
		// Icons from uxwing.com - free and no attribution required.
		[Themes.Auto]: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 122.88 94.35" class="w-4 h-4 inline-block" fill="currentColor" aria-hidden="true" focusable="false"><g><path style="fill-rule:evenodd;clip-rule:evenodd;" d="M90.37,26.48h25.48c1.94,0,3.71,0.79,4.97,2.06c1.28,1.28,2.06,3.04,2.06,4.97v53.82 c0,1.94-0.79,3.71-2.06,4.97c-1.28,1.28-3.04,2.06-4.97,2.06H90.37c-1.94,0-3.71-0.79-4.97-2.06c-1.28-1.28-2.06-3.04-2.06-4.97 V33.5c0-1.94,0.79-3.71,2.06-4.97C86.68,27.25,88.43,26.48,90.37,26.48L90.37,26.48z M3.05,0h106.12c1.68,0,3.05,1.37,3.05,3.05 v18.44h-6.48V8.44c0-1.48-1.21-2.7-2.7-2.7H9.17v0c-1.48,0-2.7,1.21-2.7,2.7v52.53c0,1.48,1.21,2.7,2.7,2.7H76.7V76.4H3.05 C1.37,76.4,0,75.03,0,73.35V3.05C0,1.37,1.37,0,3.05,0L3.05,0L3.05,0z M42.27,80.61h27.67c0.07,4.79,2.04,9.07,7.39,12.45H34.89 C39.16,89.96,42.29,86.19,42.27,80.61L42.27,80.61L42.27,80.61z M56.11,66.12c2.16,0,3.92,1.75,3.92,3.92 c0,2.16-1.76,3.92-3.92,3.92c-2.16,0-3.92-1.75-3.92-3.92C52.19,67.88,53.94,66.12,56.11,66.12L56.11,66.12z M103.1,85.72 c1.59,0,2.89,1.28,2.89,2.89c0,1.59-1.28,2.89-2.89,2.89c-1.59,0-2.89-1.28-2.89-2.89C100.21,87.02,101.49,85.72,103.1,85.72 L103.1,85.72z M86.3,83.52h33.61V37.37H86.3V83.52L86.3,83.52z"/></g></svg>',
		[Themes.Light]: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 122.88 122.88" class="w-4 h-4 inline-block" fill="currentColor" aria-hidden="true" focusable="false"><path d="M30,13.21A3.93,3.93,0,1,1,36.8,9.27L41.86,18A3.94,3.94,0,1,1,35.05,22L30,13.21Zm31.45,13A35.23,35.23,0,1,1,36.52,36.52,35.13,35.13,0,0,1,61.44,26.2ZM58.31,4A3.95,3.95,0,1,1,66.2,4V14.06a3.95,3.95,0,1,1-7.89,0V4ZM87.49,10.1A3.93,3.93,0,1,1,94.3,14l-5.06,8.76a3.93,3.93,0,1,1-6.81-3.92l5.06-8.75ZM109.67,30a3.93,3.93,0,1,1,3.94,6.81l-8.75,5.06a3.94,3.94,0,1,1-4-6.81L109.67,30Zm9.26,28.32a3.95,3.95,0,1,1,0,7.89H108.82a3.95,3.95,0,1,1,0-7.89Zm-6.15,29.18a3.93,3.93,0,1,1-3.91,6.81l-8.76-5.06A3.93,3.93,0,1,1,104,82.43l8.75,5.06ZM92.89,109.67a3.93,3.93,0,1,1-6.81,3.94L81,104.86a3.94,3.94,0,0,1,6.81-4l5.06,8.76Zm-28.32,9.26a3.95,3.95,0,1,1-7.89,0V108.82a3.95,3.95,0,1,1,7.89,0v10.11Zm-29.18-6.15a3.93,3.93,0,0,1-6.81-3.91l5.06-8.76A3.93,3.93,0,1,1,40.45,104l-5.06,8.75ZM13.21,92.89a3.93,3.93,0,1,1-3.94-6.81L18,81A3.94,3.94,0,1,1,22,87.83l-8.76,5.06ZM4,64.57a3.95,3.95,0,1,1,0-7.89H14.06a3.95,3.95,0,1,1,0,7.89ZM10.1,35.39A3.93,3.93,0,1,1,14,28.58l8.76,5.06a3.93,3.93,0,1,1-3.92,6.81L10.1,35.39Z"/></svg>',
		[Themes.Dark]: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 122.56 122.88" class="w-4 h-4 inline-block" fill="currentColor" aria-hidden="true" focusable="false"><path style="fill-rule:evenodd" d="M121.85,87.3A64.31,64.31,0,1,1,36.88.4c2.94-1.37,5.92.91,4.47,4.47a56.29,56.29,0,0,0,75.75,77.4l.49-.27a3.41,3.41,0,0,1,4.61,4.61l-.35.69ZM92.46,74.67H92A16.11,16.11,0,0,0,76.2,58.93v-.52a15.08,15.08,0,0,0,11-4.72,15.19,15.19,0,0,0,4.72-11h.51a15.12,15.12,0,0,0,4.72,11,15.12,15.12,0,0,0,11,4.72v.51A16.13,16.13,0,0,0,92.46,74.67Zm10.09-46.59h-.27a7.94,7.94,0,0,0-2.49-5.81A7.94,7.94,0,0,0,94,19.78v-.27A7.94,7.94,0,0,0,99.79,17a8,8,0,0,0,2.49-5.8h.27A8,8,0,0,0,105,17a8,8,0,0,0,5.81,2.49v.27A8,8,0,0,0,105,22.27a7.94,7.94,0,0,0-2.49,5.81Zm-41.5,8h-.41a12.06,12.06,0,0,0-3.78-8.82A12.06,12.06,0,0,0,48,23.5v-.41a12.07,12.07,0,0,0,8.82-3.78,12.09,12.09,0,0,0,3.78-8.82h.41a12.08,12.08,0,0,0,3.77,8.82,12.09,12.09,0,0,0,8.83,3.78v.41a12.09,12.09,0,0,0-8.83,3.78,12.08,12.08,0,0,0-3.77,8.82Z"/></svg>',
	} as const;

	private _chosenTheme = ko.observable(localStorage.getItem(window.ThemeSwitcher.storageKey) || Themes.Auto);

	public chosenTheme = ko.pureComputed({
		read: (): Theme => this._chosenTheme() as Theme,
		write: (value: Theme): void => {
			this._chosenTheme(value);
			localStorage.setItem(window.ThemeSwitcher.storageKey, value);
			window.ThemeSwitcher.reloadTheme();
		},
		owner: this
	});

	public showSwitcher = ko.observable(false);

	public toggleSwitcher = (): void => {
		this.showSwitcher(!this.showSwitcher());
	};

	constructor() {
		window.addEventListener('mouseup', () => this.showSwitcher(false));
	}
}

ko.components.register('theme-switcher', {
	viewModel: ThemeSwitcherViewModel,
	template:
		'<span class="relative">\
			<button\
				title="Change theme"\
				class="text-gray-500 hover:text-gray-700 focus:outline-none focus:text-gray-700 transition duration-150 ease-in-out"\
				aria-haspopup="menu"\
				data-bind="click: toggleSwitcher, attr: {\'aria-expanded\': showSwitcher() ? \'true\' : \'false\'}"\
			>\
				<i data-bind="html: themeIcons[chosenTheme()]"></i>\
			</button>\
			<ul class="absolute -left-8 top-8 p-2 w-28 bg-white rounded-lg border border-slate-200" data-bind="visible: showSwitcher, foreach: themes">\
				<li>\
					<label class="block my-1 cursor-pointer px-2 text-gray-500 hover:text-gray-700 focus:outline-none focus:text-gray-700 transition duration-150 ease-in-out">\
						<i class="me-2" data-bind="html: $parent.themeIcons[$data[1]]"></i>\
						<input name="theme" type="radio" class="hidden" data-bind="checkedValue: $data[1], checked: $parent.chosenTheme">\
						<span data-bind="text: $data[0]"> </span>\
					</label>\
				</li>\
			</ul>\
		</span>'
});
