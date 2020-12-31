<?php

namespace App\Widgets;

use App\Helpers\ScriptsManager;
use App\Helpers\StatsHelper;
use App\Helpers\Util;

class WidgetStatsPosts extends AbstractWidgetBase
{
    public function __construct( $id = '', $options = [] )
    {
        parent::__construct( $id, $options );

        if ( empty( $id ) ) {
            return;
        }

        $info = StatsHelper::getInstance()->getPostsInfo();

        if ( isset( $info[ CURRENT_YEAR ] ) && !empty( $info[ CURRENT_YEAR ] ) ) {

            $this->setData( $info );
            $this->setOptions( $options );

            ScriptsManager::enqueueFooterScript( 'Chart.min.js', asset( 'vendor/admin-template/plugins/chart.js' ) );
            add_action( 'valpress/admin/footer', [ $this, '__printInlineScripts' ] );
        }
    }

    public function render()
    {
        $options = $this->getOptions();
        ?>
        <div class="card mb-2 widget"
             data-id="<?php esc_attr_e( $this->getId() ); ?>"
             data-class="<?php esc_attr_e( __CLASS__ ); ?>">

            <div class="card-body">
                <h4 class="card-title">
                    <?php echo \apply_filters( 'valpress/widget/title', esc_html( __( 'a.Posts' ) ), __CLASS__ ); ?>
                </h4>
                <canvas id="chartjs-lineChart-<?php esc_attr_e( $this->getId() ); ?>" class="embed-responsive-item"></canvas>
            </div>
        </div>
        <?php
    }

    public function __printInlineScripts()
    {
        $stats = $this->getData();

        if ( !isset( $stats[ CURRENT_YEAR ] ) || empty( $stats[ CURRENT_YEAR ] ) ) {
            return;
        }

        //#! Build the list of months
        $months = array_map( function ( $m ) {
            return Util::getMonthName( $m, true );
        }, array_keys( $stats[ CURRENT_YEAR ] ) );
        ?>
        <script id="widget-stats-posts-chart-js-<?php esc_attr_e( $this->getId() ); ?>">
            jQuery( function ($) {
                "use strict";
                var chartElement = $( "#chartjs-lineChart-<?php esc_attr_e( $this->getId() ); ?>" );
                if ( chartElement.length ) {
                    var data = {
                        labels: [<?php echo StatsHelper::getInstance()->buildStringsArrayForJS( $months );?>],
                        datasets: [{
                            label: "<?php esc_html_e( __( 'a.# of Posts' ) );?>",
                            data: [<?php echo implode( ',', array_values( $stats[ CURRENT_YEAR ] ) );?>],
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
