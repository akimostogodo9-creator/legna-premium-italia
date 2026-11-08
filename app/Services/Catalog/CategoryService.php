<?php

namespace App\Services\Catalog;

use App\Models\Category;
use App\Services\Media\ImageProcessor;
use App\Support\SlugGenerator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// R-CAT-01→04
class CategoryService
{
    public function __construct(private readonly ImageProcessor $images) {}

    /** @param array<string, mixed> $data */
    public function create(array $data, ?UploadedFile $image): Category
    {
        $data['slug'] = SlugGenerator::unique(Category::class, $data['slug'] ?: $data['name']);
        $data['sort_order'] = (int) Category::max('sort_order') + 1;

        if ($image) {
            $data['image_path'] = $this->images->store($image, 'categories')['path'];
        }

        return Category::create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(Category $category, array $data, ?UploadedFile $image, bool $removeImage): Category
    {
        $data['slug'] = SlugGenerator::unique(Category::class, $data['slug'] ?: $data['name'], $category->id);
        $oldImage = $category->image_path;

        if ($image) {
            $data['image_path'] = $this->images->store($image, 'categories')['path'];
        } elseif ($removeImage) {
            $data['image_path'] = null;
        }

        $category->update($data);

        if ($oldImage && $oldImage !== $category->image_path) {
            $this->deleteImageFiles($oldImage);
        }

        return $category;
    }

    /** R-CAT-04 : suppression refusée si la catégorie contient des produits. */
    public function delete(Category $category): void
    {
        $count = $category->products()->count();

        if ($count > 0) {
            throw ValidationException::withMessages([
                'category' => __('admin.categories.not_empty', ['count' => $count]),
            ]);
        }

        $image = $category->image_path;
        $category->delete();

        if ($image) {
            $this->deleteImageFiles($image);
        }
    }

    /**
     * R-ORD-01 : nouvel ordre d'affichage (liste d'identifiants dans l'ordre voulu).
     *
     * @param  list<int>  $ids
     */
    public function reorder(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            foreach (array_values($ids) as $position => $id) {
                Category::whereKey($id)->update(['sort_order' => $position]);
            }
        });
    }

    /** Une image de catégorie est stockée sous « …-1600.webp » ; on supprime toutes ses tailles. */
    private function deleteImageFiles(string $path): void
    {
        $variants = [];
        foreach (ImageProcessor::WIDTHS as $width) {
            $variants[] = preg_replace('/-\d+\.webp$/', "-{$width}.webp", $path);
        }

        $this->images->delete($path, $variants);
    }
}
