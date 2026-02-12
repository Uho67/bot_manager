export interface Product {
  id: number;
  name: string;
  description: string;
  image?: string;
  categories: Category[];
}

export interface Category {
  id: number;
  name: string;
}

