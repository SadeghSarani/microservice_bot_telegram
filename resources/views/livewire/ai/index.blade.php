<?php

use App\Models\TelegramReplyKeyboard;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public bool $changeAi = false;

    public string $ai_name = '';

    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->success('Filters cleared.', position: 'toast-bottom');
    }

    // Delete action
    public function getAi($ai): void
    {
        $this->ai_name = \App\Models\Ai::query()->where('id', $ai)->first()['name'];
        $this->changeAi = true;
    }

    public function updateModel()
    {
        \App\Models\Ai::query()->where('id', 1)->update([
            'name' => $this->ai_name
        ]);

        $this->changeAi = false;
        $this->success('ai changed');
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'title', 'class' => 'w-64'],

        ];
    }

    /**
     * For demo purpose, this is a static collection.
     *
     * On real projects you do it with Eloquent collections.
     * Please, refer to maryUI docs to see the eloquent examples.
     */
    public function ai(): Collection
    {
        return \App\Models\Ai::all();
    }

    public function with(): array
    {
        return [
            'ai' => $this->ai(),
            'headers' => $this->headers()
        ];
    }
};

?>

<div>
    <!-- HEADER -->
    <x-header title="Ai model" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass"/>
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel"/>
        </x-slot:actions>
    </x-header>

    <x-modal wire:model="changeAi" title="تغییر پرامپت">
        <div>
            <x-input label="Ai Name" wire:model="ai_name" placeholder="نام مدل"/>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.change_prompt = false"/>
            <x-button label="Confirm" class="btn-primary" wire:click="updateModel()"/>
        </x-slot:actions>
    </x-modal>

    <!-- TABLE  -->
    <x-card>
        <x-table :headers="$headers" :rows="$ai" :sort-by="$sortBy">
            @scope('actions', $ai)
            <x-button icon="o-wrench" wire:click="getAi({{ $ai['id'] }})"
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
