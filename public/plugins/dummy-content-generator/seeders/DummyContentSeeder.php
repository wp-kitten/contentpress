<?php
namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryMeta;
use App\Helpers\CPML;
use App\Helpers\MetaFields;
use App\Helpers\Util;
use App\Models\Post;
use App\Models\PostMeta;
use App\Models\PostStatus;
use App\Models\PostType;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $postTypeID = PostType::where( 'name', 'post' )->first()->id;
        $languageID = CPML::getDefaultLanguageID();

        //#! Create categories and add meta fields
        $categories = [
            //#! Name
            'Animals',
            'Architecture',
            'Business & Work',
            'Fashion',
            'General',
            'Mountains',
            'Ships',
            'Sports',
            'Technology',
            'Travel',
            'Uncategorized',
            'Wallpapers',
        ];

        $catClass = new Category();

        foreach ( $categories as $categoryName ) {
            if ( !$catClass->exists( $categoryName ) ) {
                $category = $catClass->create( [ 'name' => Str::title( $categoryName ), 'slug' => Str::slug( $categoryName ), 'language_id' => $languageID, 'post_type_id' => $postTypeID, ] );
                if ( $category && $category->id ) {
                    //#! Add meta fields
                    CategoryMeta::create( [
                        'meta_name' => '_category_image',
                        'meta_value' => '',
                        'category_id' => $category->id,
                        'language_id' => $category->language_id,
                    ] );
                }
            }
        }

        //#! Tags
        $tags = [
            //#! Name
            'Animals',
            'Architecture',
            'Business',
            'Computers',
            'Fashion',
            'General',
            'IT',
            'Lorem Ipsum',
            'Man',
            'Mountains',
            'Office',
            'Ships',
            'Sports',
            'Technology',
            'Travel',
            'Wallpapers',
            'Woman',
            'Work',
        ];
        $tagClass = new Tag();
        foreach ( $tags as $tagName ) {
            if ( !$tagClass->exists( $tagName ) ) {
                $tagClass->create( [ 'name' => Str::title( $tagName ), 'slug' => Str::slug( $tagName ), 'language_id' => $languageID, 'post_type_id' => $postTypeID, ] );
            }
        }

        $postStatusID = PostStatus::where( 'name', 'publish' )->first()->id;
        $currentUserID = cp_get_current_user()->getAuthIdentifier();
        $defaultLanguageID = CPML::getDefaultLanguageID();

        //#! Pages
        $pages = [ 'Home', 'Blog', 'About', 'Contact', 'Thank you', 'Cookie policy', 'Privacy policy' ];
        $postTypeId = PostType::where( 'name', 'page' )->first()->id;
        foreach ( $pages as $title ) {
            Post::create( [
                'title' => Str::title( $title ),
                'slug' => Str::slug( $title ),
                'content' => '',
                'user_id' => $currentUserID,
                'language_id' => $defaultLanguageID,
                'post_type_id' => $postTypeId,
                'post_status_id' => $postStatusID,
            ] );
        }

        //#! Posts
        $posts = [
            'Hello World!' => [
                'category_id' => Category::where( 'name', 'General' )->first()->id,
                'tag_id' => Tag::where( 'name', 'General' )->first()->id,
                'content' => '<h2>What is Lorem Ipsum?</h2>
<p><strong>Lorem Ipsum</strong>&nbsp;is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>
<h2>Where does it come from?</h2>
<p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of "de Finibus Bonorum et Malorum" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.</p>
<p>The standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections 1.10.32 and 1.10.33 from "de Finibus Bonorum et Malorum" by Cicero are also reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.</p>
<h2>Why do we use it?</h2>
<p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).</p>
<h2>Where can I get some?</h2>
<p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don\'t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn\'t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</p>
<p>&nbsp;</p>',
                'excerpt' => 'Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature',
                'comments_enabled' => true,
            ],
        ];
        $postTypeId = PostType::where( 'name', 'post' )->first()->id;

        foreach ( $posts as $title => $info ) {
            $post = Post::create( [
                'title' => Str::title( $title ),
                'slug' => Str::slug( $title ),
                'content' => $info[ 'content' ],
                'excerpt' => $info[ 'excerpt' ],
                'user_id' => $currentUserID,
                'language_id' => $defaultLanguageID,
                'post_type_id' => $postTypeId,
                'post_status_id' => $postStatusID,
            ] );
            if ( $post ) {
                //#! Update post meta
                if ( cp_current_user_can( 'manage_custom_fields' ) ) {
                    if ( $meta = MetaFields::getInstance( new PostMeta(), 'post_id', $post->id, '_comments_enabled', $defaultLanguageID ) ) {
                        $meta->meta_value = $info[ 'comments_enabled' ];
                        $meta->update();
                    }
                    else {
                        MetaFields::add( new PostMeta(), 'post_id', $post->id, '_comments_enabled', $info[ 'comments_enabled' ], $defaultLanguageID );
                    }
                }

                //#! Set category & tag
                if ( cp_current_user_can( 'manage_taxonomies' ) ) {
                    $post->categories()->detach();
                    $post->categories()->attach( $info[ 'category_id' ] );

                    $post->tags()->detach();
                    $post->tags()->attach( $info[ 'tag_id' ] );
                }
            }
        }
    }
}
