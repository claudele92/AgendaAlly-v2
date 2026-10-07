<?php
declare(strict_types=1);

namespace App\Imports;

use App\Models\Category;
use App\Models\Language;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

class CategoryImport extends BaseImport implements ToCollection, WithHeadingRow
{
    use Importable;

    public function __construct(private string $language, private ?int $shopId = null)
    {
    }

    /**
     * @param Collection $collection
     * @return void
     */
    public function collection(Collection $collection): void
    {
        $language = Language::where('default', 1)->first();

        try {

            DB::transaction(function () use ($collection, $language) {

                $parentIds = [];

                foreach ($collection as $row) {

                    $type = data_get($row, 'type');

                    if (!data_get($row, 'img_urls', '')) {
                        continue;
                    }

                    $parentId = $row['parent_id'] ?? 0;

                    $category = Category::updateOrCreate([
                        'uuid'      => data_get($row, 'uu_id', '')
                    ], [
                        'keywords'  => data_get($row, 'keywords', ''),
                        'type'      => empty($type) ? Category::MAIN : data_get(Category::TYPES, $type, Category::MAIN),
                        'parent_id' => !empty($parentId) ? $parentIds[$parentId] : $parentId,
                        'shop_id'   => $this->shopId,
                        'active'    => data_get($row, 'active') === 'active' ? 1 : 0,
                    ]);

                    if (empty($parentId) && isset($row['id'])) {
                        $parentIds[$row['id']] = $category->id;
                    }

                    $category->update([
                        'slug' => Str::slug(data_get($row, 'title', '')) . "-$category->id"
                    ]);

                    $category->translation()->updateOrCreate([
                        'category_id' => $category->id,
                        'locale' => $this->language ?? $language,
                    ], [
                        'title' => data_get($row, 'title', ''),
                        'description' => data_get($row, 'description', ''),
                    ]);

                    $this->downloadImages($category, data_get($row, 'img_urls', ''));

                }

            });
        } catch (Throwable $e) {
            $this->error($e);
        }
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function batchSize(): int
    {
        return 200;
    }

    public function chunkSize(): int
    {
        return 200;
    }
}
