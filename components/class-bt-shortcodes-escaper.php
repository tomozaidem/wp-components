<?php
/**
 * Class allows escape unexpected <br /> & <p> tags between nested theme shortcodes.
 * For example we have following structute of the shortcodes:
 * <pre>
 * [table]
 * 	[tr]
 * 		[td]cell 1[td]
 * 		[td]cell 2[/td]
 * 	[/tr]
 * [/table]
 * </pre>
 *
 * So to prevent tags P or BR between table, tr, td tags we should register this structure via following call:
 * <pre>
 * $escaper = new BT_Shortcodes_Escaper();
 * $escaper->register_nested_shortcodes('table','tr','td');
 *
 * //or alternative way:
 * $escaper->add_relation('table','tr');
 * $escaper->add_relation('tr','td');
 * </pre>
 *
 * @author    Tomo Zaidem
 * @package   BrewedTech/WP_Components
 * @version   1.0.0
 */

/**
 * No direct access to this file.
 *
 * @since 1.0.0
 */
defined( 'ABSPATH' ) || die();

/**
 * Class BT_Shortcodes_Escaper
 */
class BT_Shortcodes_Escaper extends BT_Component {

	/**
	 * Stores list of possible parent & child tags combinations.
	 *
	 * @see is_nested
	 * @var array
	 */
	protected $relation_variations = array();

	protected $delimiter = '|';

	/**
	 * Parts of the regexp.
	 *
	 * @var array
	 */
	protected $reg_parts_opens = array();

	/**
	 * Parts of the regexp.
	 *
	 * @var array
	 */
	protected $reg_parts_closes = array();

	/**
	 * Component init method.
	 *
	 * @return bool
	 */
	public function init() {
		if ( parent::init() ) {
			add_filter( 'the_content', array( $this, 'remove_whitespaces' ), 2 );
			return true;
		}
		return false;
	}

	/**
	 * Registers relations between all arguments passed to the function.
	 *
	 * @example
	 * <pre>
	 * $escaper->register_nested_shortcodes('table','tr','td');
	 * </pre>
	 * @return void
	 */
	public function register_nested_shortcodes() {
		$items = func_get_args();

		if ( count( $items ) < 2 ) {
			return;
		}
		$parent = array_shift( $items );
		foreach ( $items as $child ) {
			$this->push_relation( $parent, $child );
			$parent = $child;
		}
	}

	/**
	 * Registers relation between parent and child shortcodes.
	 *
	 * @example
	 * <pre>
	 * $escaper->add_relation('table','tr');
	 * $escaper->add_relation('tr','td');
	 * </pre>
	 *
	 * @param string $parent name of the parent shortcode
	 * @param string $child  name of the child shortcode
	 * @return void
	 */
	public function add_relation( $parent, $child ) {
		$this->push_relation( $parent, $child );
	}

	public function remove_whitespaces( $content ) {
		return preg_replace_callback( $this->get_regexp(),array( $this, '_parse_callback' ), $content );
	}

	public function push_relation( $parent, $child ) {
		$this->relation_variations[] = $parent . $this->delimiter . $child;
		$this->relation_variations[] = '/' . $child . $this->delimiter . '/' . $parent;
		$this->relation_variations[] = '/' . $child . $this->delimiter . $child;

		$this->push_to_regexp_parts( 'open', $parent );
		$this->push_to_regexp_parts( 'open', '\/' . $child );

		$this->push_to_regexp_parts( 'close', '\/' . $parent );
		$this->push_to_regexp_parts( 'close', '\/?' . $child );
	}

	protected function get_regexp() {
		// $result = '`\[\/?(\w+)[^\]]*\](\s)+\[\/?(\w+)[^\]]*\]`';
		$attributes_pattern = '[^\]]*';
		return '`\[(' . join( '|', $this->reg_parts_opens ) . ')' . $attributes_pattern . '\](\s)+\[(' . join( '|', $this->reg_parts_closes ) . ')' . $attributes_pattern . '\]`';
	}

	public function _parse_callback( $res ) {
		$full_text = $res[0];
		if ( $this->is_nested( $res[1], $res[3] ) ) {
			return preg_replace( '`(\])\s+(\[)`', '$1$2', $full_text );
		}
		return $full_text;
	}

	protected function push_to_regexp_parts( $type, $regexp ) {
		if ( 'open' == $type ) {
			$target_list = &$this->reg_parts_opens;
		} else {
			$target_list = &$this->reg_parts_closes;
		}
		if ( ! isset( $target_list[ $regexp ] ) ) {
			$target_list[ $regexp ] = $regexp;
		}
	}

	protected function is_nested( $tag1, $tag2 ) {
		return in_array( $tag1 . $this->delimiter . $tag2, $this->relation_variations, true );
	}

}
