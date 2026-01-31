export interface Category {
  id: number;
  name: string;
  sortOrder?: number;
  childCategories: Category[];
}

