<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class AdminBar
{
    /**
     * Stores the reference to the instance of this class
     * @var null|AdminBar
     */
    private static $_instance = null;

    /**
     * AdminBar constructor.
     *
     * @uses apply_filters( 'valpress/admin-bar/show', true );
     * @uses add_filter( 'valpress/body-class', [ $this, 'addBodyClass' ] );
     * @uses add_action( 'valpress/site/head', [ $this, 'printHeadStyles' ], 200 );
     * @uses add_action( 'valpress/site/footer', [ $this, 'printFooterScript' ], 200 );
     * @uses add_action( 'valpress/after_body_open', [ $this, 'render' ], 0 );
     */
    private function __construct()
    {
        if ( apply_filters( 'valpress/admin-bar/show', true ) && cp_is_user_logged_in() ) {
            add_action( 'valpress/site/head', [ $this, 'printHeadStyles' ], 200 );
            add_action( 'valpress/site/footer', [ $this, 'printFooterScript' ], 200 );
            add_filter( 'valpress/body-class', [ $this, 'addBodyClass' ] );
            add_action( 'valpress/after_body_open', [ $this, 'render' ], 0 );
        }
    }

    /**
     * Retrieve the reference to the instance of this class
     * @return self
     */
    public static function getInstance(): ?AdminBar
    {
        if ( is_null( self::$_instance ) || !( self::$_instance instanceof self ) ) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * Print head styles
     */
    public function printHeadStyles()
    {
        ?>
        <style id="admin-bar-styles" type="text/css">
            body.has-admin-bar {
                padding-top: 42px;
            }

            .admin-bar {
                padding: .2rem 1rem !important;
            }
        </style>
        <?php
    }

    public function printFooterScript()
    {
        ?>
        <script id="admin-bar-js">
            jQuery( function ($) {
                "use strict";
                //#! Activate parent if any of the submenu items is active
                $( '.admin-bar .dropdown-item' ).each( function (i, e) {
                    var self = $( e );
                    if ( self.hasClass( 'active' ) ) {
                        self.parents( '.nav-item.dropdown' ).first().addClass( 'active' );
                    }
                } );
            } );
        </script>
        <?php
    }

    /**
     * Attach the class to the body element indicating the admin bar is showed
     * @param array $classes
     * @return array
     */
    public function addBodyClass( $classes = [] ): array
    {
        $classes[] = 'has-admin-bar';
        return $classes;
    }

    /**
     * Render the admin bar. Visible by default to authenticated users
     *
     * @uses apply_filters( 'valpress/admin-bar/entries', [] )
     */
    public function render()
    {
        $entries = apply_filters( 'valpress/admin-bar/entries', [
            'dashboard' => [
                'title' => __( 'a.Go to dashboard' ),
                'text' => __( 'a.Dashboard' ),
                'url' => route( 'admin.dashboard' ),
            ],
        ] );
        ?>
        <nav class="admin-bar navbar navbar-dark bg-dark fixed-top navbar-expand-md">
            <a class="navbar-brand" href="<?php esc_attr_e( route( 'app.home' ) ); ?>" title="<?php esc_attr_e( __( 'a.Home' ) ); ?>">
                <i class="fas fa-home"></i>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse"
                    data-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent"
                    aria-expanded="false"
                    aria-label="<?php esc_attr_e( __( 'a.Toggle navigation' ) ); ?>">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav">
                    <?php
                    foreach ( $entries as $id => $entry ) {
                        //#! Submenu
                        if ( isset( $entry[ 'submenu' ] ) && !empty( $entry[ 'submenu' ] ) ) {
                            echo '<li class="nav-item dropdown">';
                            echo '<a class="nav-link dropdown-toggle" id="navbarDropdownMenuLink-' . esc_attr( $id ) . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="' . esc_attr( $entry[ 'title' ] ) . '" href="#">' . $entry[ 'text' ] . '</a>';

                            echo '<div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink-' . esc_attr( $id ) . '">';
                            foreach ( $entry[ 'submenu' ] as $k => $item ) {
                                $activeClass = ( Str::containsAll( url()->current(), [ $item[ 'url' ] ] ) ? 'active' : '' );
                                echo '<a class="dropdown-item ' . esc_attr( $activeClass ) . '" id="dropdown-menu-item-' . esc_attr( $k ) . '" title="' . esc_attr( $item[ 'title' ] ) . '" href="' . esc_attr( $item[ 'url' ] ) . '">' . $item[ 'text' ] . '</a>';
                            }
                            echo '</div>';
                            echo '</li>';
                        }
                        //#! Single menu item
                        else {
                            $activeClass = ( Str::containsAll( url()->current(), [ $entry[ 'url' ] ] ) ? 'active' : '' );
                            echo '<li class="nav-item ' . esc_attr( $activeClass ) . '">';
                            echo '<a class="nav-link" id="menu-item-' . esc_attr( $id ) . '" title="' . esc_attr( $entry[ 'title' ] ) . '" href="' . esc_attr( $entry[ 'url' ] ) . '">' . $entry[ 'text' ] . '</a>';
                            echo '</li>';
                        }
                    }
                    ?>
                </ul>
            </div>
        </nav>
        <?php
    }
}
