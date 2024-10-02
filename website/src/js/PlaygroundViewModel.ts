import * as ko from 'knockout';
import {PHPStanError} from './PHPStanError';
import $ from 'jquery';
import {MainMenuViewModel} from './MainMenuViewModel';
import {PlaygroundTabViewModel} from './PlaygroundTabViewModel';
import linkifyStr from 'linkify-string';
import * as pages from '../pages.json';
import * as Sentry from '@sentry/browser';
import {slugify} from './ErrorIdentifiersViewModel';

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
	id: ko.Observable<string | null>;
	resultUrl: string | null;
	isHashMatch: boolean;
	hasServerError: ko.Observable<boolean>;

	apiBaseUrl: string = 'https://api.phpstan.org';

	linkify: typeof linkifyStr;

	slugify: typeof slugify;

	constructor(urlPath: string) {
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

		this.level = ko.observable('10');
		this.strictRules = ko.observable<boolean>(false);
		this.bleedingEdge = ko.observable<boolean>(false);
		this.treatPhpDocTypesAsCertain = ko.observable<boolean>(true);

		this.isLoading = ko.observable<boolean>(false);
		this.isSharing = ko.observable<boolean>(false);
		this.xhr = null;
		this.shareXhr = null;

		const legacyHashMatch = urlPath.match(/^\/r\/([a-f0-9]{32})$/);
		let resultUrl = null;
		let id: string | null = null;
		if (legacyHashMatch !== null) {
			id = legacyHashMatch[1];
			resultUrl = this.apiBaseUrl + '/legacyResult?id=' + id;
		}

		const hashMatch = urlPath.match(/^\/r\/([0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12})$/i);
		if (hashMatch !== null) {
			id = hashMatch[1];
			resultUrl = this.apiBaseUrl + '/result?id=' + id;
		}

		this.resultUrl = resultUrl;
		this.isHashMatch = hashMatch !== null;

		this.id = ko.observable(id);
		this.id.subscribe((value) => {
			if (value === null) {
				window.history.replaceState({}, '', '/try');
			} else {
				window.history.replaceState({}, '', '/r/' + value);
			}
		});
		this.hasServerError = ko.observable<boolean>(false);

		this.linkify = (text: string, options) => {
			return linkifyStr(text, {
				className: () => 'underline hover:no-underline',
				target: '_blank',
				format: (value, type) => {
					if (type === 'url' && value.startsWith('https://phpstan.org/')) {
						const path = value.substring('https://phpstan.org'.length);
						if (path in pages) {
							// @ts-ignore
							return pages[path];
						}
					}
					return value;
				},
			}).replace('%configurationFile%', '<a class="underline hover:no-underline" target="_blank" href="/config-reference">configuration file</a>').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br/>' + '$2');
		};

		this.slugify = slugify;
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

	preanalyse(): void {
		this.id(null);
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
			const scope = new Sentry.Scope();
			scope.setExtra('code', this.code());
			Sentry.captureMessage('Server error - could not analyse code', scope);
		}).always(() => {
			this.isLoading(false);
		});

		return this.xhr;
	}

	share(): void {
		if (this.id() !== null) {
			this.copyId();
			return;
		}
		this.isSharing(true);
		this.analyse(true).done((data) => {
			this.id(data.id);
			this.copyId();

			const anyWindow = (window as any);
			if (typeof anyWindow.fathom !== 'undefined') {
				anyWindow.fathom.trackGoal('N702LGVH', 0);
			}
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
			this.analyse(false).done(() => {
				const anyWindow = (window as any);
				if (typeof anyWindow.fathom !== 'undefined') {
					anyWindow.fathom.trackGoal('BGQV3HAP', 0);
				}
			});
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
		this.id(null);
	}

	init(initCallback: () => void): void {
		if (this.resultUrl !== null) {
			$.get(this.resultUrl).done((data) => {
				this.code(data.code);

				if (this.isHashMatch) {
					let tabs;
					if (typeof data.tabs !== 'undefined') {
						tabs = this.createTabs(data.tabs);
					} else {
						tabs = this.createTabs([{errors: data.errors, title: 'PHP 7.4'}]);
					}
					this.currentTabIndex(0);
					this.legacyResult(null);

					const upToDateTabs = this.createTabs(data.upToDateTabs);
					if (this.areTabsDifferent(tabs, upToDateTabs)) {
						this.tabs(tabs);
						this.upToDateTabs(upToDateTabs);
					} else {
						this.tabs(upToDateTabs);
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
				const scope = new Sentry.Scope();
				scope.setExtra('id', this.id());
				Sentry.captureMessage('Server error - could not get analysed result');
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
		initCallback();
		this.tabs([
			new PlaygroundTabViewModel([
				{
					message: 'Parameter $date of method HelloWorld::sayHello() has invalid typehint type DateTimeImutable.',
					line: 5,
					ignorable: true,
					identifier: 'class.notFound',
				},
				{
					message: 'Call to method format() on an unknown class DateTimeImutable.',
					line: 7,
					ignorable: true,
					identifier: 'class.notFound',
				},
			], '', true),
		]);
		this.currentTabIndex(0);
		this.legacyResult(null);
		this.upToDateTabs(null);
		this.startAcceptingChanges();
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
				if (error.tip !== upToDateError.tip) {
					return true;
				}
				if (typeof error.identifier !== 'undefined' && typeof upToDateError.identifier !== 'undefined') {
					if (error.identifier !== upToDateError.identifier) {
						return true;
					}
				}
				if (typeof error.ignorable !== 'undefined' && typeof upToDateError.ignorable !== 'undefined') {
					if (error.ignorable !== upToDateError.ignorable) {
						return true;
					}
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
