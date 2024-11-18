<?php
/**
 * Graphs
 *
 * @package     RSM
 * @subpackage  Admin/Dashboard
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * RSM_Graph Class
 *
 * @since 1.0
 */
class RSM_Graph {
    /**
     * Data to graph
     *
     * @var array
     * @since 1.0
     */
    private $data;

    /**
     * Unique ID for the graph
     *
     * @var string
     * @since 1.0
     */
    private $id = '';

    /**
     * Graph options
     *
     * @var array
     * @since 1.0
     */
    private $options = [];

    /**
     * Get things started
     *
     * @since 1.0
     */
    public function __construct( $_data ) {
        $this->data = $_data;

        // Generate unique ID
        $this->id   = md5( rand() );

        // Setup default options;
        $this->options = [
            'range'         => 'this_month',
            'y_label'       => null,
            'x_label'       => null,
            'min_tick_size' => '[1, "day"]',
            'time_format'   => '%d/%b',
            'line_colors'   => [ '#334a71', '#009dc6', '#8b9dc3', '#AA4643' ]
        ];
    }

    /**
     * Set an option
     *
     * @param $key The option key to set
     * @param $value The value to assign to the key
     * @since 1.0
     */
    public function set( $key, $value ) {
        $this->options[ $key ] = $value;
    }

    /**
     * Get an option
     *
     * @param $key The option key to get
     * @since 1.0
     */
    public function get( $key ) {
        return isset( $this->options[ $key ] ) ? $this->options[ $key ] : false;
    }

    /**
     * Get graph data
     *
     * @since 1.0
     */
    public function get_data() {
        return $this->data;
    }

    /**
     * Build the graph and return it as a string
     *
     * @var array
     * @since 1.0
     * @return string
     */
    public function build_graph() {
        $yaxis_count = 1;

        ob_start();
        ?>
		<script type="text/javascript">
			jQuery( document ).ready( function($) {
				$.plot(
					$("#rsm-graph-<?php echo $this->id; ?>"),
					[
						<?php foreach( $this->get_data() as $label => $data ) : ?>
							{
								label: "<?php echo esc_attr( $label ); ?>",
								data: [<?php foreach( $data as $point ) {
								    echo '[' . implode( ',', $point ) . '],';
								} ?>],
								points: {
									show: true,
									<?php if( isset( $this->options[ 'line_colors' ][ $yaxis_count - 1 ] ) ) : ?>
									fillColor: "<?php echo $this->options[ 'line_colors' ][ $yaxis_count - 1 ]; ?>"
									<?php endif; ?>
								},
								<?php if( isset( $this->options[ 'line_colors' ][ $yaxis_count - 1 ] ) ) : ?>
								color: "<?php echo $this->options[ 'line_colors' ][ $yaxis_count - 1 ]; ?>",
								<?php endif; ?>
								lines: {
									show: true,
                                    fill: true,
                                    lineWidth:3
								}
							},
						<?php $yaxis_count++; endforeach; ?>

					],
					{
						// Options
						axisLabels: {
								show: true
						},
						grid: {
								show: true,
								borderColor: "#888",
								borderWidth: 1,
								hoverable: true
						},
						xaxis: {
								mode: "time",
								//twelveHourClock: true,
								minTickSize: <?php echo $this->options['min_tick_size']; ?>,
								timeformat: "<?php echo $this->options['time_format']; ?>",
								<?php if( !empty( $this->options['x_label'] ) ) : ?>
								axisLabel: "<?php echo $this->options['x_label']; ?>",
								<?php endif; ?>
								axisLabelPadding: 5
						},
						yaxis: {
								min: 0,
								position: "left",
								<?php if( !empty( $this->options['y_label'] ) ) : ?>
								axisLabel: "<?php echo $this->options['y_label']; ?>",
								<?php endif; ?>
			          axisLabelPadding: 5
						}
					}
				);

				function rsm_flot_tooltip(x, y, contents, z) {
						$('<div id="rsm-flot-tooltip">' + contents + '</div>').css( {
							top: y - 30,
							left: x + 20,
							'border-color': z,
						}).appendTo("body").fadeIn(200);
				}

				var previousPoint = null;
				$("#rsm-graph-<?php echo $this->id; ?>").bind("plothover", function (event, pos, item) {
			        if (item) {
			            if ((previousPoint != item.dataIndex) || (previousLabel != item.series.label)) {
			                previousPoint = item.dataIndex;
			                previousLabel = item.series.label;

			                $("#rsm-flot-tooltip").remove();

			                //var x = convertToDate(item.datapoint[0]),
			                x = item.series.label;
			                y = item.datapoint[1];
			                z = item.series.color;

			                rsm_flot_tooltip(item.pageX, item.pageY,
			                    "<strong>" + item.series.label + "</strong> = " + y,
			                    z);
			            }
			        } else {
			            $("#rsm-flot-tooltip").remove();
			            previousPoint = null;
			        }
				});

			});

		</script>
		<div id="rsm-graph-<?php echo $this->id; ?>" class="rsm-graph" style="height: 300px;"></div>
<?php
        return ob_get_clean();
    }

    /**
     * Output the final graph
     *
     * @since 1.0
     */
    public function display() {
        echo $this->build_graph();
    }
}
