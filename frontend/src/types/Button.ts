export interface Button {
	id: number;
	code: string;
	label: string;
	sortOrder: number;
	buttonType: 'url' | 'callback';
	value: string;
}
