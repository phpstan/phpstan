import $ from 'jquery';
import ko from '@tko/build.knockout';
import { MerchSaleViewModel } from './MerchSaleViewModel';
import { MainMenuViewModel } from './MainMenuViewModel';

$(async () => {

	ko.options.deferUpdates = true;

	const urlParams = new URLSearchParams(window.location.search);

	try {
		ko.applyBindings({
			hasFatalError: false,
			mainMenu: new MainMenuViewModel(),
			merchSale: new MerchSaleViewModel(urlParams.has('distributor') ? urlParams.get('distributor') : null),
		});
	} catch (e) {
		console.error(e);
		ko.applyBindings({
			hasFatalError: true,
			mainMenu: new MainMenuViewModel(),
		});
	}

});
