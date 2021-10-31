import $ from 'jquery';
import * as ko from 'knockout';
import { MerchSaleViewModel } from './MerchSaleViewModel';
import { MainMenuViewModel } from './MainMenuViewModel';
import { MerchBannerViewModel } from './MerchBannerViewModel';

$(async () => {

	ko.options.deferUpdates = true;

	try {
		ko.applyBindings({
			hasFatalError: false,
			mainMenu: new MainMenuViewModel(),
			merchSale: new MerchSaleViewModel(),
			merchBanner: new MerchBannerViewModel(true),
		});
	} catch (e) {
		console.log(e);
		ko.applyBindings({
			hasFatalError: true,
			mainMenu: new MainMenuViewModel(),
		});
	}

});
