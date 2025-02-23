<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public string $password = '';
    public string $user = '';

    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->success('Filters cleared.', position: 'toast-bottom');
    }

    public function loginUser()
    {

        if (Auth::attempt(['email' => $this->user, 'password' => $this->password])) {
            $loggedInUser = Auth::user();
            $this->success('Login success');

            return redirect()->route('prompt.index');
        } else {
            $this->addError('auth', 'Invalid credentials. Please try again.');
        }
    }
};
?>


<div style="width: 400px; margin: 0 auto;">
    <x-card title="Login Page">
        <x-form wire:submit.prevent="loginUser">
            {{-- Full error bag --}}
            <x-errors title="Oops!" icon="o-face-frown"/>

            {{-- Bind user and password inputs --}}
            <x-input label="Email" wire:model.defer="user" type="email"/>
            <x-input label="Password" wire:model.defer="password" type="password"/>

            <x-slot:actions>
                <x-button label="Login" class="btn-primary" wire:click="loginUser()"/>
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
