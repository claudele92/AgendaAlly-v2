import type { Category, CategoryChildrenResponse } from "@/types/category";

export interface CategoryBranch {
  category: Category;
  title: string;
  children: Array<{ category: Category; title: string }>;
}

const categoryTitle = (category: Category) => category.translation?.title?.trim() || "";

/**
 * Reconcile the native endpoint's parent_id relationship with its optional
 * nested children payload. Only explicit roots (parent_id 0/null) are exposed
 * at level one; child records are never promoted based on their label.
 */
export const getCategoryHierarchy = (categories: Category[]): CategoryBranch[] => {
  const all = new Map<number, Category>();
  const visit = (items: Category[]) => {
    items.forEach((category) => {
      if (!all.has(category.id)) all.set(category.id, category);
      if (category.children?.length) visit(category.children);
    });
  };
  visit(categories);

  const childrenByParent = new Map<number, Map<number, Category>>();
  all.forEach((category) => {
    if (category.parent_id) {
      const siblings = childrenByParent.get(category.parent_id) || new Map<number, Category>();
      siblings.set(category.id, category);
      childrenByParent.set(category.parent_id, siblings);
    }
  });

  return [...all.values()]
    .filter((category) => !category.parent_id && Boolean(categoryTitle(category)))
    .map((category) => {
      const children = new Map<number, Category>();
      category.children?.forEach((child) => children.set(child.id, child));
      childrenByParent.get(category.id)?.forEach((child, id) => children.set(id, child));
      return {
        category,
        title: categoryTitle(category),
        children: [...children.values()]
          .filter((child) => Boolean(categoryTitle(child)))
          .map((child) => ({ category: child, title: categoryTitle(child) })),
      };
    });
};

/** The children endpoint wraps the selected parent resource in `data`. */
export const getCategoryChildren = (
  response?: CategoryChildrenResponse
): Category[] => response?.data?.children || [];