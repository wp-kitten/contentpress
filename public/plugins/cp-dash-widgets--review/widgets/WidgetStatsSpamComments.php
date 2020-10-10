<?php

namespace App\Widgets;

use App\Helpers\StatsHelper;

class WidgetStatsSpamComments extends AbstractWidgetBase
{
    public function __construct( $id = '', $options = [] )
    {
        parent::__construct( $id, $options );

        if ( empty( $id ) ) {
            return;
        }

        $info = StatsHelper::getInstance()->getSpamCommentsInfo();
        $this->setData( $info );
    }

    public function render()
    {
        $data = $this->getData();
        if ( empty( $data ) || !isset( $data[ CURRENT_YEAR ] ) ) {
            return;
        }
        $spamComments = ( isset( $data[ CURRENT_YEAR ][ CURRENT_MONTH_NUM ] ) ? $data[ CURRENT_YEAR ][ CURRENT_MONTH_NUM ] : 0 );
        $progressClass = 'bg-info';
        if ( $spamComments >= 100 ) {
            $progressClass = 'bg-warning';
        }
        elseif ( $spamComments >= 500 ) {
            $progressClass = 'bg-danger';
        }
        ?>
        <div class="card mb-2 widget"
             data-id="<?php esc_attr_e( $this->getId() ); ?>"
             data-class="<?php esc_attr_e( __CLASS__ ); ?>">

            <div class="card-body">
                <h4 class="card-title">
                    <?php echo apply_filters( 'contentpress/widget/title', esc_html( __( 'cpdw::m.Spam comments' ) ), __CLASS__ ); ?>
                </h4>
                <div class="d-flex justify-content-between">
                    <p class="text-muted">
                        <?php esc_html_e( __( 'cpdw::m.This month:' ) . ' ' . $spamComments ); ?>
                    </p>
                </div>
                <div class="progress progress-md">
                    <div class="progress-bar <?php esc_attr_e( $progressClass ); ?>"
                         role="progressbar"
                         aria-valuenow="<?php esc_attr_e( $spamComments ); ?>"
                         style="width: <?php esc_attr_e( $spamComments ); ?>%"
                         aria-valuemin="0"
                         aria-valuemax="100"></div>
                </div>
            </div>
        </div>
        <?php
    }
}
