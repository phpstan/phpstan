import * as ko from 'knockout';

export class MerchBannerViewModel {

	countdownText: ko.Observable<string>;

	constructor(immediate: boolean) {
		this.countdownText = ko.observable('&nbsp;');

		const countDownDate = new Date(1637621999 * 1000).getTime(); // 'Nov 22, 2021 23:59:59' Europe/Prague
		const currentDistance = countDownDate - (new Date().getTime());
		if (currentDistance < 0) {
			return;
		}

		const createText = (distance: number) => {
			if (distance < 0) {
				return '&nbsp;';
			}
			const days = Math.floor(distance / (1000 * 60 * 60 * 24));
			const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
			const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
			const seconds = Math.floor((distance % (1000 * 60)) / 1000);

			let text = '';
			if (days > 0) {
				text += days;
				if (days > 1) {
					text += ' days, ';
				} else {
					text += ' day, ';
				}
			}

			text += hours;
			if (hours === 1) {
				text += ' hour, ';
			} else {
				text += ' hours, ';
			}

			text += minutes;
			if (minutes === 1) {
				text += ' minute, ';
			} else {
				text += ' minutes, ';
			}

			text += seconds;
			if (seconds === 1) {
				text += ' second ';
			} else {
				text += ' seconds ';
			}

			text += 'until the sale ends!';

			return text;
		}

		if (immediate) {
			this.countdownText(createText(currentDistance));
		}

		window.setInterval(() => {
			const distance = countDownDate - (new Date().getTime());
			this.countdownText(createText(distance));
		}, 1000);
	}
}
