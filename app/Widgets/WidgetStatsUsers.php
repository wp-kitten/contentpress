<?php

namespace App\Widgets;

use App\Helpers\ScriptsManager;
use App\Helpers\StatsHelper;
use App\Helpers\Util;
use App\Models\User;

/**
 * Class WidgetStatsUsers
 * @package App\Widgets
 */
class WidgetStatsUsers extends AbstractWidgetBase
{
    public function __construct( $id = '', $options = [] )
    {
        parent::__construct( $id, $options );

        if ( empty( $id ) || !cp_current_user_can( 'manage_options' ) ) {
            return;
        }

        $info = StatsHelper::getInstance()->getUsersInfo();
        if ( isset( $info[ CURRENT_YEAR ] ) && !empty( $info[ CURRENT_YEAR ] ) ) {

            $this->setData( $info );

            ScriptsManager::enqueueFooterScript( 'Chart.min.js', asset( 'vendor/admin-template/plugins/chart.js' ) );
            add_action( 'valpress/admin/footer', [ $this, '__printInlineScripts' ] );
        }
    }

    public function render()
    {
        if ( !cp_current_user_can( 'manage_options' ) ) {
            return;
        }
        $stats = $this->getData();
        $stats = $stats[ CURRENT_YEAR ];
        $helper = StatsHelper::getInstance();

        $usersCount = User::count();
        $lastMonthInfo = $helper->getLastMonthInfo( $stats );
        $currentMonthData = ( isset( $stats[ CURRENT_MONTH_NUM ] ) ? intval( $stats[ CURRENT_MONTH_NUM ] ) : 0 );

        $diffValue = Util::getPercentageChange( $lastMonthInfo[ 'data' ], $currentMonthData );
        $sign = $icon = $cssClass = '';

        if ( $diffValue < 0 ) {
            $icon = '<i class="mdi mdi-arrow-down mr-1 text-danger"></i>';
            $cssClass = 'text-danger';
        }
        elseif ( $diffValue > 0 ) {
            $icon = '<i class="mdi mdi-arrow-up mr-1 text-success"></i>';
            $sign = '+';
            $cssClass = 'text-success';
        }
        ?>
        <div class="card mb-2 widget"
             data-id="<?php esc_attr_e( $this->getId() ); ?>"
             data-class="<?php esc_attr_e( __CLASS__ ); ?>">
            <div class="card-body">
                <h4 class="card-title">
                    <?php echo \apply_filters( 'valpress/widget/title', esc_html( __( 'a.Users' ) ), __CLASS__ ); ?>
                </h4>
                <div class="d-flex flex-wrap align-items-baseline">
                    <h2 class="mr-3"><?php esc_html_e( $usersCount ); ?></h2>
                    <?php wp_kses_e( $icon, [ 'i' => [ 'class' => [] ] ] ); ?>
                    <span>
                        <span class="mb-0 <?php esc_attr_e( $cssClass ); ?> font-weight-medium"><?php esc_html_e( $sign . $diffValue ); ?>%</span>
                    </span>
                </div>
                <p class="mb-0 text-muted"><?php esc_html_e( __( 'a.Total users' ) ); ?></p>
            </div>
            <div class="card-body">
                <canvas id="chartjs-lineChart-<?php esc_attr_e( $this->getId() ); ?>" class="embed-responsive-item"></canvas>
            </div>
        </div>
        <?php
    }

    public function __printInlineScripts()
    {
        $stats = $this->getData();
        if ( empty( $stats ) || !isset( $stats[ CURRENT_YEAR ] ) || empty( $stats[ CURRENT_YEAR ] ) ) {
            return;
        }
        $stats = $stats[ CURRENT_YEAR ];

        //#! Build the list of months
        $months = array_map( function ( $m ) {
            return Util::getMonthName( $m, true );
        }, array_keys( $stats ) );
        ?>
        <script id="widget-stats-users-js-<?php esc_attr_e( $this->getId() ); ?>">
            jQuery( function ($) {
                "use strict";
                var chartElement = $( "#chartjs-lineChart-<?php esc_attr_e( $this->getId() ); ?>" );
                if ( chartElement.length ) {
                    var data = {
                        labels: [<?php echo StatsHelper::getInstance()->buildStringsArrayForJS( $months );?>],
                        datasets: [{
                            label: '# of Users',
                            data: [<?php echo implode( ',', array_values( $stats ) );?>],
                            fillColor: "rgba(151,187,205,0.2)",
                            strokeColor: "rgba(151,187,205,1)",
                            pointColor: "rgba(151,187,205,1)",
                            pointStrokeColor: "#fff",
                            pointHighlightFill: "#fff",
                            pointHighlightStroke: "rgba(151,187,205,1)",
                        }]
                    };

                    var lineChartCanvas = chartElement.get( 0 ).getContext( "2d" );
                    new Chart( lineChartCanvas ).Line( data );
                }
            } );
        </script>
        <?php
    }
}
