import $ from 'jquery';
import {initMainMenu} from './MainMenu';

$(() => {
	initMainMenu();

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
