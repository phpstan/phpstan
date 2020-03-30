import * as ko from 'knockout';
import {PHPStanError} from './PHPStanError';
import $ from 'jquery';

export class PlaygroundViewModel {

	mainMenuOpen: KnockoutObservable<boolean>;
	code: KnockoutObservable<string>;
	codeDelayed: KnockoutComputed<string>;
	errors: KnockoutObservableArray<PHPStanError>;
	errorsText: KnockoutComputed<string>;
	shareText: KnockoutObservable<string>;
	legacyResult: KnockoutObservable<string | null>;

	level: KnockoutObservable<string>;
	strictRules: KnockoutObservable<boolean>;
	bleedingEdge: KnockoutObservable<boolean>;
	treatPhpDocTypesAsCertain: KnockoutObservable<boolean>;

	isLoading: KnockoutObservable<boolean>;
	isSharing: KnockoutObservable<boolean>;
	xhr: JQuery.jqXHR | null;
	shareXhr: JQuery.jqXHR | null;
	id: string | null;
	hasServerError: KnockoutObservable<boolean>;

	apiBaseUrl: string = 'https://api.phpstan.org';

	constructor() {
		this.mainMenuOpen = ko.observable(false);
		this.code = ko.observable('');
		this.codeDelayed = ko.pureComputed(this.code).extend({
			notify: 'always',
			rateLimit: { timeout: 500, method: 'notifyWhenChangesStop' },
		});
		this.errors = ko.observableArray([]);
		this.errorsText = ko.pureComputed(() => {
			const errorsCount = this.errors().length;
			if (errorsCount === 1) {
				return 'Found 1 error';
			}

			return 'Found ' + errorsCount.toString() + ' errors';
		});
		this.shareText = ko.observable('Share');
		this.legacyResult = ko.observable(null);

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
			this.errors(data.errors.map((error: any): PHPStanError => {
				return {line: error.line, message: error.message};
			}));
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

	toggleMainMenu(): void {
		this.mainMenuOpen(!this.mainMenuOpen());
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
					this.errors(data.errors.map((error: any): PHPStanError => {
						return {line: error.line, message: error.message};
					}));
					this.legacyResult(null);
					if (id !== null) {
						this.setId(id);
					}
				} else {
					this.errors([]);
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

}
