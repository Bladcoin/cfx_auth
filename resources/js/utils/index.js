import BigNumber from '../../../node_modules/bignumber.js/bignumber.js'

export const Units = [{
	name: 'T',
	exp: 30,
}, {
	name: 'G',
	exp: 27,
}, {
	name: 'M',
	exp: 24,
}, {
	name: 'K',
	exp: 21,
}, {
	name: 'CFX',
	exp: 18,
}, {
	name: 'mCFX',
	exp: 15,
}, {
	name: 'uCFX',
	exp: 12,
}, {
	name: 'GDrip',
	exp: 9,
}, {
	name: 'MDrip',
	exp: 6,
}, {
	name: 'KDrip',
	exp: 3,
}, {
	name: 'Drip',
	exp: 0,
}];

export const TEN = new Big(10)

export function calculateGasMargin(value, margin = 0.1) {
	return new BigNumber(value?.toString(10))
		.multipliedBy(new BigNumber(10000).plus(new BigNumber(10000 * margin)))
		.dividedBy(new BigNumber(10000))
		.toFixed(0);
}

export function formatUnit(value, unitName) {
	const bigValue = new Big(value);
	for (let i = 0; i < Units.length; i++) {
		if ( Units[i].name === unitName) {
			return `${bigValue.div(TEN.pow(Units[i].exp)).toFixed(4)} ${unitName}`;
		}
	}
	return value;
}

export function prettyFormat(value) {
	const bigValue = new Big(value);
	for (let i = 0; i < Units.length; i++) {
		const toCompare = TEN.pow(Units[i].exp);
		if (bigValue.gte(toCompare)) {
			return `${bigValue.div(toCompare).toFixed(4)} ${Units[i].name}`;
		}
	}
}

export function formatTime(date) {
	return `${paddingZero(date.getHours())}:${paddingZero(date.getMinutes())}:${paddingZero(date.getSeconds())}`;
}

export function formatDateTime(date) {
	return `${date.getFullYear()}-${paddingZero(date.getMonth() + 1)}-${paddingZero(date.getDate())} ${paddingZero(date.getHours())}:${paddingZero(date.getMinutes())}:${paddingZero(date.getSeconds())}`;
}

function paddingZero(value) {
	return value < 10 ? `0${value}` : value;
}
