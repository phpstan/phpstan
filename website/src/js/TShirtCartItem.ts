import * as ko from 'knockout';
import { MerchSaleViewModel, TShirtSize, TShirtType } from './MerchSaleViewModel';

export class TShirtCartItem {
	amount: ko.Observable<number | string>;
	amountOptions: ko.PureComputed<(number | string)[]>;
	constructor(
		public tShirtType: TShirtType,
		public size: TShirtSize,
		amount: number,
		viewModel: MerchSaleViewModel,
	) {
		this.amount = ko.observable<number | string>(amount);
		this.amount.subscribe((value) => {
			if (typeof value === 'string') {
				const newAmount = window.prompt('Please enter the number of t-shirts:');
				if (newAmount === null || newAmount === '') {
					this.amount(1);
					return;
				}

				const parsedAmount = parseInt(newAmount, 10);
				if (typeof parsedAmount !== 'number' || parsedAmount < 1 || isNaN(parsedAmount)) {
					this.amount(1);
					return;
				}

				this.amount(parsedAmount);
			}
			viewModel.updateShippingPrice();
			viewModel.updateLocalStorage();
		});
		this.amountOptions = ko.pureComputed(() => {
			const currentAmount = this.amount();
			let maxAmount = 10;
			if (typeof currentAmount === 'number') {
				maxAmount = Math.max(currentAmount, 10);
			}

			const options = [];
			for (let i = 1; i <= maxAmount; i++) {
				options.push(i);
			}

			options.push('More…');

			return options;
		});
	}

	increaseAmount(): void {
		const currentAmount = this.amount();
		if (typeof currentAmount === 'string') {
			return;
		}

		this.amount(currentAmount + 1);
	}
}
