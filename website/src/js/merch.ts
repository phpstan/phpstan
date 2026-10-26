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
		// eslint-disable-next-line no-console -- report the failure, the page shows a generic error
		console.error(e);
		ko.applyBindings({
			hasFatalError: true,
			mainMenu: new MainMenuViewModel(),
		});
	}

});
