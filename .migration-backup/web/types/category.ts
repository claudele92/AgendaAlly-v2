import { Translation } from "./global";

export interface Category {
  active: boolean;
  created_at: string;
  id: number;
  img?: string;
  keywords: string;
  parent_id?: number | null;
  translation: Translation | null;
  type: string;
  updated_at: string;
  uuid: string;
  children?: Category[];
}

export interface CategoryChildrenResponse {
  data: {
    id: number;
    translation: Translation | null;
    children: Category[];
    parent: Category | null;
  };
}
