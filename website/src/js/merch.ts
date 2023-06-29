import $ from 'jquery';
import * as ko from 'knockout';
import { MerchSaleViewModel } from './MerchSaleViewModel';
import { MainMenuViewModel } from './MainMenuViewModel';

$(async () => {

	ko.options.deferUpdates = true;

	try {
		ko.applyBindings({
			hasFatalError: false,
			mainMenu: new MainMenuViewModel(),
			merchSale: new MerchSaleViewModel(),
		});
	} catch (e) {
		console.error(e);
		ko.applyBindings({
			hasFatalError: true,
			mainMenu: new MainMenuViewModel(),
		});
	}

});
