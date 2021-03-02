import * as ko from 'knockout';
import {PHPStanError} from './PHPStanError';
import $ from 'jquery';
import {MainMenuViewModel} from './MainMenuViewModel';
import {PlaygroundTabViewModel} from './PlaygroundTabViewModel';

export class PlaygroundViewModel {

	mainMenu: MainMenuViewModel;
	code: ko.Observable<string>;
	codeDelayed: ko.Computed<string>;
	shareText: ko.Observable<string>;
	legacyResult: ko.Observable<string | null>;

	tabs: ko.ObservableArray<PlaygroundTabViewModel>;
	currentTabIndex: ko.Observable<number | null>;
	currentTab: ko.PureComputed<PlaygroundTabViewModel | null>;

	upToDateTabs: ko.Observable<PlaygroundTabViewModel[] | null>;

	level: ko.Observable<string>;
	strictRules: ko.Observable<boolean>;
	bleedingEdge: ko.Observable<boolean>;
	treatPhpDocTypesAsCertain: ko.Observable<boolean>;

	isLoading: ko.Observable<boolean>;
	isSharing: ko.Observable<boolean>;
	xhr: JQuery.jqXHR | null;
	shareXhr: JQuery.jqXHR | null;
	id: string | null;
	hasServerError: ko.Observable<boolean>;

	apiBaseUrl: string = 'https://api.phpstan.org';

	constructor() {
		this.mainMenu = new MainMenuViewModel();
		this.code = ko.observable('');
		this.codeDelayed = ko.pureComputed(this.code).extend({
			notify: 'always',
			rateLimit: { timeout: 500, method: 'notifyWhenChangesStop' },
		});
		this.shareText = ko.observable('Share');
		this.legacyResult = ko.observable(null);

		// @ts-ignore
		this.tabs = ko.observableArray([]);
		this.currentTabIndex = ko.observable(null);
		this.currentTab = ko.pureComputed(() => {
			const index = this.currentTabIndex();
			if (index === null) {
				return null;
			}

			return this.tabs()[index];
		});

		this.upToDateTabs = ko.observable(null);

		this.level = ko.observable('8');
		this.strictRules = ko.observable(false);
		this.bleedingEdge = ko.observable(false);
		this.treatPhpDocTypesAsCertain = ko.observable(true);

		this.isLoading = ko.observable(false);
		this.isSharing = ko.observable(false);
		this.xhr = null;
		this.shareXhr = null;
		this.id = null;
		this.hasServerError = ko.observable(false);
	}

	switchTab(index: number): void {
		const currentIndex = this.currentTabIndex();
		if (currentIndex !== null) {
			this.tabs()[currentIndex].isActive(false);
		}

		this.currentTabIndex(index);
		this.tabs()[index].isActive(true);
	}

	isActiveTab(index: number): boolean {
		return index === this.currentTabIndex();
	}

	setId(id: string | null): void {
		this.id = id;
		if (id === null) {
			window.history.replaceState({}, '', '/');
		} else{
			window.history.replaceState({}, '', '/r/' + id);
		}
	}

	preanalyse(): void {
		this.setId(null);
		this.hasServerError(false);
		if (this.xhr !== null) {
			this.xhr.abort();
			this.xhr = null;
		}
		if (this.shareXhr !== null) {
			this.shareXhr.abort();
			this.shareXhr = null;
		}

		this.isLoading(true);
	}

	analyse(saveResult: boolean): JQuery.jqXHR {
		this.xhr = $.ajax({
			type: 'POST',
			url: this.apiBaseUrl + '/analyse',
			dataType: 'json',
			data: JSON.stringify({
				code: this.code(),
				level: this.level(),
				strictRules: this.strictRules(),
				bleedingEdge: this.bleedingEdge(),
				treatPhpDocTypesAsCertain: this.treatPhpDocTypesAsCertain(),
				saveResult,
			}),
			contentType: 'application/json'
		}).done((data) => {
			this.tabs(this.createTabs(data.tabs));
			this.currentTabIndex(0);
			this.legacyResult(null);
			this.upToDateTabs(null);
		}).fail((xhr, textStatus) => {
			if (textStatus === 'abort') {
				return;
			}


			this.hasServerError(true);
		}).always(() => {
			this.isLoading(false);
		});

		return this.xhr;
	}

	share(): void {
		if (this.id !== null) {
			this.copyId();
			return;
		}
		this.isSharing(true);
		this.analyse(true).done((data) => {
			this.setId(data.id);
			this.copyId();
		}).always(() => {
			this.isSharing(false);
		});
	}

	copyId(): void {
		// @ts-ignore TS2339
		if (typeof window.navigator.share !== 'undefined') {
			// @ts-ignore TS2339
			window.navigator.share({url: window.location.href});
			return;
		}
		if (typeof window.navigator.clipboard !== 'undefined') {
			window.navigator.clipboard.writeText(window.location.href);
			this.shareText('Copied!');
			window.setTimeout(() => {
				this.shareText('Share');
			}, 2000);
			return;
		}
	}

	startAcceptingChanges(): void {
		this.code.subscribe(() => {
			this.preanalyse();
		});
		this.codeDelayed.subscribe(() => {
			this.analyse(false);
		});

		const instantAnalyse = () => {
			this.preanalyse();
			this.analyse(false);
		};
		this.level.subscribe(instantAnalyse);
		this.strictRules.subscribe(instantAnalyse);
		this.bleedingEdge.subscribe(instantAnalyse);
		this.treatPhpDocTypesAsCertain.subscribe(instantAnalyse);
	}

	showUpToDateTabs(): void {
		const tabs = this.upToDateTabs();
		if (tabs === null) {
			return;
		}

		this.tabs(tabs);
		this.currentTabIndex(0);
		this.legacyResult(null);
		this.upToDateTabs(null);
		this.setId(null);
	}

	init(path: string, initCallback: () => void): void {
		const legacyHashMatch = path.match(/^\/r\/([a-f0-9]{32})$/);
		let resultUrl = null;
		let id: string | null = null;
		if (legacyHashMatch !== null) {
			id = legacyHashMatch[1];
			resultUrl = this.apiBaseUrl + '/legacyResult?id=' + id;
		}

		const hashMatch = path.match(/^\/r\/([0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12})$/i);
		if (hashMatch !== null) {
			id = hashMatch[1];
			resultUrl = this.apiBaseUrl + '/result?id=' + id;
		}

		if (resultUrl !== null) {
			$.get(resultUrl).done((data) => {
				this.code(data.code);

				if (hashMatch !== null) {
					let tabs;
					if (typeof data.tabs !== 'undefined') {
						tabs = this.createTabs(data.tabs);
					} else {
						tabs = this.createTabs([{errors: data.errors, title: 'PHP 7.4'}]);
					}
					this.tabs(tabs);
					this.currentTabIndex(0);
					this.legacyResult(null);
					if (id !== null) {
						this.setId(id);
					}

					const upToDateTabs = this.createTabs(data.upToDateTabs);
					if (this.areTabsDifferent(tabs, upToDateTabs)) {
						this.upToDateTabs(upToDateTabs);
					} else {
						this.upToDateTabs(null);
					}
				} else {
					this.tabs([]);
					this.currentTabIndex(null);
					this.legacyResult(data.htmlErrors);
					this.upToDateTabs(this.createTabs(data.upToDateTabs));
				}
				this.level(data.level);
				this.strictRules(data.config.strictRules);
				this.bleedingEdge(data.config.bleedingEdge);
				this.treatPhpDocTypesAsCertain(data.config.treatPhpDocTypesAsCertain);
			}).fail(() => {
				this.hasServerError(true);
			}).always(() => {
				initCallback();
				this.startAcceptingChanges();
			});
			return;
		}

		this.code('<?php declare(strict_types = 1);\n' +
			'\n' +
			'class HelloWorld\n' +
			'{\n' +
			'\tpublic function sayHello(DateTimeImutable $date): void\n' +
			'\t{\n' +
			'\t\techo \'Hello, \' . $date->format(\'j. n. Y\');\n' +
			'\t}\n' +
			'}'
		);
		this.analyse(false).always(() => {
			initCallback();
			this.startAcceptingChanges();
		});
	}

	areTabsDifferent(tabs: PlaygroundTabViewModel[], upToDateTabs: PlaygroundTabViewModel[]): boolean {
		if (tabs.length !== upToDateTabs.length) {
			return true;
		}

		for (let i = 0; i < tabs.length; i++) {
			const tab = tabs[i];
			const upToDateTab = upToDateTabs[i];
			if (tab.title !== upToDateTab.title && tabs.length > 1) {
				return true;
			}

			if (tab.errorsText !== upToDateTab.errorsText) {
				return true;
			}

			if (tab.errors.length !== upToDateTab.errors.length) {
				return true;
			}

			for (let j = 0; j < tab.errors.length; j++) {
				const error = tab.errors[j];
				const upToDateError = upToDateTab.errors[j];
				if (error.message !== upToDateError.message) {
					return true;
				}
				if (error.line !== upToDateError.line) {
					return true;
				}
			}
		}

		return false;
	}

	createTabs(tabs: {errors: PHPStanError[], title: string}[]): PlaygroundTabViewModel[] {
		const viewModelTabs: PlaygroundTabViewModel[] = [];
		let versionOrder = 0;
		for (const tab of tabs) {
			viewModelTabs.push(new PlaygroundTabViewModel(tab.errors, tab.title, versionOrder === 0));
			versionOrder++;
		}

		return viewModelTabs;
	}

}
