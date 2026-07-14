<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Services\CategoryService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Kelola Kategori')]
class CategoryManager extends Component
{
    public ?int $editingId = null;
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public string $color = '#2563eb';
    public string $icon = 'tag';
    public int $sortOrder = 0;
    public bool $isActive = true;

    public function edit(int $id): void
    {
        $category = Category::query()->findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = (string) $category->description;
        $this->color = $category->color;
        $this->icon = $category->icon;
        $this->sortOrder = $category->sort_order;
        $this->isActive = $category->is_active;
    }

    public function save(CategoryService $service): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:500'],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'icon' => ['required', 'string', 'max:60'],
            'sortOrder' => ['required', 'integer', 'min:0', 'max:9999'],
            'isActive' => ['boolean'],
        ]);
        $payload = [
            'name' => $data['name'], 'slug' => $data['slug'], 'description' => $data['description'],
            'color' => $data['color'], 'icon' => $data['icon'], 'sort_order' => $data['sortOrder'], 'is_active' => $data['isActive'],
        ];
        $this->editingId
            ? $service->update(Category::query()->findOrFail($this->editingId), $payload)
            : $service->create($payload);
        $this->resetForm();
        session()->flash('success', 'Kategori berhasil disimpan.');
    }

    public function toggle(int $id, CategoryService $service): void
    {
        $service->toggle(Category::query()->findOrFail($id));
    }

    public function remove(int $id, CategoryService $service): void
    {
        $service->remove(Category::query()->findOrFail($id));
        session()->flash('success', 'Kategori dihapus atau dinonaktifkan karena sudah dipakai laporan.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'slug', 'description', 'sortOrder']);
        $this->color = '#2563eb';
        $this->icon = 'tag';
        $this->isActive = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.category-manager', [
            'categories' => Category::query()->withCount('complaints')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }
}
