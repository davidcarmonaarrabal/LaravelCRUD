<?php

namespace App\Livewire;

use App\Models\Diet;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DietsTable extends Component
{
    public $diets = [];
    public $title;
    public $fecha;
    public $modal = false;

    public function mount() {
        $this->diets = Diet::where('user_id', Auth::id())->get();
    }
    public function render()
    {
        return view('livewire.diets-table');
    }

    private function clearFields(){
        $this->title = '';
        $this->fecha = '';
    }

    private function createDiet(){
        $this->clearFields();
        $this->modal = true;
    }
}
