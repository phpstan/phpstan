import $ from 'jquery';
import ko from '@tko/build.reference';
import {MerchSaleViewModel} from './MerchSaleViewModel';
import {initMainMenu} from './MainMenu';
import {mountMerch, mountMerchFatalError} from './Merch';

$(async () => {
	ko.options.deferUpdates = true;

	initMainMenu();

	const urlParams = new URLSearchParams(window.location.search);

	try {
		mountMerch(new MerchSaleViewModel(urlParams.has('distributor') ? urlParams.get('distributor') : null));
	} catch (e) {
		// eslint-disable-next-line no-console -- report the failure, the page shows a generic error
		console.error(e);
		mountMerchFatalError();
	}
});
