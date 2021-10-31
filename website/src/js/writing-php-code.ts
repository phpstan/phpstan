import $ from 'jquery';
import * as ko from 'knockout';
import {MainMenuViewModel} from './MainMenuViewModel';
import { MerchBannerViewModel } from './MerchBannerViewModel';

$(() => {
	ko.applyBindings({
		mainMenu: new MainMenuViewModel(),
		merchBanner: new MerchBannerViewModel(false),
	});
});
