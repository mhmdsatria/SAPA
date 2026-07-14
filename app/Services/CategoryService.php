<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function create(array $data): Category
    {
        return Category::query()->create($this->normalize($data));
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($this->normalize($data, $category));

        return $category->fresh();
    }

    public function remove(Category $category): void
    {
        if ($category->complaints()->exists()) {
            $category->update(['is_active' => false]);

            return;
        }

        $category->delete();
    }

    public function toggle(Category $category): Category
    {
        $category->update(['is_active' => ! $category->is_active]);

        return $category->fresh();
    }

    private function normalize(array $data, ?Category $category = null): array
    {
        $name = trim((string) $data['name']);
        $slug = Str::slug((string) ($data['slug'] ?: $name));
        $duplicate = Category::query()->where('slug', $slug)
            ->when($category, fn ($query) => $query->whereKeyNot($category->id))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages(['slug' => 'Slug kategori sudah digunakan.']);
        }

        return [
            'name' => $name,
            'slug' => $slug,
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'color' => strtolower((string) $data['color']),
            'icon' => trim((string) ($data['icon'] ?? 'tag')) ?: 'tag',
            'is_active' => (bool) ($data['is_active'] ?? true),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }
}
