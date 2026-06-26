<?php

use App\Models\Category;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Kategori Produk')] class extends Component {
    public string $name = '';

    public ?int $editingId = null;

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($this->editingId)],
        ]);

        if ($this->editingId) {
            Category::query()->whereKey($this->editingId)->update($validated);
            Flux::toast(variant: 'success', text: 'Kategori berhasil diperbarui.');
        } else {
            Category::query()->create($validated);
            Flux::toast(variant: 'success', text: 'Kategori berhasil ditambahkan.');
        }

        $this->resetForm();
    }

    public function edit(int $categoryId): void
    {
        $category = Category::query()->findOrFail($categoryId);

        $this->editingId = $category->id;
        $this->name = $category->name;
    }

    public function delete(int $categoryId): void
    {
        $category = Category::query()->withCount('products')->findOrFail($categoryId);

        if ($category->products_count > 0) {
            Flux::toast(variant: 'danger', text: 'Kategori masih memiliki produk.');

            return;
        }

        $category->delete();

        if ($this->editingId === $categoryId) {
            $this->resetForm();
        }

        Flux::toast(variant: 'success', text: 'Kategori berhasil dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'editingId']);
        $this->resetValidation();
    }
}; ?>

<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl">Kategori Produk</flux:heading>
        <flux:text class="mt-1">Kelola kategori untuk mengelompokkan produk.</flux:text>
    </div>

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ $editingId ? 'Edit Kategori' : 'Tambah Kategori' }}</flux:heading>

            <form wire:submit="save" class="mt-4 flex flex-col gap-4">
                <flux:input wire:model="name" label="Nama kategori" placeholder="Contoh: Minuman" required />

                <div class="flex gap-2">
                    <flux:button type="submit" variant="primary">
                        {{ $editingId ? 'Simpan perubahan' : 'Tambah kategori' }}
                    </flux:button>

                    @if ($editingId)
                        <flux:button type="button" variant="ghost" wire:click="resetForm">Batal</flux:button>
                    @endif
                </div>
            </form>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Nama</flux:table.column>
                    <flux:table.column>Jumlah Produk</flux:table.column>
                    <flux:table.column class="w-40">Aksi</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse (Category::query()->withCount('products')->orderBy('name')->get() as $category)
                        <flux:table.row wire:key="category-{{ $category->id }}">
                            <flux:table.cell>{{ $category->name }}</flux:table.cell>
                            <flux:table.cell>{{ $category->products_count }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="ghost" wire:click="edit({{ $category->id }})">Edit</flux:button>
                                    <flux:button size="sm" variant="danger" wire:click="delete({{ $category->id }})" wire:confirm="Hapus kategori ini?">Hapus</flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="3">Belum ada kategori.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>
</div>
