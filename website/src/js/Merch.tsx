import ko from '@tko/build.reference';
import type {MerchSaleViewModel, TShirtType} from './MerchSaleViewModel';
import {mount, when} from './tsx';

// Imported so that Vite fingerprints them, as it does for <img> tags in HTML.
const images = import.meta.glob<string>(['../images/elephpant*.jpg', '../images/tshirt-2024-*.jpg'], {eager: true, import: 'default'});

function image(name: string): string {
	return images[`../images/${name}.jpg`];
}

function tShirtImage(type: TShirtType, view: string): string {
	const color = type.htmlClass === 'bg-white' ? 'white' : 'blue';
	return image(`tshirt-2024-${color}${view}-${type.style.toLowerCase()}`);
}

function tShirtAlt(type: TShirtType): string {
	return type.htmlClass === 'bg-white' ? 'White T-shirt' : 'Blue T-shirt';
}

// The template of MerchSaleViewModel.
export function merchTemplate(vm: MerchSaleViewModel): JSX.Element {
	const tShirtType = vm.selectedTShirtType;

	return (
		<div>
			{vm.canBuy
				? null
				: (
						<div class="text-sm px-3 py-2 mt-1 mb-3 text-gray-500">
							<div class="text-center">The sale has ended. If you have any questions about your order, send an email to <a href="mailto:merch@phpstan.com" class="underline hover:no-underline">merch@phpstan.com</a>.</div>
						</div>
					)}

			<div ko-visible={vm.successfulOrder}>
				<div class="rounded-md bg-green-50 p-4 mx-auto w-3/4 mt-8">
					<div class="flex">
						<div class="flex-shrink-0">
							{checkCircleIcon()}
						</div>
						<div class="ml-3">
							<h3 class="text-lg font-medium text-green-800">Order completed</h3>
							<div class="mt-2 text-md text-green-700">
								<p>Thank you for your purchase. We really appreciate your support!</p>
								<p ko-visible={ko.pureComputed(() => vm.selectedPaymentMethod() === 'cc')}>You will receive an email with the invoice and order summary.</p>
								<p ko-visible={ko.pureComputed(() => vm.selectedPaymentMethod() === 'sepa')}>You will receive an email with SEPA payment details and order summary.</p>
								<div class="mt-4">
									<div class="-mx-2 -my-1.5 flex">
										<a href="/merch" class="block bg-green-50 px-2 py-1.5 rounded-md text-sm font-medium text-green-800 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-green-50 focus:ring-green-600">Continue shopping</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div ko-visible={ko.pureComputed(() => !vm.successfulOrder())}>
				<div class="lg:grid lg:grid-cols-2 lg:gap-x-8 lg:items-start mt-16">
					{/* Image gallery */}
					<div class="px-4 hidden lg:block">
						<img src={ko.pureComputed(() => image(`elephpant_${vm.selectedElephpantView()}`))} alt="PHPStan elephpant" class="mx-auto w-full h-full px-6 object-center object-cover rounded-lg" />
					</div>
					{/* Product info */}
					<div class="mt-10 px-4 lg:mt-0">
						<h1 class="text-3xl font-extrabold tracking-tight text-gray-900">PHPStan Elephpant</h1>

						<div class="mt-6">
							<h3 class="sr-only">Description</h3>

							<div class="text-base text-gray-700 space-y-6">
								<p>It's finally here! A new member of the elephpant family.</p>
								<p>This adorable elephpant holds a magnifying glass, just like in the logo. One leg features a green checkmark to celebrate error-free analysis, while the other shows a red cross to mark those moments when issues are found and your build fails.</p>

								<p class="block lg:hidden">PHPStan elephant logo on the front, text on the back of the neck.</p>

								{when(() => vm.selectedElephpantView() === 'right', () => (
									<p class="hidden lg:block">It also has the PHPStan logo on one side, and PHP logo <a href="#" class="underline hover:no-underline" ko-click={() => vm.switchElephpantToLeft()}>on the other</a>.</p>
								))}

								{when(() => vm.selectedElephpantView() === 'left', () => (
									<p class="hidden lg:block">It also has the PHPStan logo <a href="#" class="underline hover:no-underline" ko-click={() => vm.switchElephpantToRight()}>on one side</a>, and PHP logo on the other.</p>
								))}

								<p>You'll get a set of four PHPStan stickers with each elephpant for free!</p>
							</div>
						</div>

						<form class="mt-6" ko-submit={() => vm.addElephpantToCart()}>
							{/* Image gallery */}
							<div class="mt-8 block lg:hidden">
								<img src={image('elephpant')} alt="PHPStan elephpant" class="mx-auto w-full h-full object-center object-cover rounded-lg" />
							</div>

							<div class="mt-6">
								<p class="text-3xl text-gray-900">30 €</p>
							</div>

							{when(() => vm.elephpantSuccessMessage() !== null, () => successMessage(vm.elephpantSuccessMessage))}

							<div class="mt-3 flex sm:flex-col1">
								{vm.canBuy ? addToCartButton() : null}
							</div>
						</form>
					</div>
				</div>
				<div class="lg:grid lg:grid-cols-2 lg:gap-x-8 lg:items-start mt-24">
					{/* Image gallery */}
					<div class="px-4 hidden lg:block">
						<div class="w-full aspect-[1410/1600]">
							<img src={ko.pureComputed(() => tShirtImage(tShirtType(), `-${vm.selectedTShirtView()}`))} alt={ko.pureComputed(() => tShirtAlt(tShirtType()))} class="w-full h-full object-center object-cover rounded-lg" />
						</div>
					</div>
					{/* Product info */}
					<div class="mt-10 px-4 lg:mt-0">
						<h1 class="text-3xl font-extrabold tracking-tight text-gray-900">PHPStan T-Shirt</h1>

						<div class="mt-6">
							<h3 class="sr-only">Description</h3>

							<div class="text-base text-gray-700 space-y-6">
								<p class="block lg:hidden">PHPStan elephant logo on the front, text on the back of the neck.</p>

								{when(() => vm.selectedTShirtView() === 'front', () => (
									<p class="hidden lg:block">PHPStan elephant logo on the front, text <a href="#" class="underline hover:no-underline" ko-click={() => vm.switchTShirtToBack()}>on the back</a> of the neck.</p>
								))}

								{when(() => vm.selectedTShirtView() === 'back', () => (
									<p class="hidden lg:block">PHPStan elephant logo <a href="#" class="underline hover:no-underline" ko-click={() => vm.switchTShirtToFront()}>on the front</a>, text on the back of the neck.</p>
								))}

								<p>Made from certified organic cotton with two colors to choose from: Navy blue T-shirt with white PHPStan logo, or white T-shirt with blue PHPStan logo.</p>

								<p>You'll also get a set of four PHPStan stickers with each T-shirt for free!</p>
							</div>
						</div>

						<form class="mt-6" ko-submit={() => vm.addTShirtToCart()}>
							{/* Colors */}
							<div class="mt-8">
								<h3 class="text-sm text-gray-600">Color</h3>

								<fieldset class="mt-2">
									<legend class="sr-only">Choose a color</legend>
									<div class="flex items-center space-x-3">
										{vm.colors.map((color) => (
											<label class="exclude-from-dark -m-0.5 relative p-0.5 rounded-full flex items-center justify-center cursor-pointer focus:outline-none ring-gray-500" ko-click={() => vm.selectTShirtColor(color)} ko-css={{'ring ring-offset-1': ko.pureComputed(() => tShirtType().htmlClass === color)}}>
												<input type="radio" name="color-choice" value="White" class="sr-only" aria-labelledby="color-choice-0-label" />
												<p id="color-choice-0-label" class="sr-only"></p>
												<span aria-hidden="true" class={`h-8 w-8 border border-black border-opacity-10 rounded-full ${color}`}></span>
											</label>
										))}
									</div>
								</fieldset>
							</div>

							<div class="mt-8">
								<h3 class="text-sm text-gray-600">Style</h3>
								{/* Style selector */}
								<fieldset>
									<legend class="sr-only">Choose the style</legend>
									<div class="mt-1 grid gap-4 grid-cols-2">
										<label class="relative block border border-gray-300 rounded-lg p-4 cursor-pointer focus:outline-none" ko-css={{'ring-2 ring-indigo-500': ko.pureComputed(() => tShirtType().style === 'Straight')}} ko-click={() => vm.selectTShirtStyle('Straight')}>
											<input type="radio" name="size-choice" value="18L" class="sr-only" aria-labelledby="size-choice-0-label" aria-describedby="size-choice-0-description" />
											<p id="size-choice-0-label" class="text-base font-medium text-gray-900">Straight cut</p>
											<p id="size-choice-0-description" class="mt-1 text-sm text-gray-500">Also known as "men's"</p>
										</label>

										<label class="relative block border border-gray-300 rounded-lg p-4 cursor-pointer focus:outline-none" ko-css={{'ring-2 ring-indigo-500': ko.pureComputed(() => tShirtType().style === 'Fitted')}} ko-click={() => vm.selectTShirtStyle('Fitted')}>
											<input type="radio" name="size-choice" value="20L" class="sr-only" aria-labelledby="size-choice-1-label" aria-describedby="size-choice-1-description" />
											<p id="size-choice-1-label" class="text-base font-medium text-gray-900">Fitted cut</p>
											<p id="size-choice-1-description" class="mt-1 text-sm text-gray-500">Also known as "women's"</p>
										</label>
									</div>
								</fieldset>
							</div>

							{/* Image gallery */}
							<div class="mt-8 block lg:hidden">
								<div class="w-full aspect-[1610/913]">
									<img src={ko.pureComputed(() => tShirtImage(tShirtType(), ''))} alt={ko.pureComputed(() => tShirtAlt(tShirtType()))} class="w-full h-full object-center object-cover rounded-lg" />
								</div>
							</div>

							{/* Size picker */}
							<div class="mt-8">
								<div class="flex items-center justify-between">
									<h3 class="text-sm text-gray-600">Size</h3>
									<div class="flex flex-grow justify-end">
										<a href={ko.pureComputed(() => tShirtType().style === 'Straight' ? '/images/malfini-sizing-chart.pdf' : '/images/malfini-sizing-chart-fitted.pdf')} target="_blank" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Sizing chart</a>
									</div>
								</div>

								<fieldset class="mt-2">
									<legend class="sr-only">Choose a size</legend>
									<div class="grid grid-cols-3 gap-3 sm:grid-cols-6">
										{ko.pureComputed(() => vm.sizes().map((size) => (
											<label class={`border rounded-md py-3 px-3 flex items-center justify-center text-sm font-medium uppercase sm:flex-1 cursor-pointer focus:outline-none ${vm.selectedTShirtSize() === size ? 'bg-indigo-600 border-transparent text-white ring-2 ring-offset-2 ring-indigo-500' : 'bg-white border-gray-200 text-gray-900 hover:bg-gray-50'}`} ko-click={() => vm.selectTShirtSize(size)}>
												<input type="radio" name="size-choice" class="sr-only" aria-labelledby="size-choice-0-label" value={size} />
												<p id="size-choice-0-label">{size}</p>
											</label>
										)))}
									</div>
								</fieldset>
							</div>

							<div class="mt-6">
								<p class="text-3xl text-gray-900" ko-text={ko.pureComputed(() => tShirtType().price + ' €')}></p>
							</div>

							{when(() => vm.tShirtErrorMessage() !== null, () => errorMessage(vm.tShirtErrorMessage))}

							{when(() => vm.tShirtSuccessMessage() !== null, () => successMessage(vm.tShirtSuccessMessage))}

							<div class="mt-3 flex sm:flex-col1">
								{vm.canBuy ? addToCartButton() : null}
							</div>
						</form>
					</div>
				</div>

				<h2 class="sr-only">Checkout</h2>

				{vm.canBuy
					? <div class="mx-auto max-w-lg text-center mt-10 mb-10 text-gray-600 text-sm" ko-visible={vm.isCartEmpty}>Cart will appear here once you add something to it.</div>
					: <div class="mx-auto max-w-lg text-center mt-10 mb-10 text-gray-600 text-sm">The sale has ended. If you have any questions about your order, send an email to <a href="mailto:merch@phpstan.com" class="underline hover:no-underline">merch@phpstan.com</a>.</div>}

				{checkout(vm)}
			</div>
		</div>
	);
}

export function mountMerchFatalError(): void {
	mount('merch', (
		<div>
			<div class="rounded-md bg-red-50 p-4 mt-8">
				<div class="flex">
					<div class="flex-shrink-0">
						{xCircleIcon()}
					</div>
					<div class="ml-3">
						<h3 class="text-sm font-medium text-red-800">A fatal error occurred during initialization. Please contact us at <a href="mailto:merch@phpstan.com" class="underline hover:no-underline">merch@phpstan.com</a></h3>
					</div>
				</div>
			</div>
		</div>
	));
}

function checkout(vm: MerchSaleViewModel): JSX.Element {
	return (
		<form class="lg:grid lg:grid-cols-2 lg:gap-x-12 xl:gap-x-16 mt-16" ko-visible={ko.pureComputed(() => !vm.isCartEmpty() && vm.canBuy)} ko-submit={() => vm.confirmOrder()}>
			{/* Order summary */}
			<div class="mt-10 lg:mt-0 px-4">
				<h2 class="text-lg font-medium text-gray-900">Order summary</h2>

				<div class="mt-4 bg-white border border-gray-200 rounded-lg shadow-sm">
					<h3 class="sr-only">Items in your cart</h3>
					<ul role="list" class="divide-y divide-gray-200">
						{ko.pureComputed(() => vm.cartTShirts().map((item, index) => (
							<li class="flex py-6 px-4 sm:px-6">
								<div class="flex-shrink-0">
									<img src={tShirtImage(item.tShirtType, '-front')} alt={tShirtAlt(item.tShirtType)} class="w-20 rounded-md" />
								</div>

								<div class="ml-6 flex-1 flex flex-col">
									<div class="flex">
										<div class="min-w-0 flex-1">
											<h4 class="text-sm font-medium text-gray-700">{item.tShirtType.name}</h4>
											<p class="mt-1 text-sm text-gray-500">Size {item.size}</p>
											<p class="mt-1 text-sm text-gray-500">Free stickers</p>
										</div>

										<div class="ml-4 flex-shrink-0 flow-root">
											{removeButton(() => vm.removeTShirtFromCart(index))}
										</div>
									</div>

									<div class="flex-1 pt-2 flex items-center justify-between">
										<p class="text-sm font-medium text-gray-900">{item.tShirtType.price + ' €'}</p>

										<div class="ml-4">
											{quantitySelect(item.amountOptions, item.amount)}
										</div>
									</div>
								</div>
							</li>
						)))}

						{when(() => {
							const amount = vm.cartElephpantAmount();
							return typeof amount === 'number' && amount > 0;
						}, () => (
							<li class="flex py-6 px-4 sm:px-6">
								<div class="flex-shrink-0">
									<img src={image('elephpant_right')} alt="PHPStan elephpant" class="w-20 rounded-md" />
								</div>

								<div class="ml-6 flex-1 flex flex-col">
									<div class="flex">
										<div class="min-w-0 flex-1">
											<h4 class="text-sm font-medium text-gray-700">PHPStan elephpant</h4>
											<p class="mt-1 text-sm text-gray-500">Free stickers</p>
										</div>

										<div class="ml-4 flex-shrink-0 flow-root">
											{removeButton(() => vm.removeElephpantFromCart())}
										</div>
									</div>

									<div class="flex-1 pt-2 flex items-center justify-between">
										<p class="text-sm font-medium text-gray-900">30 €</p>

										<div class="ml-4">
											{quantitySelect(vm.elephpantAmountOptions, vm.cartElephpantAmount)}
										</div>
									</div>
								</div>
							</li>
						))}
					</ul>
					<dl class="border-t border-gray-200 py-6 px-4 space-y-6 sm:px-6">{priceSummary(vm)}</dl>
				</div>
			</div>

			<div class="mt-10 lg:mt-0 px-4 lg:px-0">
				<div>
					<h2 class="text-lg font-medium text-gray-900">Billing information</h2>

					<div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
						{textField('billing-first-name', 'First name', vm.billingName, 'given-name')}
						{textField('billing-last-name', 'Last name', vm.billingSurname, 'family-name')}
						{textField('billing-company', 'Company', vm.billingCompany, 'organization', 'sm:col-span-2')}
						{textField('billing-street-address', 'Street', vm.billingStreet, 'street-address')}
						{textField('billing-house-number', 'House number', vm.billingHouseNumber)}
						{textField('billing-city', 'City', vm.billingCity, undefined, 'sm:col-span-2')}
						{textField('billing-postal-code', 'Postal code', vm.billingZip, 'postal-code')}
						{countryField('billing-country', vm, vm.billingCountry)}
						{textField('billing-registration-number', vm.registrationNumberLabel, vm.billingRegistrationNumber)}
						{textField('billing-vat-id', vm.vatIdLabel, vm.billingVatId)}
					</div>
				</div>

				<div class="mt-10 border-t border-gray-200 pt-10">
					<h2 class="text-lg font-medium text-gray-900">Delivery address</h2>

					<div class="mt-4 flex items-center" ko-visible={ko.pureComputed(() => vm.distributor() === null && vm.distributorError() === null)}>
						<input id="same-as-billing" name="same-as-billing" type="checkbox" class="h-4 w-4 border-gray-300 rounded text-indigo-600 focus:ring-indigo-500" ko-checked={vm.deliveryAddressSameAsBillingAddress} />
						<div class="ml-2">
							<label for="same-as-billing" class="text-sm font-medium text-gray-900">Same as billing address</label>
						</div>
					</div>

					{when(() => vm.distributorError() !== null, () => (
						<div class="rounded-md bg-red-50 p-4 mt-4">
							<div class="flex">
								<div class="flex-shrink-0">
									{xCircleIcon()}
								</div>
								<div class="ml-3">
									<h3 class="text-sm font-medium text-red-800" ko-text={vm.distributorError}></h3>
									<button class="w-full bg-gray-300 border border-gray-300 shadow-sm rounded-md shadow-sm py-2 px-3 mt-2 text-sm font-medium text-gray-700 hover:bg-gray-200" ko-click={() => vm.resetDistributor()}>Ship to my address</button>
								</div>
							</div>
						</div>
					))}

					{ko.pureComputed(() => {
						const distributor = vm.distributor();
						if (distributor === null) {
							return null;
						}

						return (
							<div class="rounded-md bg-gray-200 p-4 mt-4">
								<h3 class="text-sm font-bold text-gray-800">Your order will ship to:</h3>
								<p class="text-sm mt-1">{distributor.name + ', ' + distributor.country}</p>
								<p class="text-sm mt-4">You will be contacted with details how to pick up your order once the distributor receives it.</p>
								<p class="text-sm mt-4">Shipping is free. There might be a small additional charge because of import (customs) duty in your country.</p>
								<button class="mt-4 w-full bg-gray-300 border border-gray-400 shadow-sm rounded-md shadow-sm py-2 px-3 mt-2 text-sm font-medium text-gray-700 hover:bg-gray-200" ko-click={() => vm.resetDistributor()}>Ship to my address instead</button>
							</div>
						);
					})}

					<div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4" ko-visible={ko.pureComputed(() => !vm.deliveryAddressSameAsBillingAddress() && vm.distributor() === null && vm.distributorError() === null)}>
						{textField('delivery-first-name', 'First name', vm.deliveryName, 'given-name')}
						{textField('delivery-last-name', 'Last name', vm.deliverySurname, 'family-name')}
						{textField('delivery-company', 'Company', vm.deliveryCompany, 'organization', 'sm:col-span-2')}
						{textField('delivery-street-address', 'Street', vm.deliveryStreet, 'street-address')}
						{textField('delivery-house-number', 'House number', vm.deliveryHouseNumber)}
						{textField('delivery-city', 'City', vm.deliveryCity, undefined, 'sm:col-span-2')}
						{textField('delivery-postal-code', 'Postal code', vm.deliveryZip, 'postal-code')}
						{countryField('delivery-country', vm, vm.deliveryCountry)}
					</div>
				</div>

				<div class="mt-10 border-t border-gray-200 pt-10">
					<h2 class="text-lg font-medium text-gray-900">Contact information</h2>

					<div class="mt-4 grid grid-cols-3 gap-y-6 gap-x-4">
						<div class="col-span-3">
							<label for="email-address" class="block text-sm font-medium text-gray-700">Email address</label>
							<div class="mt-1">
								<input type="email" id="email-address" name="email-address" autocomplete="email" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" ko-value={vm.email} />
							</div>
						</div>

						<div>
							<label for="phone-number" class="block text-sm font-medium text-gray-700">Phone</label>
							<div class="mt-1">
								<select id="phone-prefix" name="phone-prefix" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" ko-options={vm.countries} ko-optionsText={(country) => vm.phonePrefixOptionText(country)} ko-optionsValue="country_code" ko-value={vm.phonePrefix}></select>
							</div>
						</div>

						<div class="col-span-2">
							<span class="block text-sm font-normal text-gray-500">Only for delivery purposes</span>
							<div class="mt-1">
								<input type="tel" name="phone-number" id="phone-number" autocomplete="tel" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" ko-value={vm.phoneNumber} />
							</div>
						</div>
					</div>
				</div>

				{/* Payment */}
				<div class="mt-10 border-t border-gray-200 pt-10">
					<h2 class="text-lg font-medium text-gray-900">Payment</h2>

					<div class="block lg:hidden mt-4 bg-white border border-gray-200 rounded-lg shadow-sm mb-10">
						<dl class="py-6 px-4 space-y-6 sm:px-6">{priceSummary(vm)}</dl>
					</div>

					{when(vm.isBillingCountryInSepa, () => (
						<fieldset class="mt-4">
							<legend class="sr-only">Payment type</legend>
							<div class="space-y-4 sm:flex sm:items-center sm:space-y-0 sm:space-x-10">
								<div class="flex items-center">
									<input id="credit-card" name="payment-type" type="radio" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" value="cc" ko-checked={vm.selectedPaymentMethod} />
									<label for="credit-card" class="ml-3 block text-sm font-medium text-gray-700">Credit Card</label>
								</div>

								<div class="flex items-center">
									<input id="sepa" name="payment-type" type="radio" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" value="sepa" ko-checked={vm.selectedPaymentMethod} />
									<label for="sepa" class="ml-3 block text-sm font-medium text-gray-700">SEPA Bank Transfer (IBAN)</label>
								</div>
							</div>
						</fieldset>
					))}

					{ko.pureComputed(() => vm.confirmOrderErrors().map((error) => errorMessage(error)))}
					<div ko-visible={ko.pureComputed(() => vm.selectedPaymentMethod() === 'cc')}>
						{when(vm.stripeLoading, () => (
							<div class="mt-10 mx-auto w-8 h-8">
								<div class="spinner"></div>
							</div>
						))}

						<div ko-visible={ko.pureComputed(() => !vm.stripeLoading())}>
							<div class="mt-4">
								<div class="mb-6 text-sm text-gray-500">Payment is handled by Stripe.</div>
								{/* Stripe.js injects the Card Element */}
								<div id="card-element" class="p-2 rounded-md border border-gray-300 shadow-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"></div>
							</div>
							{agreements(vm, 'card')}
							{confirmOrder(vm, 'py-6 mt-2', 'my-10')}
						</div>
					</div>

					{when(() => vm.selectedPaymentMethod() === 'sepa', () => (
						<>
							<div class="mt-4">
								<div class="text-sm text-gray-500">You will receive the SEPA payment details by email after confirming the order.</div>
							</div>
							{agreements(vm, 'sepa')}
							{confirmOrder(vm, 'py-6', 'my-8')}
						</>
					))}
				</div>
			</div>
		</form>
	);
}

// Both copies of the order total: next to the cart, and above the payment on narrow screens.
function priceSummary(vm: MerchSaleViewModel): JSX.Element {
	const shippingPriceLoaded = ko.pureComputed(() => !vm.shippingPriceLoading());

	return (
		<>
			<div class="flex items-center justify-between">
				<dt class="text-sm">Subtotal</dt>
				<dd class="text-sm font-medium text-gray-900" ko-text={ko.pureComputed(() => vm.subtotalPrice() + ' €')}></dd>
			</div>
			<div class="flex items-center justify-between">
				<dt class="text-sm">Shipping</dt>
				<dd class="text-sm font-medium text-gray-900">
					<div ko-visible={vm.shippingPriceLoading} class="mx-auto w-4 h-4">
						<div class="spinner"></div>
					</div>
				</dd>
				<dd class="text-sm font-medium text-gray-900" ko-visible={shippingPriceLoaded} ko-text={ko.pureComputed(() => vm.shippingPrice() + ' €')}></dd>
			</div>
			<div class="flex items-center justify-between border-t border-gray-200 pt-6">
				<dt class="text-base font-medium">Total</dt>
				<dd class="text-base font-medium text-gray-900">
					<div ko-visible={vm.shippingPriceLoading} class="mx-auto w-4 h-4">
						<div class="spinner"></div>
					</div>
				</dd>
				<dd class="text-base font-medium text-gray-900" ko-visible={shippingPriceLoaded} ko-text={ko.pureComputed(() => vm.totalPrice() + ' €')}></dd>
			</div>
		</>
	);
}

function textField(id: string, label: string | ko.Subscribable<string>, value: ko.Observable<string>, autocomplete?: string, className?: string): JSX.Element {
	return (
		<div class={className}>
			<label for={id} class="block text-sm font-medium text-gray-700">{label}</label>
			<div class="mt-1">
				<input type="text" id={id} name={id} autocomplete={autocomplete} class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" ko-value={value} />
			</div>
		</div>
	);
}

function countryField(id: string, vm: MerchSaleViewModel, value: ko.Observable<string>): JSX.Element {
	return (
		<div>
			<label for={id} class="block text-sm font-medium text-gray-700">Country</label>
			<div class="mt-1">
				<select id={id} name={id} autocomplete="country" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" ko-options={vm.countries} ko-optionsText="country_name" ko-optionsValue="country_code" ko-value={value}></select>
			</div>
		</div>
	);
}

function quantitySelect(options: ko.Subscribable<(number | string)[]>, amount: ko.Observable<number | string>): JSX.Element {
	return (
		<>
			<label for="quantity" class="sr-only">Quantity</label>
			<select id="quantity" name="quantity" class="rounded-md border border-gray-300 text-base font-medium text-gray-700 text-left shadow-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" ko-options={options} ko-value={amount}></select>
		</>
	);
}

function agreements(vm: MerchSaleViewModel, idSuffix: string): JSX.Element {
	return (
		<>
			<div class="mt-4 flex items-center">
				<input id={`privacy-policy-${idSuffix}`} name={`privacy-policy-${idSuffix}`} type="checkbox" class="h-4 w-4 border-gray-300 rounded text-indigo-600 focus:ring-indigo-500" ko-checked={vm.agreeToPrivacyPolicy} />
				<div class="ml-2">
					<label for={`privacy-policy-${idSuffix}`} class="text-sm font-medium text-gray-900">Agree to <a target="_blank" href="/merch-privacy-policy" class="underline hover:no-underline text-indigo-800">Privacy Policy</a></label>
				</div>
			</div>
			<div class="mt-1 flex items-center">
				<input id={`terms-${idSuffix}`} name={`terms-${idSuffix}`} type="checkbox" class="h-4 w-4 border-gray-300 rounded text-indigo-600 focus:ring-indigo-500" ko-checked={vm.agreeToTerms} />
				<div class="ml-2">
					<label for={`terms-${idSuffix}`} class="text-sm font-medium text-gray-900">Agree to <a target="_blank" href="/merch-terms" class="underline hover:no-underline text-indigo-800">Terms &amp; Conditions</a></label>
				</div>
			</div>
		</>
	);
}

function confirmOrder(vm: MerchSaleViewModel, buttonClass: string, spinnerMargin: string): JSX.Element {
	return (
		<>
			<div class={buttonClass} ko-visible={ko.pureComputed(() => !vm.isConfirmingOrder())}>
				<button type="submit" class="w-full bg-indigo-600 border border-transparent rounded-md shadow-sm py-3 px-4 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-50 focus:ring-indigo-500">Confirm order</button>
			</div>
			<div class={`${spinnerMargin} mx-auto w-8 h-8`} ko-visible={vm.isConfirmingOrder}>
				<div class="spinner"></div>
			</div>
			<div class="text-center text-sm text-gray-700" ko-visible={ko.pureComputed(() => vm.confirmOrderErrors().length > 0)}>In case of technical difficulties please contact us at <a href="mailto:merch@phpstan.com" class="underline hover:no-underline">merch@phpstan.com</a>.</div>
		</>
	);
}

function addToCartButton(): JSX.Element {
	return <button type="submit" class="sm:max-w-sm lg:max-w-xs flex-1 bg-indigo-600 border border-transparent rounded-md py-3 px-8 flex items-center justify-center text-base font-medium text-white hover:bg-indigo-700 sm:w-full">Add to cart</button>;
}

function removeButton(remove: () => void): JSX.Element {
	return (
		<button type="button" class="-m-2.5 bg-white p-2.5 flex items-center justify-center text-gray-400 hover:text-gray-500" ko-click={remove}>
			<span class="sr-only">Remove</span>
			{/* Heroicon name: solid/trash */}
			<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
				<path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
			</svg>
		</button>
	);
}

function successMessage(message: ko.Subscribable<string | null>): JSX.Element {
	return (
		<div class="rounded-md bg-green-50 p-4 mt-4">
			<div class="flex">
				<div class="flex-shrink-0">
					{checkCircleIcon()}
				</div>
				<div class="ml-3">
					<h3 class="text-sm font-medium text-green-700" ko-text={message}></h3>
				</div>
			</div>
		</div>
	);
}

function errorMessage(message: string | ko.Subscribable<string | null>): JSX.Element {
	return (
		<div class="rounded-md bg-red-50 p-4 mt-4">
			<div class="flex">
				<div class="flex-shrink-0">
					{xCircleIcon()}
				</div>
				<div class="ml-3">
					<h3 class="text-sm font-medium text-red-800" ko-text={message}></h3>
				</div>
			</div>
		</div>
	);
}

// Heroicon name: solid/check-circle
function checkCircleIcon(): JSX.Element {
	return (
		<svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
			<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
		</svg>
	);
}

// Heroicon name: solid/x-circle
function xCircleIcon(): JSX.Element {
	return (
		<svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
			<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
		</svg>
	);
}
