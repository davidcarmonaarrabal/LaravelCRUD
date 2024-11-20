<?php

namespace App\Livewire;

use App\Models\Diet;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DietsTable extends Component
{
    public $diets = [];
    public $myDiet;
    public $title;
    public $fecha;
    public $description;
    public $totalCalories;
    public $modal = false;
    public $isEditing = false;

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
        $this->description = '';
        $this->totalCalories = '';
    }

    private function createDiet(){
        $this->clearFields();
        $this->modal = true;
    }

    public function openModal(Diet $diet = null, bool $isEditing = false){
        if ($diet){
            $this -> title = $diet -> title;
            $this -> fecha = $diet -> fecha;
            $this -> description = $diet -> description;
            $this -> totalCalories = $diet -> totalCalories;
            $this -> myDiet = $diet;
        } else {
            $this -> clearFields();
        }
        $this -> isEditing = $isEditing;
        $this->modal = true;
    }
}
