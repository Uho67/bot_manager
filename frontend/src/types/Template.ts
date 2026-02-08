export interface Template {
	id: number;
	name: string;
	type: 'post' | 'start' | 'category' | 'product';
	layout: number[][];
}

