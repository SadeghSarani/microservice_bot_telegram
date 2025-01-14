<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Prompt;

class PromptManagement extends Component
{
    public $prompt, $service_id, $prompts;

    public function createPrompt()
    {
        $this->validate([
            'prompt' => 'required|string',
            'service_id' => 'required|integer|exists:services,id',
        ]);

        Prompt::create([
            'prompt' => $this->prompt,
            'service_id' => $this->service_id,
        ]);

        session()->flash('message', 'Prompt created successfully.');
        $this->resetInput();
        $this->fetchPrompts();
    }

    public function fetchPrompts()
    {
        $this->prompts = Prompt::with('service')->get();
    }

    public function resetInput()
    {
        $this->prompt = '';
        $this->service_id = '';
    }

    public function mount()
    {
        $this->fetchPrompts();
    }

    public function render()
    {
        return view('livewire.prompt-management');
    }
}
