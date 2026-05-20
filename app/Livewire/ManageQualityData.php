<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class ManageQualityData extends Component
{

    public function render(): View
    {
        return view('livewire.manage-quality-data')->layout('components.layouts.app');
    }
}
