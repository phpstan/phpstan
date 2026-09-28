import $ from 'jquery';
import ko from '@tko/build.knockout';
import {MainMenuViewModel} from './MainMenuViewModel';

$(() => {
	ko.applyBindings({
		mainMenu: new MainMenuViewModel(),
	});

	if (document.querySelector('.line-chart') !== null) {
		import('./LineChart').then(({initLineCharts}) => initLineCharts());
	}

	if (document.querySelector('.type-inference-demo') !== null) {
		import('./TypeInferenceDemo').then(({initTypeInferenceDemos}) => initTypeInferenceDemos());
	}

	if (document.querySelector('.identifier-explorer') !== null) {
		import('./IdentifierExplorer').then(({initIdentifierExplorers}) => initIdentifierExplorers());
	}
});
