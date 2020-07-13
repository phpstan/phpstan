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
			this.createTabs(data.versionedErrors);
			this.legacyResult(null);
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
					if (typeof data.versionedErrors !== 'undefined') {
						this.createTabs(data.versionedErrors);
					} else {
						this.createTabs([{phpVersion: 70400, errors: data.errors}]);
					}
					this.legacyResult(null);
					if (id !== null) {
						this.setId(id);
					}
				} else {
					this.tabs([]);
					this.currentTabIndex(null);
					this.legacyResult(data.htmlErrors);
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

	createTabs(versionedErrors: {phpVersion: number, errors: PHPStanError[]}[]): void {
		const versions: {versions: number[], errors: PHPStanError[]}[] = [];
		let last: {versions: number[], errors: PHPStanError[]} | null = null;
		for (const version of versionedErrors) {
			const phpVersion = version.phpVersion;
			const errors = version.errors;
			const current = {
				versions: [phpVersion],
				errors,
			};
			if (last === null) {
				last = current;
				continue;
			}

			if (errors.length !== last.errors.length) {
				versions.push(last);
				last = current;
				continue;
			}

			let merge = true;
			for (const i in errors) {
				if (!errors.hasOwnProperty(i)) {
					continue;
				}
				const error = errors[i];
				const lastError = last.errors[i];
				if (error.line !== lastError.line) {
					versions.push(last);
					last = current;
					merge = false;
					break;
				}
				if (error.message !== lastError.message) {
					versions.push(last);
					last = current;
					merge = false;
					break;
				}
			}

			if (!merge) {
				continue;
			}

			last.versions.push(phpVersion);
		}

		if (last !== null) {
			versions.push(last);
		}

		versions.sort((a: {versions: number[], errors: PHPStanError[]}, b: {versions: number[], errors: PHPStanError[]}) => {
			const aVersion = a.versions[a.versions.length - 1];
			const bVersion = b.versions[b.versions.length - 1];
			if (aVersion === 80000) {
				return -1;
			} else if (bVersion === 80000) {
				return 1;
			}

			return bVersion - aVersion;
		});

		const tabs: PlaygroundTabViewModel[] = [];
		let versionOrder = 0;
		for (const version of versions) {
			tabs.push(new PlaygroundTabViewModel(version.errors, version.versions, versionOrder === 0));
			versionOrder++;
		}

		this.tabs(tabs);
		this.currentTabIndex(0);
	}

}
