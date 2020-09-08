<?php

namespace App\Helpers;

use App\Category;
use App\PostType;

class CategoriesWalker
{
    /**
     * Holds the base path to categories page
     * @var string
     */
    private $baseRoute = '';

    /**
     * The list of options to configure the output
     * @var array
     */
    private $options = [];

    /**
     * @var PostType
     */
    private $postType;

    /**
     * Holds the list of the categories tree
     * @var array
     * @see listCategories()
     */
    private $list = [];

    /**
     * CategoriesWalker constructor.
     * @param PostType $postType
     * @param array $options The options to customize the output
     */
    public function __construct( PostType $postType, $options = [] )
    {
        $this->postType = $postType;
        $this->baseRoute = "admin.{$postType->name}";
        $this->options = $options;
    }

    /**
     * Retrieve all parent categories
     * @return mixed
     */
    public function getCategories()
    {
        return Category::where( 'post_type_id', $this->postType->id )
            ->where( 'category_id', null )
            ->where( 'language_id', $this->postType->language_id )
            ->oldest()
            ->get();
    }

    /**
     * Check to see whether or not there are any categories
     * @return bool
     */
    public function hasCategories()
    {
        return ( count( $this->getCategories() ) > 0 );
    }

    /**
     * Display the categories tree
     */
    public function renderCategories()
    {
        if ( $this->hasCategories() ) {
            $categories = $this->getCategories();
            echo '<ul class="list-unstyled mt-4 category-list js-sortable">';
            foreach ( $categories as $category ) {
                $this->renderCategory( $category );
            }
            echo '</ul>';
        }
    }

    /**
     * Helper method to retrieve the categories tree as an associated array
     * @return array
     */
    public function listCategories()
    {
        if ( $this->hasCategories() ) {
            $categories = $this->getCategories();
            foreach ( $categories as $category ) {
                $entry = [
                    'id' => $category->id,
                    'name' => $category->name,
                ];
                array_push( $this->list, $entry );
                $this->__processSubcategories( $category );
            }
        }

        return $this->list;
    }

    /**
     * Internal helper method to process subcategories and build the list
     * @param Category $category
     * @internal
     * @private
     * @see listCategories()
     */
    private function __processSubcategories( Category $category )
    {
        $subcategories = $this->__getSubcategories( $category );
        foreach ( $subcategories as $subcategory ) {
            $entry = [
                'id' => $subcategory->id,
                'name' => $subcategory->name,
            ];
            array_push( $this->list, $entry );

            $this->__processSubcategories( $subcategory );
        }
    }

    /**
     * Helper method to retrieve the list of subcategories
     * @param Category $category
     * @return array
     */
    public function getSubcategories( Category $category )
    {
        $theList = $this->list;
        $this->__processSubcategories( $category );
        $subcategories = $this->list;
        //#! Restore old state
        $this->list = $theList;
        return $subcategories;
    }

    //<editor-fold desc="PROTECTED METHODS">

    /**
     * Render the specified category
     * @param Category $category
     */
    protected function renderCategory( Category $category )
    {
        ?>
        <li id="category_<?php esc_attr_e( $category->id ); ?>" data-id="<?php esc_attr_e( $category->id ); ?>" class="list-item">
            <div>
                <a class="cp-text-dark"
                   href="<?php esc_attr_e( route( 'blog.category', $category->slug ) ); ?>"
                   title="<?php esc_attr_e( __( 'a.View' ) ); ?>"
                   target="_blank">
                    <?php
                    if ( CPML::categoryMissingTranslations( $category->id ) ) {
                        echo '<span class="bullet danger" title="'.esc_html(__('a.This category is missing translations.')).'"></span>';
                    }
                    else {
                        echo '<span class="bullet success" title="'.esc_html(__('a.This category has translations for all enabled languages.')).'"></span>';
                    }
                    ?>
                    <?php echo utf8_encode( $category->name ); ?>
                </a>
                <?php $this->renderActions( $category ); ?>
            </div>

            <?php $this->renderSubcategories( $category ); ?>
        </li>
        <?php
    }

    /**
     * Retrieve all replies for the given $category
     * @param Category $category
     * @return mixed
     */
    protected function __getSubcategories( Category $category )
    {
        return $category
            ->where( 'post_type_id', $this->postType->id )
            ->where( 'category_id', $category->id )
            ->where( 'language_id', $category->language_id )
            ->get();
    }

    /**
     * Recursively render all replies to the given $category
     * @param Category $category
     */
    protected function renderSubcategories( Category $category )
    {
        $subcategories = $this->__getSubcategories( $category );
        //#! Display the list by default so it can serve as a parent category since sortable is enabled
        echo '<ul class="list-unstyled mt-2 mb-2 subcategory-list">';
        if ( $subcategories->count() ) {
            foreach ( $subcategories as $subcategory ) {
                ?>
                <li id="category_<?php esc_attr_e( $subcategory->id ); ?>" data-id="<?php esc_attr_e( $subcategory->id ); ?>" class="list-item">
                    <div>
                        <a class="cp-text-dark"
                           href="<?php esc_attr_e( route( 'blog.category', $subcategory->slug ) ); ?>"
                           title="<?php esc_attr_e( __( 'a.View' ) ); ?>"
                           target="_blank">
                            <?php
                            if ( CPML::categoryMissingTranslations( $subcategory->id ) ) {
                                echo '<span class="bullet danger" title="'.esc_html(__('a.This category is missing translations.')).'"></span>';
                            }
                            else {
                                echo '<span class="bullet success" title="'.esc_html(__('a.This category has translations for all enabled languages.')).'"></span>';
                            }
                            ?>
                            <?php echo utf8_encode( $subcategory->name ); ?>
                        </a>

                        <?php $this->renderActions( $subcategory ); ?>
                    </div>

                    <?php $this->renderSubcategories( $subcategory ); ?>
                </li>
                <?php
            }
        }
        echo '</ul>';
    }

    protected function renderActions( Category $category )
    {
        ?>
        <span class="category-actions">
        (
            <?php if ( cp_is_multilingual() ) { ?>
                <a href="#"
                   data-toggle="modal"
                   data-target="#infoModal"
                   class="text-primary js-cat-show-translations"
                   data-category-id="<?php esc_attr_e( $category->id ); ?>">
                    <?php esc_html_e( __( 'a.Translations' ) ); ?>
                </a>
                |
            <?php } ?>
            <a href="<?php esc_attr_e( route( "{$this->baseRoute}.category.edit", [ 'id' => $category->id ] ) ); ?>" class="text-primary">
                <?php esc_html_e( __( 'a.Edit' ) ); ?>
            </a>
            |
            <a href="<?php esc_attr_e( route( "{$this->baseRoute}.category.delete", [ 'id' => $category->id ] ) ); ?>"
               data-confirm="<?php esc_html_e( __( 'a.Are you sure you want to delete this category?' ) ); ?>"
               class="text-danger">
                <?php esc_html_e( __( 'a.Delete' ) ); ?>
            </a>
        )
        </span>
        <?php
    }

    //</editor-fold desc="PROTECTED METHODS">
}
