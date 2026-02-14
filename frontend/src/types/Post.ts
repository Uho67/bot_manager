export interface Post {
  id: number;
  name: string;
  description: string;
  image?: string;
  image_file_id?: string;
  template_type: 'start' | 'product' | 'post';
  enabled: boolean;
}
