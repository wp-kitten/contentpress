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
        $postType = PostType::where( 'name', 'post' )->first();
        $languageID = cp_get_frontend_user_language_id();
        $this->tags = Tag::where( 'post_type_id', $postType->id )->latest( 'language_id', $languageID )->limit( 20 )->get();
        $_categories = Category::where( 'post_type_id', $postType->id )->where( 'language_id', $languageID )->where( 'category_id', null )->latest()->get();
        if ( $_categories ) {
            foreach ( $_categories as $category ) {
                $this->categories[ $category->id ] = [
                    'category' => $category,
                    'num_posts' => $category->posts()->count(),
                ];
            }
        }
    }

    public function render()
    {
        return view( 'livewire.blog-sidebar' );
    }
}
