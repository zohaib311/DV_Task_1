<?php

namespace App\View\Components\Result;

use App\Models\Course\Course;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AddResultModal extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {

        $courses  = Course::all();
        return view('components.result.add-result-modal', compact("courses"));
    }
}
