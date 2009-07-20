<?php
/**
 * WoopraChart Class for Woopra
 *
 * This class contains all functions and actions required
 * for Woopra to work on the backend of WordPress.
 *
 * @since 1.4.1
 * @package woopra
 * @subpackage chart
 */
class WoopraChart {
		
	/**
	 * Type of chart we are going to draw.
	 * @since 1.4.1
	 * @var string
	 */
	var $type = null;
	
	/**
	 * Hold the data needed.
	 * @since 1.4.1
	 * @var array
	 */
	var $data = null;
	
	/**
	 * PHP 4 Style constructor which calls the below PHP5 Style Constructor
	 * @since 1.4.1
	 * @return none
	 */
	function WoopraChart() {
		$this->__construct();
	}
	
	/**
	 * Chart Contructor Class
	 * @since 1.4.1
	 * @return none
	 * @constructor
	 */
	function __construct() {
		// nothing to do here....
	}
	
	function render() {
		
		// Store Data
		if (is_array($this->data['hours'])) {
			$data = $this->data['hours'];
			$this->chart = 'bar';
		}
		
		if (is_array($this->data['days'])) {
			$data = $this->data['days'];
			$this->chart = 'line';
		}
		
		// Gather Data
		foreach ($data as $key => $value) {
			if ($this->chart == "bar") {
				$b['pvs'][] = ((int) $value['pvs'] == 0 ? null : (int) $value['pvs']);
				$b['vts'][] = ((int) $value['vts'] == 0 ? null : (int) $value['vts']);
				$b['info']['x_labels'][] = sprintf(__("Hour %d", 'woopra'), $value['h']+1);
			}
			if ($this->chart == "line") {
				if ( ((int) $value['pvs'] != 0) && ((int) $value['vts'] != 0) ) {
					$b['pvs'][] = (int) $value['pvs'];
					$b['vts'][] = (int) $value['vts'];
					$b['vtrs'][] = (int) $value['vtrs'];
					$b['info']['x_labels'][] = $value['date'];
				}
			}			
		}
		
		// Find Max & Min
		if ($this->chart == "bar") {
			$b['info']['max'] = $this->rounded_max(max( array_merge($b['pvs'], $b['vts']) ));
			$b['info']['min'] = $this->rounded_min(min( array_merge($b['pvs'], $b['vts']) ));
		} else {
			$b['info']['max'] = $this->rounded_max(max( array_merge($b['pvs'], $b['vts'], $b['vtrs']) ));
			$b['info']['min'] = $this->rounded_min(min( array_merge($b['pvs'], $b['vts'], $b['vtrs']) ));
		}

		include_once('php-ofc-library/open-flash-chart.php');
		
		switch ($this->chart) {
			case 'bar': {
			
				/** ONLY USED FOR "GLOBALS" **/
				
				$bar = new bar_glass();
				$bar->colour( '#3B5998' );
				$bar->key(__("Page Views", 'woopra'), 12);
				$bar->set_values( $b['pvs'] );
				$bar->set_tooltip( "#val# #key#" );
				
				$bar_2 = new bar_glass();
				$bar_2->colour( '#6D84B4' );
				$bar_2->key(__("Visits", 'woopra'), 12);
				$bar_2->set_values( $b['vts'] );
				$bar_2->set_tooltip( "#val# #key#" );
				
				$y = new y_axis();
				$y->set_range( $b['info']['min'], $b['info']['max'], (($b['info']['max']/($b['info']['min']+1))/2) );
				$y->set_colours( '#000000', '#DDDDDD' );
				
				$x_labels = new x_axis_labels();
				$x_labels->set_size( 8 );
				$x_labels->set_labels( $b['info']['x_labels'] );
				
				$x = new x_axis();
				$x->set_labels( $x_labels );
				$x->set_colours( '#000000', '#DDDDDD' );
							
				$chart = new open_flash_chart();
				$chart->add_y_axis( $y );
				$chart->set_x_axis( $x );
				$chart->add_element( $bar	);
				$chart->add_element( $bar_2	);
				$chart->set_bg_colour( '#FFFFFF' );
				break;

			}
			
			case 'line': {
				
				$default_dot = new dot();
				$default_dot->size(5)->colour('#3CB7FF')->tooltip ( '#x_label#: #val# #key#' );
				
				$line_dot = new line();
				$line_dot->set_default_dot_style($default_dot);
				$line_dot->set_width( 4 );
				$line_dot->set_colour( '#3B5998' );
				$line_dot->set_values( $b['pvs'] );
				$line_dot->set_key( __("Page Views", 'woopra'), 10 );

				$line_dot_2 = new line();
				$line_dot_2->set_default_dot_style($default_dot);
				$line_dot_2->set_width( 3 );
				$line_dot_2->set_colour( '#6D84B4' );
				$line_dot_2->set_values( $b['vts'] );
				$line_dot_2->set_key( __("Visits", 'woopra'), 10 );
				
				$line_dot_3 = new line();
				$line_dot_3->set_default_dot_style($default_dot);
				$line_dot_3->set_width( 2 );
				$line_dot_3->set_colour( '#4169E1' );
				$line_dot_3->set_values( $b['vtrs'] );
				$line_dot_3->set_key( __("Visitors", 'woopra'), 10 );
				
				$y = new y_axis();
				$y->set_tick_length(3);
				$y->set_colours( '#000000', '#DDDDDD' );
				$y->set_range( $b['info']['min'], $b['info']['max'], $this->rounded_max(($b['info']['max']/($b['info']['min']+2))) );
				
				$y_labels = new y_axis_labels();
				$y_labels->set_labels();
				$y_labels->set_steps(6);
				$y->set_labels($y_labels);
				
				$x_labels = new x_axis_labels();
				$x_labels->set_size( 8 );
				$x_labels->set_labels( $b['info']['x_labels'] );
				$x_labels->visible_steps(5);
				
				$x = new x_axis();
				$x->set_labels( $x_labels );
				$x->set_colours( '#000000', '#DDDDDD' );
				
				$chart = new open_flash_chart();
				$chart->set_y_axis( $y );
				$chart->set_x_axis( $x );
				$chart->add_element( $line_dot_3 );
				$chart->add_element( $line_dot_2 );
				$chart->add_element( $line_dot );
				$chart->set_bg_colour( '#FFFFFF' );
				break;
			}
			
		}
		
		return $chart->toString();

	}
	
	/**
	 * Get the MAX number in the array  for "y axis"
	 * @since 1.4.1
	 * @param object $max
	 * @return int
	 */
	private function rounded_max($max) {
		$values = array(5,10,15,20,30,40,50,60,70,80,90,100,120,150,200,250,300,400,500,600,700,800,900,1000,1200,1500,2000,2500,3000,3500,4000,4500,5000,10000,20000,50000,100000,200000,500000,1000000,2000000,5000000,10000000,50000000);
		foreach ($values as $value) {
			if ($value > $max) {
				return $value;
			}
		}
		return $max;
	}
	
	/**
	 * Get the MIX number in the array for "y axis"
	 * @since 1.4.1
	 * @param object $min
	 * @return int
	 */
	private function rounded_min($min) {
		$values = array(5,10,15,20,30,40,50,60,70,80,90,100,120,150,200,250,300,400,500,600,700,800,900,1000,1200,1500,2000,2500,3000,3500,4000,4500,5000,10000,20000,50000,100000,200000,500000,1000000,2000000,5000000,10000000,50000000);
		$rounded_min = 0;
		foreach ($values as $value) {
			if ($value < $min) {
				$rounded_min = $value;
			} else {
				return $rounded_min;
			}
		}
	}
	
	/**
	 * Create the array for rounded_max and rounded_min
	 * 
	 * Note: Not currently in use in Version 1.4.1
	 * 
	 * @since 1.4.2
	 * @return array
	 */
	private function max_array() {
		for ($i = 1; $i++; $i <= 500000)
			$step[] = $i*10;
			
		return (array) $step;
	}
	
}