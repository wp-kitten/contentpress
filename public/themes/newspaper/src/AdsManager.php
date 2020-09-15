<?php

namespace App\Newspaper;

class AdsManager
{
    public static function get()
    {
        $ads = self::getAds();
        $ix = array_rand( $ads );
        return $ads[ $ix ];
    }

    public static function getAds()
    {
        $theme = cp_get_current_theme();
        return [
            'fb' => [
                'image_url' => $theme->url( 'assets/img/fb-ad-placeholder.png' ),
                'title' => __( 'np::m.Advertise yourself on :provider today!', [ 'provider' => 'Facebook'] ),
                'url' => 'https://ads.facebook.com',
            ],
            'ga' => [
                'image_url' => $theme->url( 'assets/img/g-ad-placeholder.png' ),
                'title' => __( 'np::m.Advertise yourself on :provider today!', [ 'provider' => 'Google'] ),
                'url' => 'https://ads.google.com/',
            ],
            'amz' => [
                'image_url' => $theme->url( 'assets/img/amz-ad-placeholder.jpg' ),
                'title' => __( 'np::m.Advertise yourself on :provider today!', [ 'provider' => 'Amazon'] ),
                'url' => 'https://advertising.amazon.com/',
            ],
        ];
    }
}
