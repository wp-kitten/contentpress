<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\CategoryMeta;
use App\Helpers\CategoriesWalker;
use App\Helpers\CPML;
use App\Helpers\MetaFields;
use App\Helpers\ScriptsManager;
use App\Helpers\Util;
use App\Models\Language;
use App\Models\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CategoriesController extends PostsController
{
    public function index()
    {
        if ( !cp_current_user_can( 'manage_taxonomies' ) ) {
            return $this->_forbidden();
        }

        ScriptsManager::enqueueStylesheet( 'dropify.min.css', asset( 'vendor/dropify/css/dropify.min.css' ) );
        ScriptsManager::enqueueStylesheet( 'categories-index.css', asset( '_admin/css/categories/index.css' ) );

        ScriptsManager::localizeScript( 'categories-index-scripts', 'CategoriesIndexLocale', [
            'confirm_delete_category' => __( 'a.Are you sure you want to delete this category? All items subcategories will also be deleted.' ),
            'description_placeholder' => __( 'a.Short description here...' ),
            'text_error' => __( 'a.Error' ),
            'text_error_category_id_missing' => __( 'a.The category ID is missing.' ),
            'text_translations' => __( 'a.Translations' ),
        ] );

        ScriptsManager::enqueueFooterScript( 'jquery-sortable', asset( 'vendor/jquery-sortable/jquery-sortable.js' ) );
        ScriptsManager::enqueueFooterScript( 'dropify.min.js', asset( 'vendor/dropify/js/dropify.min.js' ) );
        ScriptsManager::enqueueFooterScript( 'DropifyImageUploader.js', asset( '_admin/js/DropifyImageUploader.js' ) );
        ScriptsManager::enqueueFooterScript( 'categories-index.js', asset( '_admin/js/categories/index.js' ) );

        //#! Load scripts & inject the markup for the media modal
        cp_enqueue_media_scripts();

        $walker = new CategoriesWalker( $this->_postType, [] );
        return view( 'admin.post.categories' )->with( [
            'walker' => $walker,
            'categories' => Arr::pluck( $walker->listCategories(), 'name', 'id' ),
            //#! Special entry
            //@required
            '__post_type' => $this->_postType,
        ] );
    }

    public function showEditPage( $id )
    {
        if ( !cp_current_user_can( 'manage_taxonomies' ) ) {
            return $this->_forbidden();
        }

        ScriptsManager::enqueueStylesheet( 'dropify.min.css', asset( 'vendor/dropify/css/dropify.min.css' ) );

        ScriptsManager::localizeScript( 'categories-edit-scripts', 'CategoriesIndexLocale', [
            'confirm_delete_category' => __( 'a.Are you sure you want to delete this category?' ),
            'description_placeholder' => __( 'a.Short description here...' ),
        ] );

        ScriptsManager::enqueueFooterScript( 'dropify.min.js', asset( 'vendor/dropify/js/dropify.min.js' ) );
        ScriptsManager::enqueueFooterScript( 'DropifyImageUploader.js', asset( '_admin/js/DropifyImageUploader.js' ) );
        ScriptsManager::enqueueFooterScript( 'categories-edit.js', asset( '_admin/js/categories/edit.js' ) );

        //#! Load scripts & inject the markup for the media modal
        cp_enqueue_media_scripts();

        MetaFields::generateProtectedMetaFields( new CategoryMeta(), 'category_id', $id, MetaFields::SECTION_CATEGORY );

        $category = Category::find( $id );

        return view( 'admin.post.category_edit' )->with( [
            'enabled_languages' => ( new Options() )->getOption( 'enabled_languages', [] ),
            'default_language_code' => $this->settings->getSetting( 'default_language' ),
            'categories' => Category::where( 'post_type_id', $this->_postType->id )
                ->where( 'id', '!=', $id )
                ->where( 'language_id', $this->_postType->language_id )
                ->get(),
            'category' => $category,
            //#! Special entry
            //@required
            '__post_type' => $this->_postType,
            'meta_fields' => MetaFields::getAll( new CategoryMeta(), 'category_id', $id, $category->language_id ),
        ] );
    }

    public function __insert( Request $request )
    {
        if ( !cp_current_user_can( 'manage_taxonomies' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        $request->validate( [
            'name' => 'required',
            'category_id' => 'required',
        ] );

        $name = $request->get( 'name' );
        $description = $request->get( 'description' );
        $category_id = $request->get( 'category_id' );
        $language_id = $this->_postType->language_id;

        $slug = Str::slug( $name );
        if ( !Util::isUniqueCategorySlug( $slug, $language_id, $this->_postType->id ) ) {
            $slug = Str::slug( $name . '-' . time() );
        }

        //#! WP like, preserve the selected category
        session()->put( 'previously_selected', $category_id );

        $r = Category::create( [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'language_id' => $language_id,
            'post_type_id' => $this->_postType->id,
            'category_id' => ( empty( $category_id ) ? null : $category_id ),
        ] );

        if ( $r ) {
            //#! Check for image
            $imageID = $this->request->get( '__category_image_id' );

            //#! Check if meta exists, since this meta is protected, it might have been created
            $meta = CategoryMeta::where( 'category_id', $r->id )
                ->where( 'language_id', CPML::getDefaultLanguageID() )
                ->where( 'meta_name', '_category_image' )
                ->first();

            if ( $meta ) {
                $meta->meta_value = $imageID;
                $meta->update();
            }
            else {
                CategoryMeta::create( [
                    'category_id' => $r->id,
                    'language_id' => CPML::getDefaultLanguageID(),
                    'meta_name' => '_category_image',
                    'meta_value' => $imageID,
                ] );
            }

            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( 'a.Category added.' ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'a.Category not added.' ),
        ] );
    }

    public function __update( $id )
    {
        if ( !cp_current_user_can( 'manage_taxonomies' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        $request = \request();

        $request->validate( [
            'name' => 'required',
            'category_id' => 'required',
        ] );

        if ( !$category = Category::find( $id ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.Category not found.' ),
            ] );
        }

        $name = $request->get( 'name' );
        $description = $request->get( 'description' );
        $category_id = $request->get( 'category_id' );
        $language_id = (empty($request->get('language_id')) ? $this->_postType->language_id : $request->get('language_id'));
        $slug = null;

        if ( $name != $category->name ) {
            $slug = Str::slug( $name );
            if ( !Util::isUniqueCategorySlug( $slug, $language_id, $this->_postType->id ) ) {
                $slug = Str::slug( $name . '-' . time() );
            }
        }

        if ( $name != $category->name ) {
            $category->name = $name;
            $category->slug = $slug;
        }
        $category->description = $description;
        $category->language_id = $language_id;
        $category->post_type_id = $this->_postType->id;
        $category_id = ( empty( $category_id ) ? null : $category_id );
        $category->category_id = $category_id;

        $r = $category->update();

        if ( $r ) {
            //#! Check for image
            $imageID = $this->request->get( '__category_image_id' );

            //#! Check if meta exists, since this meta is protected, it might have been created
            $meta = CategoryMeta::where( 'category_id', $id )
                ->where( 'language_id', CPML::getDefaultLanguageID() )
                ->where( 'meta_name', '_category_image' )
                ->first();

            if ( $meta ) {
                $meta->meta_value = $imageID;
                $meta->update();
            }
            else {
                CategoryMeta::create( [
                    'category_id' => $id,
                    'language_id' => CPML::getDefaultLanguageID(),
                    'meta_name' => '_category_image',
                    'meta_value' => $imageID,
                ] );
            }

            return redirect()->back()->with( 'message', [
                'class' => 'success', // success or danger on error
                'text' => __( 'a.Category updated' ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'a.Category not updated' ),
        ] );
    }

    public function __delete( $id )
    {
        if ( !cp_current_user_can( 'manage_taxonomies' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.You are not allowed to perform this action' ),
            ] );
        }

        $category = Category::find( $id );

        //#! Delete the featured images of all subcategories
        $cw = new CategoriesWalker( $this->_postType );
        $subcategories = $cw->getSubcategories( $category );
        if ( $subcategories ) {
            $subcategories = Arr::pluck( $subcategories, 'id' );

            if ( !empty( $subcategories ) ) {
                foreach ( $subcategories as $subCatId ) {
                    $meta = CategoryMeta::where( 'category_id', $subCatId )
                        ->where( 'language_id', CPML::getDefaultLanguageID() )
                        ->where( 'meta_name', '_category_image' )
                        ->first();
                    if ( $meta && !empty( $meta->meta_value ) ) {
                        $fileName = $meta->meta_value;
                        if ( !empty( $fileName ) ) {
                            $filePath = public_path( "uploads/" ) . $fileName;
                            if ( File::isFile( $filePath ) ) {
                                File::delete( $filePath );
                            }
                        }
                    }
                }
            }
        }

        //#! Delete the associated image if any of the category being deleted
        $meta = CategoryMeta::where( 'category_id', $id )
            ->where( 'language_id', CPML::getDefaultLanguageID() )
            ->where( 'meta_name', '_category_image' )
            ->first();
        $fileName = '';
        if ( $meta && !empty( $meta->meta_value ) ) {
            $fileName = $meta->meta_value;
        }

        $result = Category::destroy( $id );
        if ( $result ) {
            //#! Here, after the category is deleted
            if ( !empty( $fileName ) ) {
                $filePath = public_path( "uploads/" ) . $fileName;
                if ( File::isFile( $filePath ) ) {
                    File::delete( $filePath );
                }
            }

            return redirect()->route( "admin.{$this->_postType->name}.category.all" )->with( 'message', [
                'class' => 'success',
                'text' => __( 'a.Category deleted' ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.The specified category could not be deleted' ),
        ] );
    }

    public function __translate( $category_id, $language_id )
    {
        if ( !cp_current_user_can( 'manage_taxonomies' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.You are not allowed to perform this action' ),
            ] );
        }

        $request = \request();

        $request->validate( [
            'name' => 'required',
        ] );

        // $category -> the category being translated
        if ( !$category = Category::find( $category_id ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.Category not found [1]' ),
            ] );
        }

        if ( !Language::find( $language_id ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.Language not found' ),
            ] );
        }

        $name = $request->get( 'name' );
        $description = $request->get( 'description' );
        $current_category_id = $request->get( 'current_category_id' );
        $current_category = ( empty( $current_category_id ) ? null : Category::find( $current_category_id ) );

        $slug = null;

        // If this is an edit
        if ( $current_category ) {
            if ( $name != $current_category->name ) {
                $slug = Str::slug( $name );
                if ( !Util::isUniqueCategorySlug( $slug, $language_id, $this->_postType->id, $current_category_id ) ) {
                    $slug = Str::slug( $name . '-' . time() );
                }
                $current_category->name = $name;
                $current_category->slug = $slug;
            }
            $current_category->description = $description;
            $current_category->language_id = $language_id;
            $current_category->post_type_id = $this->_postType->id;
            $current_category->category_id = $category_id;
            $r = $current_category->update();
        }
        //#! Create
        else {
            $slug = Str::slug( $name );
            if ( !Util::isUniqueCategorySlug( $slug, $language_id, $this->_postType->id ) ) {
                $slug = Str::slug( $name . '-' . time() );
            }
            $r = Category::create( [
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'language_id' => $language_id,
                'category_id' => $category_id,
                'post_type_id' => $this->_postType->id,
            ] );
        }

        if ( $r ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success', // success or danger on error
                'text' => __( 'a.Category updated' ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'a.Category not updated' ),
        ] );
    }

    public function __translateCreate( $category_id, $language_id )
    {
        if ( !cp_current_user_can( 'manage_taxonomies' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.You are not allowed to perform this action' ),
            ] );
        }

        // $category -> the category being translated
        if ( !$category = Category::find( $category_id ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.Category not found [1]' ),
            ] );
        }

        if ( !Language::find( $language_id ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.Language not found' ),
            ] );
        }

        $catName = $category->name . '-' . time();
        $slug = Str::slug( $catName );
        $r = Category::create( [
            'name' => $catName,
            'slug' => $slug,
            'description' => '',
            'post_type_id' => $this->_postType->id,
            'language_id' => $language_id,
            'translated_category_id' => $category_id
        ] );

        if ( $r ) {
            return redirect()->route("admin.{$this->_postType->name}.category.edit", $r->id)->with( 'message', [
                'class' => 'success', // success or danger on error
                'text' => __( 'a.Translation created.' ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger', // success or danger on error
            'text' => __( 'a.The translation could not be created.' ),
        ] );
    }
}
