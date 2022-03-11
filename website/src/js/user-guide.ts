import $ from 'jquery';
import * as ko from 'knockout';
import {MainMenuViewModel} from './MainMenuViewModel';
import {IgnoreErrorsWidgetModel} from './IgnoreErrorsWidgetModel';
import Prism from 'prismjs';
import 'prismjs/components/prism-yaml';

Prism.manual = true;

$(() => {
	ko.applyBindings({
		mainMenu: new MainMenuViewModel(),
		ignoreErrors: new IgnoreErrorsWidgetModel(),
	});
});
