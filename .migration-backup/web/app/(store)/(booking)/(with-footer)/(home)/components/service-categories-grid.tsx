"use client";

import { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import Link from "next/link";
import { CategoryPictogram } from "@/components/stage2";
import { categoryService } from "@/services/category";
import type { Category } from "@/types/category";
import { useSettings } from "@/hook/use-settings";
import { buildUrlQueryParams } from "@/utils/build-url-query-params";
import { getCategoryChildren, getCategoryHierarchy } from "@/utils/category-hierarchy";

interface ServiceCategoriesGridProps {
  categories: Category[];
  loadError?: boolean;
}

export const ServiceCategoriesGrid = ({
  categories,
  loadError = false,
}: ServiceCategoriesGridProps) => {
  const serviceCategories = getCategoryHierarchy(categories);
  const [selectedCategoryId, setSelectedCategoryId] = useState<number | null>(
    serviceCategories[0]?.category.id ?? null
  );
  const { language } = useSettings();
  const selectedCategory =
    serviceCategories.find(({ category }) => category.id === selectedCategoryId) ||
    serviceCategories[0];
  const selectedId = selectedCategory?.category.id;
  const embeddedChildren = selectedCategory?.children || [];
  const {
    data: childrenResponse,
    isInitialLoading: isChildrenLoading,
    isError: hasChildrenError,
    refetch: refetchChildren,
  } = useQuery(
    ["service-category-children", selectedId, language?.locale],
    () => categoryService.getChildren(selectedId as number, { lang: language?.locale }),
    { enabled: Boolean(selectedId) && embeddedChildren.length === 0 }
  );
  const categoryChildren =
    embeddedChildren.length > 0
      ? embeddedChildren
      : getCategoryChildren(childrenResponse)
          .map((category) => ({
            category,
            title: category.translation?.title?.trim() || "",
          }))
          .filter(({ title }) => title.length > 0);

  return (
    <section className="aa-s2-section">
      <div className="aa-s2-section-head">
        <div>
          <span className="aa-s2-eyebrow">Services</span>
          <h2>Explore service categories.</h2>
          <p className="aa-s2-muted">
            Browse the categories supported by the service catalog. Category listings do not
            guarantee provider availability.
          </p>
        </div>
      </div>

      {loadError ? (
        <p className="aa-s2-muted" role="alert">
          Service categories could not be loaded.
        </p>
      ) : serviceCategories.length ? (
        <>
          <div className="aa-s2-category-grid" role="group" aria-label="Service categories">
            {serviceCategories.map(({ category, title, children }) => {
              const isSelected = category.id === selectedCategory?.category.id;
              return (
                <button
                  aria-pressed={isSelected}
                  className="aa-s2-category"
                  key={category.id}
                  onClick={() => setSelectedCategoryId(category.id)}
                  type="button"
                >
                  <span className="aa-s2-cat-glyph" aria-hidden="true">
                    <CategoryPictogram
                      category={title}
                      categoryId={category.id}
                      imageRef={category.img}
                    />
                  </span>
                  <span className="line-clamp-2 text-sm font-semibold">{title}</span>
                  <span className="aa-s2-category-count">
                    {children.length
                      ? `${children.length} ${children.length === 1 ? "category" : "categories"}`
                      : "Browse services"}
                  </span>
                </button>
              );
            })}
          </div>
          {selectedCategory && (
            <div className="aa-s2-category-children" aria-live="polite">
              <div className="aa-s2-category-children-heading">
                <div>
                  <span className="aa-s2-eyebrow">Explore within</span>
                  <h3>{selectedCategory.title}</h3>
                </div>
                <Link
                  className="aa-s2-category-all"
                  href={buildUrlQueryParams("/search", {
                    category_id: selectedCategory.category.id,
                  })}
                >
                  All {selectedCategory.title}
                </Link>
              </div>
              {isChildrenLoading ? (
                <div className="aa-s2-subcategory-list" aria-busy="true" aria-live="polite">
                  {Array.from({ length: 3 }, (_, index) => (
                    <span className="aa-s2-subcategory-skeleton" key={index} />
                  ))}
                </div>
              ) : hasChildrenError ? (
                <div className="aa-s2-category-child-error" role="alert">
                  <span>Subcategories could not be loaded.</span>
                  <button onClick={() => void refetchChildren()} type="button">
                    Try again
                  </button>
                </div>
              ) : categoryChildren.length ? (
                <div className="aa-s2-subcategory-list">
                  {categoryChildren.map(({ category, title }) => (
                    <Link
                      className="aa-s2-subcategory"
                      href={buildUrlQueryParams("/search", { category_id: category.id })}
                      key={category.id}
                    >
                      <span className="aa-s2-subcategory-mark" aria-hidden="true" />
                      <span>{title}</span>
                    </Link>
                  ))}
                </div>
              ) : (
                <p className="aa-s2-muted">
                  Browse the services available in this category.
                </p>
              )}
            </div>
          )}
        </>
      ) : (
        <p className="aa-s2-muted" role="status">
          The service catalog currently returns no categories.
        </p>
      )}
    </section>
  );
};