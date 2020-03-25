<?php
/**
 * Storage interface.
 * TODO: Update desc
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

interface BT_Storage
{
	public function get_data( $dataId );

	public function set_data( $dataId, $dataValue );

	public function delete_data( $dataId );
}