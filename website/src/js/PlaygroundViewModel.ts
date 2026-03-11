import * as ko from 'knockout';
import {PHPStanError} from './PHPStanError';
import $ from 'jquery';
import {MainMenuViewModel} from './MainMenuViewModel';
import {PlaygroundTabViewModel} from './PlaygroundTabViewModel';
import linkifyStr from 'linkify-string';
import * as Sentry from '@sentry/browser';
import {slugify} from './utils';
import {EditorView} from '@codemirror/view';
import {Transaction} from '@codemirror/state';
import {setUrlId, urlIdField} from './editor/urlId';
import {historyField} from '@codemirror/commands';

declare const __PAGES_JSON__: Record<string, string>;
const pages = __PAGES_JSON__;

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
	resultXhr: JQuery.jqXHR | null;
	id: ko.Observable<string | null>;
	resultUrl: string | null;
	sampleUrl: string | null;
	isHashMatch: boolean;
	hasServerError: ko.Observable<boolean>;

	apiBaseUrl: string = 'https://api.phpstan.org';

	editorView: EditorView | null;
	urlIdJustRestored: boolean;
	savedTabsByUrlId: Map<string, {tabs: PlaygroundTabViewModel[], upToDateTabs: PlaygroundTabViewModel[] | null}>;
	settingUrlIdProgrammatically: boolean;
	restoredEditorState: any | null;

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
		this.resultXhr = null;
		this.editorView = null;
		this.urlIdJustRestored = false;
		this.savedTabsByUrlId = new Map();
		this.settingUrlIdProgrammatically = false;
		this.restoredEditorState = null;

		const legacyHashMatch = urlPath.match(/^\/r\/([a-f0-9]{32})$/);
		let resultUrl = null;
		let sampleUrl: string | null = null;
		let id: string | null = null;
		if (legacyHashMatch !== null) {
			id = legacyHashMatch[1];
			resultUrl = this.apiBaseUrl + '/legacyResult?id=' + id;
		}

		const hashMatch = urlPath.match(/^\/r\/([0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12})$/i);
		if (hashMatch !== null) {
			id = hashMatch[1];
			resultUrl = this.apiBaseUrl + '/result?id=' + id;
			sampleUrl = this.apiBaseUrl + '/sample?id=' + id;
		}

		this.resultUrl = resultUrl;
		this.sampleUrl = sampleUrl;
		this.isHashMatch = hashMatch !== null;

		this.id = ko.observable(id);
		this.id.subscribe((value) => {
			if (value === null) {
				window.history.replaceState(window.history.state, '', '/try');
			} else {
				window.history.replaceState(window.history.state, '', '/r/' + value);
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
		if (this.resultXhr !== null) {
			this.resultXhr.abort();
			this.resultXhr = null;
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
			this.savePlaygroundState();
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
			this.savedTabsByUrlId.set(data.id, {tabs: this.tabs().slice(), upToDateTabs: null});
			if (this.editorView) {
				this.settingUrlIdProgrammatically = true;
				this.editorView.dispatch({
					effects: [setUrlId.of(data.id)],
				});
				this.settingUrlIdProgrammatically = false;
			}
			this.copyId();
			this.savePlaygroundState();

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

	grabEditorView(): void {
		const el = document.querySelector('[data-bind*="codeMirror"]');
		if (el) {
			this.editorView = ko.utils.domData.get(el, 'codeMirror') ?? null;
		}
	}

	onEditorUrlIdChange(newUrlId: string | null): void {
		if (this.settingUrlIdProgrammatically) {
			return;
		}
		if (newUrlId !== null) {
			this.urlIdJustRestored = true;
			this.id(newUrlId);

			if (this.xhr !== null) {
				this.xhr.abort();
				this.xhr = null;
			}
			if (this.shareXhr !== null) {
				this.shareXhr.abort();
				this.shareXhr = null;
			}
			if (this.resultXhr !== null) {
				this.resultXhr.abort();
				this.resultXhr = null;
			}

			this.isLoading(false);
			this.hasServerError(false);

			const saved = this.savedTabsByUrlId.get(newUrlId);
			if (saved) {
				this.tabs(saved.tabs);
				this.currentTabIndex(0);
				this.legacyResult(null);
				this.upToDateTabs(saved.upToDateTabs);
			}
		}
	}

	startAcceptingChanges(): void {
		window.addEventListener('beforeunload', () => {
			if (!this.isLoading()) {
				this.savePlaygroundState();
			}
		});

		this.code.subscribe(() => {
			if (this.urlIdJustRestored) {
				this.urlIdJustRestored = false;
				return;
			}
			this.preanalyse();
		});
		this.codeDelayed.subscribe(() => {
			if (this.id() !== null) {
				return;
			}
			this.analyse(false).done(() => {
				const anyWindow = (window as any);
				if (typeof anyWindow.fathom !== 'undefined') {
					anyWindow.fathom.trackGoal('BGQV3HAP', 0);
				}
			});
		});

		const instantAnalyse = () => {
			if (this.editorView) {
				this.editorView.dispatch({
					effects: [setUrlId.of(null)],
					annotations: [Transaction.addToHistory.of(false)],
				});
			}
			this.savedTabsByUrlId.clear();
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

	savePlaygroundState(): void {
		if (!this.editorView) return;
		const state = {
			editorState: this.editorView.state.toJSON({history: historyField, urlId: urlIdField}),
			settings: {
				level: this.level(),
				strictRules: this.strictRules(),
				bleedingEdge: this.bleedingEdge(),
				treatPhpDocTypesAsCertain: this.treatPhpDocTypesAsCertain(),
			},
			tabs: this.tabs().map(t => ({errors: t.errors, title: t.title})),
			currentTabIndex: this.currentTabIndex(),
			legacyResult: this.legacyResult(),
			upToDateTabs: this.upToDateTabs()?.map(t => ({errors: t.errors, title: t.title})) ?? null,
			savedTabsByUrlId: Object.fromEntries(
				[...this.savedTabsByUrlId].map(([k, v]) => [k, {
					tabs: v.tabs.map(t => ({errors: t.errors, title: t.title})),
					upToDateTabs: v.upToDateTabs?.map(t => ({errors: t.errors, title: t.title})) ?? null,
				}])
			),
			id: this.id(),
		};
		window.history.replaceState(state, '', window.location.pathname);
	}

	static loadSavedState(): any | null {
		const state = window.history.state;
		if (state && typeof state.editorState === 'object' && state.editorState !== null) {
			return state;
		}
		return null;
	}

	init(initCallback: () => void): void {
		const saved = PlaygroundViewModel.loadSavedState();
		if (saved) {
			const doc = Array.isArray(saved.editorState.doc)
				? saved.editorState.doc.join('\n')
				: saved.editorState.doc;
			this.code(doc);
			this.level(saved.settings.level);
			this.strictRules(saved.settings.strictRules);
			this.bleedingEdge(saved.settings.bleedingEdge);
			this.treatPhpDocTypesAsCertain(saved.settings.treatPhpDocTypesAsCertain);
			this.id(saved.id);
			this.restoredEditorState = saved.editorState;

			this.tabs(this.createTabs(saved.tabs));
			this.currentTabIndex(saved.currentTabIndex);
			this.legacyResult(saved.legacyResult);
			this.upToDateTabs(saved.upToDateTabs ? this.createTabs(saved.upToDateTabs) : null);

			for (const [key, value] of Object.entries(saved.savedTabsByUrlId)) {
				this.savedTabsByUrlId.set(key, {
					tabs: this.createTabs((value as any).tabs),
					upToDateTabs: (value as any).upToDateTabs
						? this.createTabs((value as any).upToDateTabs) : null,
				});
			}

			initCallback();
			this.grabEditorView();
			this.startAcceptingChanges();
			return;
		}

		if (this.sampleUrl !== null && this.resultUrl !== null) {
			const originalId = this.id();
			$.get(this.sampleUrl).done((data) => {
				this.code(data.code);

				let tabs;
				if (typeof data.tabs !== 'undefined') {
					tabs = this.createTabs(data.tabs);
				} else {
					tabs = this.createTabs([{errors: data.errors, title: 'PHP 7.4'}]);
				}
				this.tabs(tabs);
				this.currentTabIndex(0);
				this.legacyResult(null);
				this.upToDateTabs(null);
				this.level(data.level);
				this.strictRules(data.config.strictRules);
				this.bleedingEdge(data.config.bleedingEdge);
				this.treatPhpDocTypesAsCertain(data.config.treatPhpDocTypesAsCertain);

				if (originalId !== null) {
					this.savedTabsByUrlId.set(originalId, {tabs, upToDateTabs: null});
				}

				initCallback();
				this.grabEditorView();
				this.startAcceptingChanges();
				this.savePlaygroundState();

				this.resultXhr = $.get(this.resultUrl!).done((resultData) => {
					if (this.id() !== originalId) {
						return;
					}

					let savedTabs;
					if (typeof resultData.tabs !== 'undefined') {
						savedTabs = this.createTabs(resultData.tabs);
					} else {
						savedTabs = this.createTabs([{errors: resultData.errors, title: 'PHP 7.4'}]);
					}
					const upToDateTabs = this.createTabs(resultData.upToDateTabs);

					if (this.areTabsDifferent(savedTabs, upToDateTabs)) {
						this.tabs(savedTabs);
						this.upToDateTabs(upToDateTabs);
						if (originalId !== null) {
							this.savedTabsByUrlId.set(originalId, {tabs: savedTabs, upToDateTabs});
						}
					} else {
						this.tabs(upToDateTabs);
						this.upToDateTabs(null);
						if (originalId !== null) {
							this.savedTabsByUrlId.set(originalId, {tabs: upToDateTabs, upToDateTabs: null});
						}
					}
					this.savePlaygroundState();
				});
			}).fail(() => {
				this.hasServerError(true);
				const scope = new Sentry.Scope();
				scope.setExtra('id', this.id());
				Sentry.captureMessage('Server error - could not get analysed result');
				initCallback();
				this.startAcceptingChanges();
			});
			return;
		}

		if (this.resultUrl !== null) {
			$.get(this.resultUrl).done((data) => {
				this.code(data.code);

				this.tabs([]);
				this.currentTabIndex(null);
				this.legacyResult(data.htmlErrors);
				this.upToDateTabs(this.createTabs(data.upToDateTabs));

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
			'use function PHPStan\\dumpType;\n' +
			'use function PHPStan\\Testing\\assertType;\n' +
			'\n' +
			'class CoffeeBreak\n' +
			'{\n' +
			'\tpublic function getDuration(): int { }\n' +
			'}\n' +
			'\n' +
			'class MondayMorning\n' +
			'{\n' +
			'\tprivate bool $coffeeConsumed = false;\n' +
			'\n' +
			'\tpublic function startDay(string|int $task): string\n' +
			'\t{\n' +
			'\t\t$this->coffeeConsumed = true;\n' +
			'\t\t\n' +
			'\t\t/** @var DateTime $deadline */\n' +
			'\t\t$deadline = new DateTimeImmutable(\'friday\');\n' +
			'\t\t$deadline->modify(\'-1 week\'); // that will help\n' +
			'\t\t\n' +
			'\t\t$words = count(explode(\' \', $task));\n' +
			'\t\tif ($words === 0) { echo \'if only\'; }\n' +
			'\t\t$this->deployToProduction();\n' +
			'\t\treturn array_pop($task);\n' +
			'\t}\n' +
			'}\n' +
			'\n' +
			'$cb = new CoffeeBreak();\n' +
			'if (isset($cb->getDuration())) { echo \'break time\'; }\n' +
			'\n' +
			'echo sprintf(\'%s %s\', \'safe\');\n'
		);
		initCallback();
		this.tabs([
			new PlaygroundTabViewModel([
				{
					message: 'Method CoffeeBreak::getDuration() should return int but return statement is missing.',
					line: 8,
					ignorable: true,
					identifier: 'return.missing',
				},
				{
					message: 'Property MondayMorning::$coffeeConsumed is never read, only written.',
					line: 13,
					ignorable: true,
					identifier: 'property.onlyWritten',
					tip: 'See: https://phpstan.org/developing-extensions/always-read-written-properties',
				},
				{
					message: 'PHPDoc tag @var with type DateTime is not subtype of native type DateTimeImmutable.',
					line: 20,
					ignorable: true,
					identifier: 'varTag.nativeType',
				},
				{
					message: 'Parameter #2 $string of function explode expects string, int|string given.',
					line: 23,
					ignorable: true,
					identifier: 'argument.type',
				},
				{
					message: 'Strict comparison using === between int<1, max> and 0 will always evaluate to false.',
					line: 24,
					ignorable: true,
					identifier: 'identical.alwaysFalse',
					tip: 'Because the type is coming from a PHPDoc, you can turn off this check by setting <code>treatPhpDocTypesAsCertain: false</code> in your <code>%configurationFile%</code>.',
				},
				{
					message: 'Call to an undefined method MondayMorning::deployToProduction().',
					line: 25,
					ignorable: true,
					identifier: 'method.notFound',
				},
				{
					message: 'Method MondayMorning::startDay() should return string but returns null.',
					line: 26,
					ignorable: true,
					identifier: 'return.type',
				},
				{
					message: 'Parameter #1 $array of function array_pop expects array, int|string given.',
					line: 26,
					ignorable: true,
					identifier: 'argument.type',
				},
				{
					message: 'Expression in isset() is not nullable.',
					line: 31,
					ignorable: true,
					identifier: 'isset.expr',
				},
				{
					message: 'Call to sprintf contains 2 placeholders, 1 value given.',
					line: 33,
					ignorable: true,
					identifier: 'argument.sprintf',
				},
			], 'PHP 8.0 \u2013 8.5 (10 errors)', true),
			new PlaygroundTabViewModel([
				{
					message: 'Method CoffeeBreak::getDuration() should return int but return statement is missing.',
					line: 8,
					ignorable: true,
					identifier: 'return.missing',
				},
				{
					message: 'Property MondayMorning::$coffeeConsumed is never read, only written.',
					line: 13,
					ignorable: true,
					identifier: 'property.onlyWritten',
					tip: 'See: https://phpstan.org/developing-extensions/always-read-written-properties',
				},
				{
					message: 'Method MondayMorning::startDay() uses native union types but they\'re supported only on PHP 8.0 and later.',
					line: 15,
					ignorable: false,
					identifier: 'parameter.unionTypeNotSupported',
				},
				{
					message: 'PHPDoc tag @var with type DateTime is not subtype of native type DateTimeImmutable.',
					line: 20,
					ignorable: true,
					identifier: 'varTag.nativeType',
				},
				{
					message: 'Parameter #2 $str of function explode expects string, int|string given.',
					line: 23,
					ignorable: true,
					identifier: 'argument.type',
				},
				{
					message: 'Strict comparison using === between int<1, max> and 0 will always evaluate to false.',
					line: 24,
					ignorable: true,
					identifier: 'identical.alwaysFalse',
					tip: 'Because the type is coming from a PHPDoc, you can turn off this check by setting <code>treatPhpDocTypesAsCertain: false</code> in your <code>%configurationFile%</code>.',
				},
				{
					message: 'Call to an undefined method MondayMorning::deployToProduction().',
					line: 25,
					ignorable: true,
					identifier: 'method.notFound',
				},
				{
					message: 'Method MondayMorning::startDay() should return string but returns null.',
					line: 26,
					ignorable: true,
					identifier: 'return.type',
				},
				{
					message: 'Parameter #1 $stack of function array_pop expects array, int|string given.',
					line: 26,
					ignorable: true,
					identifier: 'argument.type',
				},
				{
					message: 'Expression in isset() is not nullable.',
					line: 31,
					ignorable: true,
					identifier: 'isset.expr',
				},
				{
					message: 'Call to sprintf contains 2 placeholders, 1 value given.',
					line: 33,
					ignorable: true,
					identifier: 'argument.sprintf',
				},
			], 'PHP 7.2 \u2013 7.4 (11 errors)', false),
		]);
		this.currentTabIndex(0);
		this.legacyResult(null);
		this.upToDateTabs(null);
		this.grabEditorView();
		this.startAcceptingChanges();
		this.savePlaygroundState();
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
