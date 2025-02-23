<?php

use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public string $package_name = '';
    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->success('Filters cleared.', position: 'toast-bottom');
    }



    // Delete action
    public function delete($id): void
    {
        Package::query()->where('id', $id)->delete();

        $this->success("Item delete #$id");
    }
    
    public function edit($id): void{
        Package::query()->where('id', $id)->update([
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'month' => $this->month
        ]);

        $this->success("Item edit #$id");
    }

    public function getPackage($ai): void
    {
        $this->package_name = Package::query()->where('id', $ai)->first()['name'];
        $this->changePackage = true;
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'name', 'class' => 'w-64'],
            ['key' => 'description', 'label' => 'description', 'class' => 'w-64'],
            ['key' => 'price', 'label' => 'price', 'class' => 'w-64'],
            ['key' => 'month', 'label' => 'month', 'class' => 'w-64'],
            ['key' => 'count_request', 'label' => 'count_request', 'class' => 'w-64'],

        ];
    }

    /**
     * For demo purpose, this is a static collection.
     *
     * On real projects you do it with Eloquent collections.
     * Please, refer to maryUI docs to see the eloquent examples.
     */
    public function buttons(): Collection
    {
        return Package::all();
    }

    public function with(): array
    {
        return [
            'buttons' => $this->buttons(),
            'headers' => $this->headers()
        ];
    }
};

?>

<div>

    <x-modal wire:model="changePackage" title="تغییر پرامپت">
        <div>
            <x-input label="Ai Name" wire:model="ai_name" placeholder="نام مدل"/>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.change_prompt = false"/>
            <x-button label="Confirm" class="btn-primary" wire:click="updateModel()"/>
        </x-slot:actions>
    </x-modal>


    <!-- HEADER -->
    <x-header title="Buttons" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass"/>
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel"/>
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table :headers="$headers" :rows="$buttons" :sort-by="$sortBy">
            @scope('actions', $button)
            <x-button icon="o-trash" wire:click="delete({{ $button['id'] }})" wire:confirm="Are you sure?" spinner
                      class="btn-ghost btn-sm text-red-500"/>
            
            <x-button icon="o-wrench" wire:click="edit({{ $button['id'] }})"
                      class="btn-ghost btn-sm text-yellow-500"/>
            
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <x-input placeholder="Search..." wire:model.live.debounce="search" icon="o-magnifying-glass"
                 @keydown.enter="$wire.drawer = false"/>

        <x-slot:actions>
            <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner/>
            <x-button label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer = false"/>
        </x-slot:actions>
    </x-drawer>
</div>
