<?php

namespace App\Http\Controllers;

use App\Category;
use App\Http\Controllers\Admin\AdminControllerBase;
use App\Newspaper\NewspaperHelper;
use App\Options;

class NewspaperAdminController extends AdminControllerBase
{
    /**
     * Stores the name of the option holding the theme's options
     * @var string
     */
    const THEME_OPTIONS_OPT_NAME = 'newspaper-theme-options';

    /**
     * Stores the list of the theme's default options
     * @var array[]
     */
    private static $_defaultOptions = [
        'featured_categories' => [],
    ];

    /**
     * Store the theme's options
     * @var array[]
     */
    private static $_themeOptions = [];

    public function __construct()
    {
        parent::__construct();

        self::$_themeOptions = $this->options->getOption( self::THEME_OPTIONS_OPT_NAME, self::$_defaultOptions );
    }

    public function themeOptionsPageView()
    {
        $nh = new NewspaperHelper();
        return view( '_admin/theme-options' )->with( [
            'categories' => $nh->getCategoriesTree(),
            'options' => self::$_themeOptions,
        ] );
    }

    //#! [post]
    public function themeOptionsSave()
    {
        $themeOptions = $this->options->getOption(self::THEME_OPTIONS_OPT_NAME, []);

        //#! Homepage options
        $options = $this->request->get('homepage');
        if( ! isset($themeOptions['homepage'])){
            $themeOptions['homepage'] = [];
        }
        $homepageOptions = $themeOptions['homepage'];
        if(! empty($options)){
            foreach($options as $sectionID => $catID){
                $theCat = Category::find($catID);
                if($theCat){
                    $homepageOptions[$sectionID] = $catID;
                }
            }
        }
        $themeOptions['homepage'] = $homepageOptions;

        //...

        //#! Save options
        $this->options->addOption( self::THEME_OPTIONS_OPT_NAME, $themeOptions );

        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'np::m.Options saved.' ),
        ] );
    }

    /**
     * Retrieve all theme options
     * @return array
     */
    public static function getThemeOptions(): array
    {
        if ( empty( self::$_themeOptions ) ) {
            $option = Options::where( 'name', self::THEME_OPTIONS_OPT_NAME )->first();
            if ( $option ) {
                self::$_themeOptions = maybe_unserialize( $option->value );
            }
            else {
                self::$_themeOptions = self::$_defaultOptions;
            }
        }
        return self::$_themeOptions;
    }

    /**
     * Retrieve the default theme options
     * @return array[]
     */
    public static function getDefaultThemeOptions(): array
    {
        return self::$_defaultOptions;
    }
}
