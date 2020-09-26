<?php

use App\Models\Post;
use App\Models\PostMeta;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/*
 * Add custom routes or override existent ones
 */

Route::post( 'admin/post-duplicator/duplicate/{id}', function ( $postID ) {

    $post = Post::find( $postID );
    if ( !$post ) {
        return redirect()->back()->with( 'message', [
            'class' => 'warning',
            'text' => __( 'cppd::m.Post not found.' ),
        ] );
    }

    //#! clone post
    $clone = $post->replicate();

    //#! Update title, slug
    $postTitle = $clone->title . '-copy';
    $clone->title = Str::title( $postTitle );
    $clone->slug = Str::slug( $postTitle );
    $clone->save();

    if ( !$clone || !$clone->id ) {
        return redirect()->back()->with( 'message', [
            'class' => 'warning',
            'text' => __( 'cppd::m.Could not duplicate the post.' ),
        ] );
    }

    $newPostID = $clone->id;

    //#! clone meta
    $postMetas = $post->post_metas()->get();
    if ( !empty( $postMetas ) ) {
        foreach ( $postMetas as $entry ) {
            PostMeta::create( [
                'post_id' => $newPostID,
                'language_id' => $entry->language_id,
                'meta_name' => $entry->meta_name,
                'meta_value' => $entry->meta_value,
            ] );
        }
    }

    //#! clone tags
    $postTags = $post->tags;
    if ( !empty( $postTags ) ) {
        $ids = [];
        foreach ( $postTags as $entry ) {
            array_push( $ids, $entry->id );
        }
        $clone->tags()->attach( $ids );
    }

    //#! clone categories
    $postCategories = $post->categories;
    if ( !empty( $postCategories ) ) {
        $ids = [];
        foreach ( $postCategories as $entry ) {
            array_push( $ids, $entry->id );
        }
        $clone->categories()->attach( $ids );
    }

    return redirect()->back()->with( 'message', [
        'class' => 'success',
        'text' => __('cppd::m.Post cloned.'),
    ] );
} )->middleware( [ 'web', 'auth', 'active_user' ] )->name( 'admin.post_duplicator.duplicate' );
