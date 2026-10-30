import $ from 'jquery';
import ko from '@tko/build.reference';
import {MerchSaleViewModel} from './MerchSaleViewModel';
import {initMainMenu} from './MainMenu';
import {mountMerchFatalError} from './Merch';
import {mountComponent} from './tsx';

$(async () => {
	ko.options.deferUpdates = true;

	initMainMenu();

	const urlParams = new URLSearchParams(window.location.search);

	try {
		mountComponent('merch', MerchSaleViewModel, urlParams.get('distributor'));
	} catch (e) {
		// eslint-disable-next-line no-console -- report the failure, the page shows a generic error
		console.error(e);
		mountMerchFatalError();
	}
});
