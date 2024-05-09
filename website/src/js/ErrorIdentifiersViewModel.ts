import * as ko from 'knockout';
import { MainMenuViewModel } from './MainMenuViewModel';
import * as errorIdentifiers from '../errorsIdentifiers.json';

export const slugify = (s: string) => encodeURIComponent(String(s).trim().toLowerCase().replace(/\s+/g, '-'))

export class ErrorIdentifiersViewModel {
	mainMenu: MainMenuViewModel;
	groupByIdentifier: ko.Observable<boolean>;

	list: ko.PureComputed<any>;

	constructor(hash: string) {
		this.mainMenu = new MainMenuViewModel();
		this.groupByIdentifier = ko.observable(
			hash.startsWith('#gbr-') ? false : true,
		);
		this.groupByIdentifier.subscribe(() => {
			document.documentElement.scrollIntoView({ behavior: 'smooth' });
		});
		this.list = ko.pureComputed(() => {
			if (!this.groupByIdentifier()) {
				return this.itemsByRule();
			}

			return this.itemsByIdentifier();
		});
	}

	itemsByRule() {
		const byRule = {};
		for (const [identifier, value] of Object.entries(errorIdentifiers)) {
			for (const [ruleClass, ruleValue] of Object.entries(value)) {
				let repo = null;

				const tmpItems = [];
				for (const [repoKey, ruleLinks] of Object.entries(ruleValue)) {
					repo = repoKey.slice('phpstan/'.length);
					tmpItems.push({
						itemName: identifier,
						itemLabel: 'phpstan-src', // unused
						links: ruleLinks,
					});
				}

				if (!(ruleClass in byRule)) {
					// @ts-ignore TS7053
					byRule[ruleClass] = {
						id: 'gbr-' + slugify(ruleClass),
						label: repo,
						title: ruleClass,
						items: tmpItems,
					};
				} else {
					// @ts-ignore TS7053
					byRule[ruleClass].items.push(...tmpItems);
				}
			}
		}

		return Object.values(
			Object.fromEntries(Object.entries(byRule).sort(([a],[b]) => a.localeCompare(b))),
		);
	}

	itemsByIdentifier() {
		const items = [];
		for (const [identifier, value] of Object.entries(errorIdentifiers)) {
			const rules = [];
			for (const [ruleClass, ruleValue] of Object.entries(value)) {
				const links = [];
				let repo = null;
				for (const [repoKey, ruleLinks] of Object.entries(ruleValue)) {
					repo = repoKey.slice('phpstan/'.length);
					// @ts-ignore TS2488
					links.push(...ruleLinks);
				}
				rules.push({
					itemName: ruleClass,
					itemLabel: repo,
					links,
				});
			}

			items.push({
				id: 'gbi-' + slugify(identifier),
				label: null,
				title: identifier,
				items: rules,
			});
		}

		return items;
	}

	switchToIdentifier() {
		this.groupByIdentifier(true);
		window.history.replaceState({}, '', '#gbi-');
	}

	switchToRule() {
		this.groupByIdentifier(false);
		window.history.replaceState({}, '', '#gbr-');
	}

}
