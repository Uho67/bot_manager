export interface Button {
	id: number;
	code: string;
	label: string;
	buttonType: 'url' | 'callback';
	value: string;
}
