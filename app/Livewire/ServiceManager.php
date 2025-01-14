<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Service;

class ServiceManager extends Component
{
    public $services;
    public $service_id, $name, $description;

    public bool $isEdit = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
    ];

    public function mount()
    {
        $this->loadServices();
    }

    public function loadServices()
    {
        $this->services = Service::all();
    }

    public function create()
    {
        $this->resetFields();
        $this->isEdit = false;
    }

    public function edit($id)
    {
        $this->isEdit = true;
        $service = Service::findOrFail($id);
        $this->service_id = $service->id;
        $this->name = $service->name;
        $this->description = $service->description;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEdit) {
            $service = Service::findOrFail($this->service_id);
            $service->update([
                'name' => $this->name,
                'description' => $this->description,
            ]);
        } else {
            Service::create([
                'name' => $this->name,
                'description' => $this->description,
            ]);
        }

        $this->loadServices();
        $this->resetFields();
    }

    public function delete($id)
    {
        Service::findOrFail($id)->delete();
        $this->loadServices();
    }

    public function resetFields()
    {
        $this->service_id = null;
        $this->name = '';
        $this->description = '';
    }

    public function render()
    {
        return view('livewire.service-manager');
    }
}

