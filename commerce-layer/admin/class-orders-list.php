<?php
/**
 * Orders List Table Class
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class CL_Orders_List extends WP_List_Table {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct( array(
            'singular' => __( 'הזמנה', 'commerce-layer' ),
            'plural'   => __( 'הזמנות', 'commerce-layer' ),
            'ajax'     => false,
        ) );
    }

    /**
     * Get columns
     */
    public function get_columns() {
        return array(
            'cb'             => '<input type="checkbox" />',
            'order_number'   => __( 'מספר הזמנה', 'commerce-layer' ),
            'customer'       => __( 'לקוח', 'commerce-layer' ),
            'status'         => __( 'סטטוס', 'commerce-layer' ),
            'total'          => __( 'סכום', 'commerce-layer' ),
            'items'          => __( 'פריטים', 'commerce-layer' ),
            'date'           => __( 'תאריך', 'commerce-layer' ),
        );
    }

    /**
     * Get sortable columns
     */
    public function get_sortable_columns() {
        return array(
            'order_number' => array( 'order_number', false ),
            'total'        => array( 'total', false ),
            'date'         => array( 'created_at', true ),
        );
    }

    /**
     * Prepare items
     */
    public function prepare_items() {
        $per_page = 20;
        $current_page = $this->get_pagenum();

        $args = array(
            'per_page' => $per_page,
            'page'     => $current_page,
            'orderby'  => isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'created_at',
            'order'    => isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'DESC',
        );

        // Filter by status
        if ( ! empty( $_GET['status'] ) ) {
            $args['status'] = sanitize_text_field( $_GET['status'] );
        }

        // Search
        if ( ! empty( $_GET['s'] ) ) {
            $args['search'] = sanitize_text_field( $_GET['s'] );
        }

        $this->items = CL_Order::get_orders( $args );

        $total_items = CL_Order::get_count( array(
            'status' => $args['status'] ?? '',
        ) );

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page ),
        ) );

        $this->_column_headers = array(
            $this->get_columns(),
            array(),
            $this->get_sortable_columns(),
        );
    }

    /**
     * Column default
     */
    public function column_default( $item, $column_name ) {
        return '';
    }

    /**
     * Checkbox column
     */
    public function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="order_ids[]" value="%d" />', $item->get_id() );
    }

    /**
     * Order number column
     */
    public function column_order_number( $item ) {
        $view_url = add_query_arg( array(
            'page'     => 'cl-orders',
            'order_id' => $item->get_id(),
        ), admin_url( 'admin.php' ) );

        $actions = array(
            'view' => sprintf(
                '<a href="%s">%s</a>',
                esc_url( $view_url ),
                __( 'צפה', 'commerce-layer' )
            ),
        );

        return sprintf(
            '<a href="%s" class="cl-order-number"><strong>%s</strong></a>%s',
            esc_url( $view_url ),
            esc_html( $item->get_order_number() ),
            $this->row_actions( $actions )
        );
    }

    /**
     * Customer column
     */
    public function column_customer( $item ) {
        return sprintf(
            '<strong>%s</strong><br><a href="mailto:%s">%s</a>',
            esc_html( $item->get_customer_name() ),
            esc_attr( $item->get_customer_email() ),
            esc_html( $item->get_customer_email() )
        );
    }

    /**
     * Status column
     */
    public function column_status( $item ) {
        $status = $item->get_status();
        $label = $item->get_status_label();

        $status_classes = array(
            'pending'  => 'cl-status-pending',
            'paid'     => 'cl-status-paid',
            'canceled' => 'cl-status-canceled',
            'refunded' => 'cl-status-refunded',
        );

        $class = isset( $status_classes[ $status ] ) ? $status_classes[ $status ] : '';

        return sprintf(
            '<span class="cl-status %s">%s</span>',
            esc_attr( $class ),
            esc_html( $label )
        );
    }

    /**
     * Total column
     */
    public function column_total( $item ) {
        return CL_Core::format_price( $item->get_total() );
    }

    /**
     * Items column
     */
    public function column_items( $item ) {
        return $item->get_items_count();
    }

    /**
     * Date column
     */
    public function column_date( $item ) {
        $date = $item->get_created_date();
        if ( empty( $date ) ) {
            return '—';
        }

        return sprintf(
            '<span title="%s">%s</span>',
            esc_attr( $date ),
            esc_html( date_i18n( 'd/m/Y H:i', strtotime( $date ) ) )
        );
    }

    /**
     * Extra table nav
     */
    public function extra_tablenav( $which ) {
        if ( 'top' !== $which ) {
            return;
        }

        $current_status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
        ?>
        <div class="alignleft actions">
            <select name="status" id="filter-by-status">
                <option value=""><?php esc_html_e( 'כל הסטטוסים', 'commerce-layer' ); ?></option>
                <?php foreach ( CL_Order::$statuses as $status => $label ) : ?>
                    <option value="<?php echo esc_attr( $status ); ?>" <?php selected( $current_status, $status ); ?>>
                        <?php echo esc_html( $label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php submit_button( __( 'סנן', 'commerce-layer' ), '', 'filter_action', false ); ?>
        </div>
        <?php
    }

    /**
     * Get bulk actions
     */
    public function get_bulk_actions() {
        return array(
            'mark_paid'     => __( 'סמן כשולם', 'commerce-layer' ),
            'mark_canceled' => __( 'סמן כבוטל', 'commerce-layer' ),
            'export_csv'    => __( 'ייצא ל-CSV', 'commerce-layer' ),
        );
    }

    /**
     * Process bulk actions
     */
    public function process_bulk_action() {
        if ( ! isset( $_POST['order_ids'] ) || empty( $_POST['order_ids'] ) ) {
            return;
        }

        $order_ids = array_map( 'absint', $_POST['order_ids'] );
        $action = $this->current_action();

        switch ( $action ) {
            case 'mark_paid':
                foreach ( $order_ids as $order_id ) {
                    $order = new CL_Order( $order_id );
                    if ( $order->exists() ) {
                        $order->set_status( 'paid' );
                    }
                }
                break;

            case 'mark_canceled':
                foreach ( $order_ids as $order_id ) {
                    $order = new CL_Order( $order_id );
                    if ( $order->exists() ) {
                        $order->set_status( 'canceled' );
                    }
                }
                break;

            case 'export_csv':
                $this->export_csv( $order_ids );
                break;
        }
    }

    /**
     * Export orders to CSV
     */
    private function export_csv( $order_ids ) {
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=orders-' . date( 'Y-m-d' ) . '.csv' );

        $output = fopen( 'php://output', 'w' );

        // BOM for Excel Hebrew support
        fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

        // Header
        fputcsv( $output, array(
            __( 'מספר הזמנה', 'commerce-layer' ),
            __( 'תאריך', 'commerce-layer' ),
            __( 'סטטוס', 'commerce-layer' ),
            __( 'שם לקוח', 'commerce-layer' ),
            __( 'אימייל', 'commerce-layer' ),
            __( 'טלפון', 'commerce-layer' ),
            __( 'סכום', 'commerce-layer' ),
            __( 'פריטים', 'commerce-layer' ),
        ) );

        foreach ( $order_ids as $order_id ) {
            $order = new CL_Order( $order_id );
            if ( ! $order->exists() ) {
                continue;
            }

            $items_str = array();
            foreach ( $order->get_items() as $item ) {
                $items_str[] = $item['name'] . ' x' . $item['quantity'];
            }

            fputcsv( $output, array(
                $order->get_order_number(),
                $order->get_created_date(),
                $order->get_status_label(),
                $order->get_customer_name(),
                $order->get_customer_email(),
                $order->get_customer_phone(),
                $order->get_total(),
                implode( ', ', $items_str ),
            ) );
        }

        fclose( $output );
        exit;
    }

    /**
     * No items message
     */
    public function no_items() {
        esc_html_e( 'לא נמצאו הזמנות', 'commerce-layer' );
    }
}
