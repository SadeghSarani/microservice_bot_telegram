<?php

use App\Models\Prompt;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component
{
    use Toast;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public $prompt = null;

    public $change_prompt = false;


    public $add_prompt = false;

    public $service_prompt_id = '';

    public $prompt_new = '';

    public $prompt_updated = null;
    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->success('Filters cleared.', position: 'toast-bottom');
    }

    public function getPrompt($id)
    {
        $this->prompt = Prompt::query()->where('id', $id)->first();
        $this->prompt_updated = $this->prompt['prompt'];
        $this->change_prompt = true;
    }

    // Delete action
    public function delete($id): void
    {
        Prompt::query()->where('id', $id)->delete();

        $this->success('prompt deleted success', position: 'toast-bottom');
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'service_id', 'label' => 'شماره سرویس متصل', 'class' => 'w-1'],
            ['key' => 'prompt', 'label' => 'پرامپت', 'class' => 'w-64'],
        ];
    }

    public function updatePrompt($prompt)
    {
        Prompt::query()->where('id', $prompt['id'])->update([
            'prompt' => $this->prompt_updated
        ]);

        $this->success('با موفقیت آپدیت شد');
    }

    public function create_prompt()
    {
        Prompt::query()->create([
            'prompt' => $this->prompt_new,
            'service_id' => $this->service_prompt_id
        ]);

        $this->success('با موفقیت افزوده شد');
        $this->add_prompt = false;
    }

    public function prompts(): Collection
    {
        return Prompt::all();
    }

    public function services(): Collection
    {
        return Service::all();
    }

    public function with(): array
    {
        return [
            'prompts' => $this->prompts(),
            'headers' => $this->headers(),
            'services' => $this->services()
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


    <x-button class="btn-ghost  text-red-500" label="ساخت پرامپت جدید" @click="$wire.add_prompt = true"/>


    <x-modal wire:model="add_prompt" title="ساخت پرامپت جدید">
        <div>
            <x-select
                    label="سرویس مربوطه"
                    :options="$services"
                    option-value="id"
                    option-label="name"
                    placeholder="سرویس را انتخاب کنید"
                    placeholder-value="انتخاب کنید"
                    hint="Select one, please."
                    wire:model="service_prompt_id"
            />

            <x-textarea
                    label="پرامپت مربوط به سرویس"
                    wire:model="prompt_new"
                    rows="5"
                    inline
            >
            </x-textarea>
        </div>

        <x-slot:actions>
            <x-button label="کنسل" @click="$wire.change_prompt = false"/>
            <x-button label="ثبت" class="btn-primary" wire:click="create_prompt()"/>
        </x-slot:actions>
    </x-modal>

    <x-modal wire:model="change_prompt" title="تغییر پرامپت">
        <div>
            @if($prompt)
                <div>
                    <x-textarea
                            label="اپدیت پرامپت"
                            wire:model="prompt_updated"
                            rows="5"
                            inline
                    >
                        {{$prompt['prompt']}}
                    </x-textarea>
                </div>

            @else
                پرامپت مشخص شده پیدا نشد
            @endif
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.change_prompt = false"/>
            <x-button label="Confirm" class="btn-primary" wire:click="updatePrompt({{$prompt}})"/>
        </x-slot:actions>
    </x-modal>

    <!-- TABLE  -->
    <x-card>
        <x-table :headers="$headers" :rows="$prompts" :sort-by="$sortBy">
            @scope('actions', $prompt)
            <x-button icon="o-trash" wire:click="delete({{ $prompt['id'] }})" wire:confirm="Are you sure?" spinner
                      class="btn-ghost btn-sm text-red-500"/>
            <x-button icon="o-wrench" wire:click="getPrompt({{ $prompt['id'] }})"
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