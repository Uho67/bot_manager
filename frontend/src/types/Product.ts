export interface ProductImage {
  id: number;
  image: string;
  sort_order: number;
}

export interface Product {
  id: number;
  name: string;
  description: string;
  image?: string;
  images: ProductImage[];
  categories: Category[];
  enabled: boolean;
}

export interface Category {
  id: number;
  name: string;
}

