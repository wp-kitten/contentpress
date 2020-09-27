<?php

namespace App\Http\Livewire;

use App\Models\Category;
use App\Models\PostType;
use App\Models\Tag;
use Livewire\Component;

class BlogSidebar extends Component
{
    public $categories = [];
    public $tags = [];

    //#! Constructor
    public function mount()
    {

    }

    public function render()
    {
        return view( 'livewire.blog-sidebar' );
    }
}
