import * as ko from 'knockout';
import $ from 'jquery';
import * as countries from './data/countries.json';
import { loadStripe, Stripe, StripeCardElement } from '@stripe/stripe-js';
import { TShirtCartItem } from './TShirtCartItem';

export type TShirtType = {
	id: string,
	htmlClass: TShirtColor,
	name: string,
	price: number,
	style: TShirtStyle,
	sizes: TShirtSize[],
};

type Country = {
	country_code: string,
	country_name: string,
	phone_code: string,
}

type PaymentMethod = 'cc' | 'sepa';

export type TShirtSize = 'XS' | 'S' | 'M' | 'L' | 'XL' | 'XXL' | '3XL';
type TShirtColor = 'bg-white' | 'bg-blue-800';
type TShirtStyle = 'Straight' | 'Fitted';

type TShirtView = 'front' | 'back';

export class MerchSaleViewModel {

	canBuy: boolean;
	countries: Country[];
	tShirtTypes: TShirtType[];
	colors: TShirtColor[];
	styles: TShirtStyle[];
	sizes: ko.PureComputed<TShirtSize[]>;
	selectedTShirtColor: ko.Observable<TShirtColor>;
	selectedTShirtStyle: ko.Observable<TShirtStyle>;
	selectedTShirtSize: ko.Observable<TShirtSize | null>;
	selectedTShirtType: ko.PureComputed<TShirtType>;
	tShirtErrorMessage: ko.Observable<string | null>;
	tShirtSuccessMessage: ko.Observable<string | null>;
	badgeSetSuccessMessage: ko.Observable<string | null>;
	selectedTShirtView: ko.Observable<TShirtView>;
	cartTShirts: ko.ObservableArray<TShirtCartItem>;
	cartBadgeSetAmount: ko.Observable<number | string>;
	badgeSetAmountOptions: ko.PureComputed<(number | string)[]>;
	isCartEmpty: ko.PureComputed<boolean>;
	subtotalPrice: ko.PureComputed<number>;
	shippingPrice: ko.Observable<number>;
	shippingPriceXhr: ko.Observable<JQueryXHR | null>;
	shippingPriceLoading: ko.PureComputed<boolean>;
	totalPrice: ko.PureComputed<number>;

	billingName: ko.Observable<string>;
	billingSurname: ko.Observable<string>;
	billingCompany: ko.Observable<string>;
	billingStreet: ko.Observable<string>;
	billingHouseNumber: ko.Observable<string>;
	billingCity: ko.Observable<string>;
	billingZip: ko.Observable<string>;
	billingCountry: ko.Observable<string>;
	billingRegistrationNumber: ko.Observable<string>;
	billingVatId: ko.Observable<string>;

	deliveryAddressSameAsBillingAddress: ko.Observable<boolean>;
	deliveryName: ko.Observable<string>;
	deliverySurname: ko.Observable<string>;
	deliveryCompany: ko.Observable<string>;
	deliveryStreet: ko.Observable<string>;
	deliveryHouseNumber: ko.Observable<string>;
	deliveryCity: ko.Observable<string>;
	deliveryZip: ko.Observable<string>;
	deliveryCountry: ko.Observable<string>;

	email: ko.Observable<string>;
	phonePrefix: ko.Observable<string>;
	phoneNumber: ko.Observable<string>;

	isBillingCountryInSepa: ko.PureComputed<boolean>;
	selectedPaymentMethod: ko.Observable<PaymentMethod>;

	stripeLoading: ko.Observable<boolean>;
	stripePromise: Promise<Stripe | null> | null;
	cardElement: StripeCardElement | null;

	registrationNumberLabel: ko.PureComputed<'IČO' | 'Registration number'>;
	vatIdLabel: ko.PureComputed<'DIČ' | 'IČ DPH' | 'VAT ID'>;

	isConfirmingOrder: ko.Observable<boolean>;
	confirmOrderErrors: ko.ObservableArray<string>;
	agreeToPrivacyPolicy: ko.Observable<boolean>;
	agreeToTerms: ko.Observable<boolean>;

	successfulOrder: ko.Observable<boolean>;

	constructor() {
		countries.sort((a: Country, b: Country) => {
			return a.country_name.localeCompare(b.country_name);
		});
		const endDate = new Date(1637621999 * 1000).getTime(); // 'Nov 22, 2021 23:59:59' Europe/Prague
		this.canBuy = (endDate - (new Date().getTime())) > 0;

		this.countries = countries;
		this.tShirtTypes = [
			{id: 'be46f470-235d-4522-87fa-6df97da342f7', htmlClass: 'bg-blue-800', name: 'Blue T-Shirt (straight cut)', price: 25, style: 'Straight', sizes: ['S', 'M', 'L', 'XL', 'XXL', '3XL']},
			{id: 'e0582148-4155-4a70-bfb2-bc87c40c63f3', htmlClass: 'bg-white', name: 'White T-Shirt (straight cut)', price: 30, style: 'Straight', sizes: ['S', 'M', 'L', 'XL', 'XXL', '3XL']},
			{id: '196a52d2-d31a-44a7-8b43-9bba5743315a', htmlClass: 'bg-blue-800', name: 'Blue T-Shirt (fitted cut)', price: 25, style: 'Fitted', sizes: ['XS', 'S', 'M', 'L', 'XL']},
			{id: '86a2bb63-c8cd-4862-ad7a-5e5f389795d6', htmlClass: 'bg-white', name: 'White T-Shirt (fitted cut)', price: 30, style: 'Fitted', sizes: ['XS', 'S', 'M', 'L', 'XL', 'XXL']},
		];
		this.colors = ['bg-blue-800', 'bg-white'];
		this.styles = this.tShirtTypes.map((type) => {
			return type.style;
		});
		this.selectedTShirtColor = ko.observable<TShirtColor>('bg-blue-800');
		this.selectedTShirtStyle = ko.observable<TShirtStyle>('Straight');
		this.selectedTShirtSize = ko.observable(null);
		this.selectedTShirtType = ko.pureComputed(() => {
			const color = this.selectedTShirtColor();
			const style = this.selectedTShirtStyle();
			for (const type of this.tShirtTypes) {
				if (type.htmlClass === color && type.style === style) {
					return type;
				}
			}

			throw new Error('Undefined t-shirt');
		});
		this.sizes = ko.pureComputed(() => {
			const type = this.selectedTShirtType();

			return type.sizes;
		});
		this.sizes.subscribe((sizes) => {
			const selected = this.selectedTShirtSize();
			if (selected === null) {
				return;
			}

			if (sizes.indexOf(selected) === -1) {
				this.selectedTShirtSize(null);
			}
		});
		this.tShirtErrorMessage = ko.observable(null);
		this.tShirtSuccessMessage = ko.observable(null);
		this.badgeSetSuccessMessage = ko.observable(null);
		this.selectedTShirtView = ko.observable<TShirtView>('front');
		this.cartTShirts = ko.observableArray();
		this.cartTShirts.subscribe(() => {
			this.updateShippingPrice();
			this.updateLocalStorage();
			this.getStripe();
		});
		this.cartBadgeSetAmount = ko.observable<string | number>(0);
		this.cartBadgeSetAmount.subscribe((value) => {
			if (typeof value === 'string') {
				const newAmount = window.prompt('Please enter the number of badge sets:');
				if (newAmount === null || newAmount === '') {
					this.cartBadgeSetAmount(1);
					return;
				}

				const parsedAmount = parseInt(newAmount, 10);
				if (typeof parsedAmount !== 'number' || parsedAmount < 1 || isNaN(parsedAmount)) {
					this.cartBadgeSetAmount(1);
					return;
				}

				this.cartBadgeSetAmount(parsedAmount);
			}
			this.updateShippingPrice();
			this.updateLocalStorage();
			this.getStripe();
		});
		this.badgeSetAmountOptions = ko.pureComputed(() => {
			const currentAmount = this.cartBadgeSetAmount();
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
		this.isCartEmpty = ko.pureComputed(() => {
			return this.cartTShirts().length === 0 && this.cartBadgeSetAmount() === 0;
		});
		this.subtotalPrice = ko.pureComputed(() => {
			let price = 0;
			for (const item of this.cartTShirts()) {
				const itemAmount = item.amount();
				if (typeof itemAmount !== 'number') {
					continue;
				}
				price += itemAmount * item.tShirtType.price;
			}

			const badgeSetAmount = this.cartBadgeSetAmount();
			if (typeof badgeSetAmount === 'number') {
				price += badgeSetAmount * 9.0;
			}

			return Math.round((price + Number.EPSILON) * 100) / 100;
		});
		this.shippingPrice = ko.observable(0.0);
		this.shippingPriceXhr = ko.observable(null);
		this.shippingPriceLoading = ko.pureComputed(() => {
			return this.shippingPriceXhr() !== null;
		});
		this.totalPrice = ko.pureComputed(() => {
			return Math.round((this.subtotalPrice() + this.shippingPrice() + Number.EPSILON) * 100) / 100;
		});

		this.billingName = ko.observable('');
		this.billingName.subscribe(() => {
			this.updateLocalStorage();
		});

		this.billingSurname = ko.observable('');
		this.billingSurname.subscribe(() => {
			this.updateLocalStorage();
		});

		this.billingCompany = ko.observable('');
		this.billingCompany.subscribe(() => {
			this.updateLocalStorage();
		});

		this.billingStreet = ko.observable('');
		this.billingStreet.subscribe(() => {
			this.updateLocalStorage();
		});

		this.billingHouseNumber = ko.observable('');
		this.billingHouseNumber.subscribe(() => {
			this.updateLocalStorage();
		});

		this.billingCity = ko.observable('');
		this.billingCity.subscribe(() => {
			this.updateLocalStorage();
		});

		this.billingZip = ko.observable('');
		this.billingZip.subscribe(() => {
			this.updateLocalStorage();
		});

		this.billingCountry = ko.observable('');
		this.billingCountry.subscribe((value) => {
			this.updateLocalStorage();

			if (this.deliveryAddressSameAsBillingAddress()) {
				this.deliveryCountry(this.billingCountry());
			}

			if (this.phoneNumber() === '') {
				this.phonePrefix(value);
			}

			if (!this.isBillingCountryInSepa()) {
				this.selectedPaymentMethod('cc');
			}
		});

		this.billingRegistrationNumber = ko.observable('');
		this.billingRegistrationNumber.subscribe(() => {
			this.updateLocalStorage();
		});

		this.billingVatId = ko.observable('');
		this.billingVatId.subscribe(() => {
			this.updateLocalStorage();
		});

		this.deliveryAddressSameAsBillingAddress = ko.observable<boolean>(true);
		this.deliveryAddressSameAsBillingAddress.subscribe((value) => {
			this.updateLocalStorage();

			if (!value) {
				return;
			}

			this.deliveryName('');
			this.deliverySurname('');
			this.deliveryCompany('');
			this.deliveryStreet('');
			this.deliveryHouseNumber('');
			this.deliveryCity('');
			this.deliveryZip('');
			this.deliveryCountry(this.billingCountry());
		});

		this.deliveryName = ko.observable('');
		this.deliveryName.subscribe(() => {
			this.updateLocalStorage();
		});

		this.deliverySurname = ko.observable('');
		this.deliverySurname.subscribe(() => {
			this.updateLocalStorage();
		});

		this.deliveryCompany = ko.observable('');
		this.deliveryCompany.subscribe(() => {
			this.updateLocalStorage();
		});

		this.deliveryStreet = ko.observable('');
		this.deliveryStreet.subscribe(() => {
			this.updateLocalStorage();
		});

		this.deliveryHouseNumber = ko.observable('');
		this.deliveryHouseNumber.subscribe(() => {
			this.updateLocalStorage();
		});

		this.deliveryCity = ko.observable('');
		this.deliveryCity.subscribe(() => {
			this.updateLocalStorage();
		});

		this.deliveryZip = ko.observable('');
		this.deliveryZip.subscribe(() => {
			this.updateLocalStorage();
		});

		this.deliveryCountry = ko.observable('');
		this.deliveryCountry.subscribe(() => {
			this.updateLocalStorage();
			this.updateShippingPrice();
		});

		this.email = ko.observable('');
		this.email.subscribe(() => {
			this.updateLocalStorage();
		});

		this.phonePrefix = ko.observable('');
		this.phonePrefix.subscribe(() => {
			this.updateLocalStorage();
		});

		this.phoneNumber = ko.observable('');
		this.phoneNumber.subscribe(() => {
			this.updateLocalStorage();
		});

		this.isBillingCountryInSepa = ko.pureComputed(() => {
			const euCountries = ['AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HU', 'HR', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'];

			euCountries.push('NO'); // Norway
			euCountries.push('LI'); // Liechtenstein
			euCountries.push('IS'); // Iceland
			euCountries.push('CH'); // Switzerland
			euCountries.push('MC'); // Monaco
			euCountries.push('GB'); // United Kingdom
			euCountries.push('AD'); // Andorra
			euCountries.push('VA'); // Vatican
			euCountries.push('SM'); // San Marino
			euCountries.push('GI'); // Gibraltar
			euCountries.push('GG'); // Guernsey
			euCountries.push('JE'); // Jersey
			euCountries.push('IM'); // Isle of Man

			return euCountries.indexOf(this.billingCountry()) !== -1;
		});
		this.selectedPaymentMethod = ko.observable<PaymentMethod>('cc');
		this.selectedPaymentMethod.subscribe(() => {
			this.updateLocalStorage();
			this.confirmOrderErrors([]);
		});

		this.stripeLoading = ko.observable<boolean>(true);
		this.stripePromise = null;
		this.cardElement = null;

		this.registrationNumberLabel = ko.pureComputed(() => {
			const country = this.billingCountry();
			if (country === 'CZ' || country === 'SK') {
				return 'IČO';
			}

			return 'Registration number';
		});
		this.vatIdLabel = ko.pureComputed(() => {
			const country = this.billingCountry();
			if (country === 'CZ') {
				return 'DIČ';
			}
			if (country === 'SK') {
				return 'IČ DPH';
			}

			return 'VAT ID';
		});

		this.isConfirmingOrder = ko.observable<boolean>(false);
		this.confirmOrderErrors = ko.observableArray();
		this.agreeToPrivacyPolicy = ko.observable<boolean>(false);
		this.agreeToPrivacyPolicy.subscribe(() => {
			this.updateLocalStorage();
		});
		this.agreeToTerms = ko.observable<boolean>(false);
		this.agreeToTerms.subscribe(() => {
			this.updateLocalStorage();
		});

		this.successfulOrder = ko.observable<boolean>(false);

		this.restoreLocalStorage();
	}

	selectTShirtColor(color: TShirtColor): void {
		this.selectedTShirtColor(color);
		this.selectedTShirtView('front');
	}

	selectTShirtStyle(style: TShirtStyle): void {
		this.selectedTShirtStyle(style);
		this.selectedTShirtView('front');
	}

	selectTShirtSize(size: TShirtSize): void {
		this.tShirtErrorMessage(null);
		this.selectedTShirtSize(size);
	};

	addTShirtToCart(): void {
		const selectedTShirtSize = this.selectedTShirtSize();
		if (selectedTShirtSize === null) {
			this.tShirtErrorMessage('Please select your T-shirt size first.');
			this.tShirtSuccessMessage(null);
			return;
		}

		let existingItem = null;
		for (const item of this.cartTShirts()) {
			if (
				item.tShirtType.id === this.selectedTShirtType().id
				&& item.size === selectedTShirtSize
			) {
				existingItem = item;
				break;
			}
		}

		this.tShirtSuccessMessage('T-shirt added! Scroll to bottom to finish the order.');
		this.tShirtErrorMessage(null);
		if (existingItem !== null) {
			existingItem.increaseAmount();
			return;
		}

		const newItem = new TShirtCartItem(
			this.selectedTShirtType(),
			selectedTShirtSize,
			1,
			this,
		);

		this.cartTShirts.push(newItem);
	};

	addBadgeSetToCart(): void {
		const currentAmount = this.cartBadgeSetAmount();
		if (typeof currentAmount === 'string') {
			return;
		}
		this.cartBadgeSetAmount(currentAmount + 1);
		this.badgeSetSuccessMessage('Badges added! Scroll to bottom to finish the order.');
	}

	removeBadgeSetsFromCart(): void {
		this.badgeSetSuccessMessage(null);
		this.cartBadgeSetAmount(0);
	}

	removeTShirtFromCart(index: number): void {
		this.cartTShirts.splice(index, 1);
		this.tShirtSuccessMessage(null);
	}

	updateShippingPrice(): void {
		const oldXhr = this.shippingPriceXhr();
		if (oldXhr !== null) {
			oldXhr.abort();
		}
		if (this.cartTShirts().length === 0 && this.cartBadgeSetAmount() === 0) {
			this.shippingPriceXhr(null);
			this.shippingPrice(0.0);
			return;
		}

		const badgeSetAmount = this.cartBadgeSetAmount();

		const xhr = $.ajax({
			type: 'POST',
			url: 'https://merch-api.phpstan.org/shipping-price',
			contentType: 'application/json; charset=utf-8',
			dataType: 'json',
			data: JSON.stringify({
				country: this.deliveryCountry(),
				items: this.cartTShirts().map((item) => {
					const itemAmount = item.amount();
					return {
						id: item.tShirtType.id,
						size: item.size,
						amount: typeof itemAmount === 'number' ? itemAmount : 10,
					};
				}),
				badge_set_amount: typeof badgeSetAmount === 'number' ? badgeSetAmount : 10,
			}),
		});
		this.shippingPriceXhr(xhr);
		xhr.done((result) => {
			this.shippingPrice(Math.round((result.price + Number.EPSILON) * 100) / 100);
			this.shippingPriceXhr(null);
		});
		xhr.fail((reason) => {
			if (reason.statusText === 'abort') {
				return;
			}

			this.confirmOrderErrors(['Error occured while fetching the shipping price.']);
		});
	}

	updateLocalStorage(): void {
		try {
			const badgeSetAmount = this.cartBadgeSetAmount();
			const json = {
				items: this.cartTShirts().map((item) => {
					const itemAmount = item.amount();
					return {
						id: item.tShirtType.id,
						amount: typeof itemAmount === 'number' ? itemAmount : 10,
						size: item.size,
					};
				}),
				badgeSetAmount: typeof badgeSetAmount === 'number' ? badgeSetAmount : 10,
				billing: {
					name: this.billingName(),
					surname: this.billingSurname(),
					company: this.billingCompany(),
					street: this.billingStreet(),
					houseNumber: this.billingHouseNumber(),
					city: this.billingCity(),
					zip: this.billingZip(),
					country: this.billingCountry(),
					registrationNumber: this.billingRegistrationNumber(),
					vatId: this.billingVatId(),
				},
				deliveryAddressSameAsBillingAddress: this.deliveryAddressSameAsBillingAddress(),
				delivery: {
					name: this.deliveryName(),
					surname: this.deliverySurname(),
					company: this.deliveryCompany(),
					street: this.deliveryStreet(),
					houseNumber: this.deliveryHouseNumber(),
					city: this.deliveryCity(),
					zip: this.deliveryZip(),
					country: this.deliveryCountry(),
				},
				email: this.email(),
				phonePrefix: this.phonePrefix(),
				phoneNumber: this.phoneNumber(),
				paymentMethod: this.selectedPaymentMethod(),
				agreeToPrivacyPolicy: this.agreeToPrivacyPolicy(),
				agreeToTerms: this.agreeToTerms(),
			};
			window.localStorage.setItem('phpstan-merch', JSON.stringify(json));
		} catch (e) {
			// pass
		}
	}

	restoreLocalStorage(): void {
		try {
			const jsonString = window.localStorage.getItem('phpstan-merch');
			if (jsonString === null) {
				return;
			}

			const findTypeById = (id: string): TShirtType => {
				for (const type of this.tShirtTypes) {
					if (type.id === id) {
						return type;
					}
				}

				throw new Error('Undefined t-shirt');
			}

			const json = JSON.parse(jsonString);
			const items = json.items;
			const cartItems: TShirtCartItem[] = [];
			for (const item of items) {
				const type = findTypeById(item.id);
				cartItems.push(new TShirtCartItem(type, item.size, item.amount, this));
			}

			this.cartTShirts(cartItems);
			this.cartBadgeSetAmount(json.badgeSetAmount);
			this.deliveryAddressSameAsBillingAddress(json.deliveryAddressSameAsBillingAddress);

			this.billingName(json.billing.name);
			this.billingSurname(json.billing.surname);
			this.billingCompany(json.billing.company);
			this.billingStreet(json.billing.street);
			this.billingHouseNumber(json.billing.houseNumber);
			this.billingCity(json.billing.city);
			this.billingZip(json.billing.zip);
			this.billingCountry(json.billing.country);
			this.billingRegistrationNumber(json.billing.registrationNumber);
			this.billingVatId(json.billing.vatId);

			this.deliveryName(json.delivery.name);
			this.deliverySurname(json.delivery.surname);
			this.deliveryCompany(json.delivery.company);
			this.deliveryStreet(json.delivery.street);
			this.deliveryHouseNumber(json.delivery.houseNumber);
			this.deliveryCity(json.delivery.city);
			this.deliveryZip(json.delivery.zip);
			this.deliveryCountry(json.delivery.country);

			this.email(json.email);
			this.phonePrefix(json.phonePrefix);
			this.phoneNumber(json.phoneNumber);

			this.selectedPaymentMethod(json.paymentMethod);

			this.agreeToPrivacyPolicy(json.agreeToPrivacyPolicy);
			this.agreeToTerms(json.agreeToTerms);
		} catch (e) {
			// pass
		}
	}

	switchToBack(): void {
		this.selectedTShirtView('back');
	}

	switchToFront(): void {
		this.selectedTShirtView('front');
	}

	phonePrefixOptionText(value: Country): string {
		if (value.country_code === this.phonePrefix()) {
			return '+' + value.phone_code;
		}

		return value.country_name + ' (+' + value.phone_code + ')';
	}

	getStripe(): Promise<Stripe | null> {
		if (this.stripePromise === null) {
			return this.stripePromise = loadStripe('pk_live_51HKgooEsLWYRGjLPxKwP8MAV1zO9d1FqMjINH4m3G1DDhIhZbVbE0T1gpDI3yUUnf618OUjbTCLZwBnQUyKTav7M00SE7777dg').then((stripe) => {
				if (stripe === null) {
					this.stripeLoading(false);
					return null;
				}

				const style = {
					base: {
						color: '#32325d',
						fontFamily: 'Arial, sans-serif',
						fontSmoothing: 'antialiased',
						fontSize: '16px',
						'::placeholder': {
							color: '#32325d'
						}
					},
					invalid: {
						fontFamily: 'Arial, sans-serif',
						color: '#fa755a',
						iconColor: '#fa755a'
					}
				};
				const elements = stripe.elements({locale: 'en-GB'});
				const card = elements.create('card', { style });
				card.mount('#card-element');

				/* card.on("change", function (event) {
					// Disable the Pay button if there are no card details in the Element
					document.querySelector("button").disabled = event.empty;
					document.querySelector("#card-error").textContent = event.error ? event.error.message : "";
				}); */

				this.cardElement = card;
				this.stripeLoading(false);
				return stripe;
			});
		}

		return this.stripePromise;
	}

	getDataPayload(): any {
		let deliveryName = this.deliveryName();
		let deliverySurname = this.deliverySurname();
		let deliveryCompany = this.deliveryCompany() !== '' ? this.deliveryCompany() : null;
		let deliveryStreet = this.deliveryStreet();
		let deliveryStreetNumber = this.deliveryHouseNumber();
		let deliveryCity = this.deliveryCity();
		let deliveryZip = this.deliveryZip();
		let deliveryCountry = this.deliveryCountry();

		if (this.deliveryAddressSameAsBillingAddress()) {
			deliveryName = this.billingName();
			deliverySurname = this.billingSurname();
			deliveryCompany = this.billingCompany() !== '' ? this.billingCompany() : null;
			deliveryStreet = this.billingStreet();
			deliveryStreetNumber = this.billingHouseNumber();
			deliveryCity = this.billingCity();
			deliveryZip = this.billingZip();
			deliveryCountry = this.billingCountry();
		}

		const phoneCountry = this.phonePrefix();
		let phonePrefix = null;
		for (const country of this.countries) {
			if (phoneCountry === country.country_code) {
				phonePrefix = country.phone_code;
				break;
			}
		}

		if (phonePrefix === null) {
			throw new Error('Undefined phone prefix');
		}

		return {
			email: this.email(),
			billing_name: this.billingName(),
			billing_surname: this.billingSurname(),
			billing_company: this.billingCompany() !== '' ? this.billingCompany() : null,
			billing_street: this.billingStreet(),
			billing_street_number: this.billingHouseNumber(),
			billing_city: this.billingCity(),
			billing_zip: this.billingZip(),
			billing_country: this.billingCountry(),
			billing_registration_number: this.billingRegistrationNumber() !== '' ? this.billingRegistrationNumber() : null,
			billing_vat_id: this.billingVatId() !== '' ? this.billingVatId() : null,
			delivery_name: deliveryName,
			delivery_surname: deliverySurname,
			delivery_company: deliveryCompany,
			delivery_street: deliveryStreet,
			delivery_street_number: deliveryStreetNumber,
			delivery_city: deliveryCity,
			delivery_zip: deliveryZip,
			delivery_country: deliveryCountry,
			delivery_phone: '+' + phonePrefix + this.phoneNumber(),
			items: this.cartTShirts().map((item) => {
				return {
					id: item.tShirtType.id,
					size: item.size,
					amount: item.amount(),
				};
			}),
			badge_set_amount: this.cartBadgeSetAmount(),
			total_price: this.totalPrice(),
		};
	}

	confirmOrder(): void {
		if (this.selectedPaymentMethod() === 'cc') {
			this.confirmCreditCardOrder();
			return;
		}

		this.confirmSepaOrder();
	}

	async confirmCreditCardOrder(): Promise<void> {
		if (this.shippingPriceLoading()) {
			return;
		}
		const stripe = await this.getStripe();
		if (stripe === null) {
			return;
		}

		if (this.cardElement === null) {
			return;
		}

		this.confirmOrderErrors([]);

		if (!this.validateForm()) {
			return;
		}

		const card = this.cardElement;

		this.isConfirmingOrder(true);
		$.ajax({
			type: 'POST',
			url: 'https://merch-api.phpstan.org/create-payment-intent',
			contentType: 'application/json; charset=utf-8',
			dataType: 'json',
			data: JSON.stringify(this.getDataPayload()),
		}).done((paymentIntentResult) => {
			stripe.confirmCardPayment(paymentIntentResult.clientSecret, {
				payment_method: {
					card,
				}
			}).then((confirmResult) => {
				this.isConfirmingOrder(false);
				if (confirmResult.error) {
					if (typeof confirmResult.error.message !== 'undefined' && confirmResult.error.message !== null) {
						this.confirmOrderErrors([confirmResult.error.message]);
						return;
					}

					this.confirmOrderErrors(['Error occured while finishing the order.']);
					return;
				}

				this.markOrderAsSuccessful();
			});
		}).fail((response) => {
			this.isConfirmingOrder(false);
			if (typeof response.responseJSON !== 'undefined') {
				this.confirmOrderErrors(response.responseJSON.errors);
				return;
			}

			this.confirmOrderErrors(['Error occured while finishing the order.']);
		});
	}

	confirmSepaOrder(): void {
		if (this.shippingPriceLoading()) {
			return;
		}

		this.confirmOrderErrors([]);

		if (!this.validateForm()) {
			return;
		}

		this.isConfirmingOrder(true);
		$.ajax({
			type: 'POST',
			url: 'https://merch-api.phpstan.org/sepa',
			contentType: 'application/json; charset=utf-8',
			dataType: 'json',
			data: JSON.stringify(this.getDataPayload()),
		}).done((result) => {
			this.markOrderAsSuccessful();
		}).fail((response) => {
			if (typeof response.responseJSON !== 'undefined') {
				this.confirmOrderErrors(response.responseJSON.errors);
				return;
			}

			this.confirmOrderErrors(['Error occured while finishing the order.']);
		}).always(() => {
			this.isConfirmingOrder(false);
		});
	}

	markOrderAsSuccessful(): void {
		this.successfulOrder(true);

		const anyWindow = (window as any);
		if (typeof anyWindow.fathom !== 'undefined') {
			anyWindow.fathom.trackGoal('DPFY11RI', this.totalPrice() * 100);
		}

		try {
			window.localStorage.removeItem('phpstan-merch');
		} catch (e) {
			// pass
		}
	}

	validateForm(): boolean {
		const errors = [];

		if (this.billingName().trim().length === 0) {
			errors.push('Please fill in your first name.');
		}
		if (this.billingSurname().trim().length === 0) {
			errors.push('Please fill in your last name.');
		}
		if (this.billingStreet().trim().length === 0) {
			errors.push('Please fill in your street.');
		}
		if (this.billingHouseNumber().trim().length === 0) {
			errors.push('Please fill in your house number.');
		}
		if (this.billingCity().trim().length === 0) {
			errors.push('Please fill in your city.');
		}
		if (this.billingZip().trim().length === 0) {
			errors.push('Please fill in postal code.');
		}

		if (!this.deliveryAddressSameAsBillingAddress()) {
			if (this.deliveryName().trim().length === 0) {
				errors.push('Please fill in your first name.');
			}
			if (this.deliverySurname().trim().length === 0) {
				errors.push('Please fill in your last name.');
			}
			if (this.deliveryStreet().trim().length === 0) {
				errors.push('Please fill in your street.');
			}
			if (this.deliveryHouseNumber().trim().length === 0) {
				errors.push('Please fill in your house number.');
			}
			if (this.deliveryCity().trim().length === 0) {
				errors.push('Please fill in your city.');
			}
			if (this.deliveryZip().trim().length === 0) {
				errors.push('Please fill in postal code.');
			}
		}

		if (this.email().trim().length === 0) {
			errors.push('Please fill in your email address.');
		}

		if (this.phoneNumber().trim().length === 0) {
			errors.push('Please fill in your phone number.');
		}

		if (!this.agreeToPrivacyPolicy()) {
			errors.push('Agreement to Privacy Policy is required.');
		}

		if (!this.agreeToTerms()) {
			errors.push('Agreement to Terms & Conditions is required.');
		}

		for (const item of this.cartTShirts()) {
			const amount = item.amount();
			if (typeof amount !== 'number') {
				errors.push('An item has an invalid amount.');
			}
		}

		this.confirmOrderErrors(errors);

		return errors.length === 0;
	}
}
