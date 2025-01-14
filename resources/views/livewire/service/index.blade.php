<?php

use App\Models\Service;
use App\Models\TelegramReplyKeyboard;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public string $search = '';

    public bool $drawer = false;

    public bool $createServiceModal = false;

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public $service_name = '';
    public $service_description = '';
    public $button_name = '';
    public $service_id_btn = '';

    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->success('Filters cleared.', position: 'toast-bottom');
    }

    public function createService()
    {
        Service::create([
            'name' => $this->service_name,
            'description' => $this->service_description
        ]);

        $this->success('سرویس ساخته شد', position: 'toast-bottom');
    }


    public function createButton()
    {
        TelegramReplyKeyboard::create([
            'title' => $this->button_name,
            'service_id' => $this->service_id_btn
        ]);

        $this->success('دکمه افزوده شد');
    }

    public function delete($id): void
    {
        Service::query()->where('id', $id)->delete();
        $this->warning("Will delete #$id", position: 'toast-bottom');
    }

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-20'],
            ['key' => 'description', 'label' => 'description', 'class' => 'w-64'],
        ];
    }

    public function services(): Collection
    {
        return Service::all();
    }

    public function with(): array
    {
        return [
            'services' => $this->services(),
            'headers' => $this->headers()
        ];
    }
}; ?>


<div>
    <!-- HEADER -->
    <x-header title="Manage Service" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass"/>
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel"/>
        </x-slot:actions>
    </x-header>

    <x-button class="btn-ghost  text-red-500" label="ساخت سرویس جدید" @click="$wire.createServiceModal = true"/>
    <x-button class="btn-ghost  text-blue-500" label="ساخت دکمه جدید برای تلگرام"
              @click="$wire.createServiceModal = true"/>

    <x-modal wire:model="createServiceModal" title="ساخت سرویس">
        <x-input label="Name" wire:model="service_name" placeholder="نام سرویس"/>
        <x-input label="Name" wire:model="service_description" placeholder="توضیحات"/>

        <x-slot:actions>
            <x-button label="کنسل" @click="$wire.createServiceModal = false"/>
            <x-button label="ثبت" wire:click="createService()" class="btn-primary"/>
        </x-slot:actions>
    </x-modal>


    <x-modal wire:model="createServiceModal" title="ساخت سرویس">
        <x-input label="نام دکمه" wire:model="button_name" placeholder="متن نمایش دکمه"/>
        <x-select
                label="سرویس مربوطه"
                :options="$services"
                option-value="id"
                option-label="name"
                placeholder="سرویس را انتخاب کنید"
                placeholder-value="انتخاب کنید"
                hint="Select one, please."
                wire:model="service_id_btn"
        />

        <x-slot:actions>
            <x-button label="کنسل" @click="$wire.createServiceModal = false"/>
            <x-button label="ثبت" wire:click="createButton()" class="btn-primary"/>
        </x-slot:actions>
    </x-modal>

    <!-- TABLE  -->
    <x-card>
        <x-table :headers="$headers" :rows="$services" :sort-by="$sortBy">
            @scope('actions', $service)
            <x-button icon="o-trash" wire:click="delete({{ $service['id'] }})" wire:confirm="Are you sure?" spinner
                      class="btn-ghost btn-sm text-red-500"/>
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
