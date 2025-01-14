<?php

use App\Models\Prompt;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public $prompt = null;

    public $change_prompt = false;

    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->success('Filters cleared.', position: 'toast-bottom');
    }

    public function getPrompt($id)
    {
        $this->prompt = Prompt::query()->where('id', $id);
        $this->change_prompt = true;
    }

    // Delete action
    public function delete($id): void
    {
        $this->warning("Will delete #$id", 'It is fake.', position: 'toast-bottom');
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'service_id', 'label' => 'شماره سرویس متصل', 'class' => 'w-1'],
            ['key' => 'prompt', 'label' => 'پرامپت', 'class' => 'w-64'],
        ];
    }

    /**
     * For demo purpose, this is a static collection.
     *
     * On real projects you do it with Eloquent collections.
     * Please, refer to maryUI docs to see the eloquent examples.
     */
    public function prompts(): Collection
    {
       return Prompt::all();
    }

    public function with(): array
    {
        return [
            'prompts' => $this->prompts(),
            'headers' => $this->headers()
        ];
    }
};
?>

<div>
    <!-- HEADER -->
    <x-header title="Prompt Manager" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass"/>
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel"/>
        </x-slot:actions>
    </x-header>

    <x-modal wire:model="change_prompt" title="Hello" subtitle="Livewire example" separator>
        <div></div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.myModal2 = false" />
            <x-button label="Confirm" class="btn-primary" />
        </x-slot:actions>
    </x-modal>

    <!-- TABLE  -->
    <x-card>
        <x-table :headers="$headers" :rows="$prompts" :sort-by="$sortBy">
            @scope('actions', $prompt)
            <x-button icon="o-trash" wire:click="delete({{ $prompt['id'] }})" wire:confirm="Are you sure?" spinner
                      class="btn-ghost btn-sm text-red-500"/>
            <x-button icon="o-wrench" wire:click="getPrompt({{ $prompt['id'] }})" class="btn-ghost btn-sm text-yellow-500"/>
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
