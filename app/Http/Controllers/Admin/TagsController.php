<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Util;
use App\Models\Language;
use App\Models\Options;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagsController extends PostsController
{
    public function index()
    {
        if ( !vp_current_user_can( 'manage_taxonomies' ) ) {
            return $this->_forbidden();
        }

        return view( 'admin.post.tags' )->with( [
            'tags' => Tag::where( 'post_type_id', $this->_postType->id )
                ->where( 'language_id', $this->_postType->language_id )
                ->orderBy( 'name', 'ASC' )
                ->paginate( $this->settings->getSetting( 'posts_per_page', 10 ) ),

            //#! Special entry
            //@required
            '__post_type' => $this->_postType,
            'enabled_languages' => ( new Options() )->getOption( 'enabled_languages', [] ),
        ] );
    }

    public function showEditPage( $id )
    {
        if ( !vp_current_user_can( 'manage_taxonomies' ) ) {
            return $this->_forbidden();
        }

        return view( 'admin.post.tag_edit' )->with( [
            'enabled_languages' => ( new Options() )->getOption( 'enabled_languages', [] ),
            'default_language_code' => $this->settings->getSetting( 'default_language' ),
            'tag' => Tag::find( $id ),
            //#! Special entry
            //@required
            '__post_type' => $this->_postType,
        ] );
    }

    public function __insert( Request $request )
    {
        if ( !vp_current_user_can( 'manage_taxonomies' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.You are not allowed to perform this action' ),
            ] );
        }

        $request->validate( [
            'name' => 'required',
        ] );

        $name = $request->get( 'name' );
        $language_id = $this->_postType->language_id;

        $slug = Str::slug( $name );
        if ( !Util::isUniqueTagSlug( $slug, $language_id, $this->_postType->id ) ) {
            $slug = Str::slug( $name . '-' . time() );
        }

        $r = Tag::create( [
            'name' => $name,
            'slug' => $slug,
            'language_id' => $language_id,
            'post_type_id' => $this->_postType->id,
        ] );

        if ( $r ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success', // success or danger on error
                'text' => __( 'a.Tag added' ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'a.Tag not added' ),
        ] );
    }

    public function __update( $id )
    {
        if ( !vp_current_user_can( 'manage_taxonomies' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.You are not allowed to perform this action' ),
            ] );
        }

        $request = \request();

        $request->validate( [
            'name' => 'required',
            'language_id' => 'required',
        ] );

        $name = $request->get( 'name' );
        $tagLanguageID = $request->get( 'language_id' );

        if ( !$tag = Tag::find( $id ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.Tag not found' ),
            ] );
        }

        if ( !$language = Language::find( $id ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.Language not found' ),
            ] );
        }

        $slug = null;

        if ( $name != $tag->name ) {
            $slug = Str::slug( $name );
            if ( !Util::isUniqueTagSlug( $slug, $tagLanguageID, $this->_postType->id ) ) {
                $slug = Str::slug( $name . '-' . time() );
            }
        }

        if ( $name != $tag->name ) {
            $tag->name = $name;
            $tag->slug = $slug;
        }
        $tag->language_id = $tagLanguageID;
        $tag->post_type_id = $this->_postType->id;

        $r = $tag->update();

        if ( $r ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success', // success or danger on error
                'text' => __( 'a.Tag updated' ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'a.Tag not updated' ),
        ] );
    }

    public function __delete( $id )
    {
        if ( !vp_current_user_can( 'manage_taxonomies' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.You are not allowed to perform this action' ),
            ] );
        }

        $result = Tag::destroy( $id );
        if ( $result ) {
            //#! Delete translations as well
            Tag::where( 'translated_tag_id', $id )->delete();

            return redirect()->route( "admin.{$this->_postType->name}.tag.all" )->with( 'message', [
                'class' => 'success',
                'text' => __( 'a.Tag deleted' ),
            ] );
        }

        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.The specified tag could not be deleted' ),
        ] );
    }

    public function __translate( $language_id )
    {
        if ( !vp_current_user_can( 'manage_taxonomies' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.You are not allowed to perform this action' ),
            ] );
        }

        $request = \request();

        $request->validate( [
            'name' => 'required',
            'translated_tag_id' => 'required',
        ] );

        $translated_tag_id = $request->get( 'translated_tag_id' );
        if ( !$translatedTag = Tag::find( $translated_tag_id ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.Tag not found' ),
            ] );
        }

        //#!
        $name = $request->get( 'name' );
        $current_tag_id = $request->get( 'current_tag_id' );
        $slug = null;

        //#! CREATE
        if ( empty( $current_tag_id ) ) {
            $slug = Str::slug( $name );
            if ( !Util::isUniqueTagSlug( $slug, $language_id, $this->_postType->id ) ) {
                $slug = Str::slug( $name . '-' . time() );
            }
            $r = Tag::create( [
                'name' => $name,
                'slug' => $slug,
                'language_id' => $language_id,
                'post_type_id' => $this->_postType->id,
                'translated_tag_id' => $translated_tag_id,
            ] );
        }
        //#! UPDATE
        else {
            if ( !$currentTag = Tag::find( $current_tag_id ) ) {
                return redirect()->back()->with( 'message', [
                    'class' => 'danger', // success or danger on error
                    'text' => __( 'a.Tag not found' ),
                ] );
            }

            if ( $name != $currentTag->name ) {
                $slug = Str::slug( $name );
                if ( !Util::isUniqueTagSlug( $slug, $language_id, $this->_postType->id, $current_tag_id ) ) {
                    $slug = Str::slug( $name . '-' . time() );
                }
                $currentTag->name = $name;
                $currentTag->slug = $slug;
            }
            $currentTag->language_id = $language_id;
            $currentTag->post_type_id = $this->_postType->id;
            $currentTag->translated_tag_id = $translated_tag_id;
            $r = $currentTag->update();
        }

        if ( $r ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success', // success or danger on error
                'text' => __( 'a.Tag updated' ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'a.Tag not updated' ),
        ] );
    }

}
