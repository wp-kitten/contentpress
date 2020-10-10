<?php

namespace App\Widgets;

use App\Helpers\StatsHelper;

class WidgetStatsPendingComments extends AbstractWidgetBase
{
    public function __construct( $id = '', $options = [] )
    {
        parent::__construct( $id, $options );

        $info = StatsHelper::getInstance()->getPendingComments();
        $this->setData( $info );
    }

    public function render()
    {
        $data = $this->getData();

        $pending = ( isset( $data[ CURRENT_YEAR ] ) ? array_sum( array_values( $data[ CURRENT_YEAR ] ) ) : 0 );

        $barClass = 'bg-info';
        if ( $data >= 100 ) {
            $barClass = 'bg-warning';
        }
        elseif ( $data >= 500 ) {
            $barClass = 'bg-danger';
        }
        ?>
        <div class="card mb-2 widget"
             data-id="<?php esc_attr_e( $this->getId() ); ?>"
             data-class="<?php esc_attr_e( __CLASS__ ); ?>">

            <div class="card-body">
                <h4 class="card-title">
                    <?php echo apply_filters( 'contentpress/widget/title', esc_html( __( 'cpdw::m.Pending comments' ) ), __CLASS__ ); ?>
                </h4>
                <div class="d-flex justify-content-between">
                    <p class="text-muted">
                        <?php esc_html_e( $pending ); ?>
                    </p>
                </div>
                <div class="progress progress-md">
                    <div class="progress-bar <?php esc_attr_e( $barClass ); ?>"
                         role="progressbar"
                         aria-valuenow="<?php esc_attr_e( $pending ); ?>"
                         style="width: <?php esc_attr_e( $pending ); ?>%"
                         aria-valuemin="0"
                         aria-valuemax="100"></div>
                </div>
            </div>
        </div>
        <?php
    }
}
