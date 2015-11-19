<?php

/**
 *
 * PHP version 5
 *
 * Created: 11/19/15, 11:53 AM
 *
 * LICENSE: This is PRIVATE source code developed for clients.
 * It is in no way transferable and copy rights belong to Jeff Behnke @ ValidWebs.com
 *
 * @author         Jeff Behnke <code@validwebs.com>
 * @copyright  (c) 2015 ValidWebs.com
 *
 * dashboard
 * functions.php
 */

/**
 * Create an array of the hosts from all of the VVV host files
 *
 * @author         Jeff Behnke <code@validwebs.com>
 * @copyright  (c) 2014 ValidWebs.com
 *
 * Created:    5/23/14, 12:57 PM
 *
 * @param $path
 *
 * @return array
 */
function get_hosts( $path ) {

	$array = array();
	$debug = array();
	$hosts = array();
	$wp    = array();
	$depth = 2;
	$site  = new RecursiveDirectoryIterator( $path, RecursiveDirectoryIterator::SKIP_DOTS );
	$files = new RecursiveIteratorIterator( $site );
	if ( ! is_object( $files ) ) {
		return null;
	}
	$files->setMaxDepth( $depth );

	// Loop through the file list and find what we want
	foreach ( $files as $name => $object ) {

		if ( strstr( $name, 'vvv-hosts' ) && ! is_dir( 'vvv-hosts' ) ) {

			$lines = file( $name );
			$name  = str_replace( array( '../../', '/vvv-hosts' ), array(), $name );

			// read through the lines in our host files
			foreach ( $lines as $num => $line ) {

				// skip comment lines
				if ( ! strstr( $line, '#' ) && 'vvv.dev' != trim( $line ) ) {
					if ( 'vvv-hosts' == $name ) {
						switch ( trim( $line ) ) {
							case 'local.wordpress.dev' :
								$hosts['wordpress-default'] = array( 'host' => trim( $line ) );
								break;
							case 'local.wordpress-trunk.dev' :
								$hosts['wordpress-trunk'] = array( 'host' => trim( $line ) );
								break;
							case 'src.wordpress-develop.dev' :
								$hosts['wordpress-develop/src'] = array( 'host' => trim( $line ) );
								break;
							case 'build.wordpress-develop.dev' :
								$hosts['wordpress-develop/build'] = array( 'host' => trim( $line ) );
								break;
						}
					}
					if ( 'vvv-hosts' != $name ) {
						$hosts[ $name ] = array( 'host' => trim( $line ) );
					}
				}
			}
		}

		if ( strstr( $name, 'wp-config.php' ) ) {

			$config_lines = file( $name );
			$name         = str_replace( array( '../../', '/wp-config.php', '/htdocs' ), array(), $name );

			// read through the lines in our host files
			foreach ( $config_lines as $num => $line ) {

				// skip comment lines
				if ( strstr( $line, "define('WP_DEBUG', true);" )
				     || strstr( $line, 'define("WP_DEBUG", true);' )
				     || strstr( $line, 'define( "WP_DEBUG", true );' )
				     || strstr( $line, "define( 'WP_DEBUG', true );" )
				) {
					$debug[ $name ] = array(
						'path'  => $name,
						'debug' => 'true',
					);
				}
			}

			$wp[ $name ] = 'true';
		}
	}

	foreach ( $hosts as $key => $val ) {

		if ( array_key_exists( $key, $debug ) ) {
			if ( array_key_exists( $key, $wp ) ) {
				$array[ $key ] = $val + array( 'debug' => 'true', 'is_wp' => 'true' );
			} else {
				$array[ $key ] = $val + array( 'debug' => 'true', 'is_wp' => 'false' );
			}
		} else {
			if ( array_key_exists( $key, $wp ) ) {
				$array[ $key ] = $val + array( 'debug' => 'false', 'is_wp' => 'true' );
			} else {
				$array[ $key ] = $val + array( 'debug' => 'false', 'is_wp' => 'false' );
			}
		}
	}

	$array['site_count'] = count( $hosts );

	return $array;
}

/**
 * Formats csv data strings into bootstrap tables
 *
 * @author         Jeff Behnke <code@validwebs.com>
 * @copyright  (c) 2009-15 ValidWebs.com
 *
 * Created:    11/19/15, 12:42 PM
 *
 * @param $data
 *
 * @return string
 */
function format_table( $data ) {
	$table_data = array();

	$table = '<table class="table table-responsive table-striped table-bordered table-hover">';
	$data  = explode( "\n", $data );

	foreach ( $data as $key => $line ) {

		if ( '0' == $key ) {
			$row = explode( ',', $line );

			$table_data[] .= '<thead><tr>';
			$table_data[] .= '</th><th>' . implode( '<th>', $row ) . '</th>';
			$table_data[] .= '</tr></thead>';
		} else {
			$row = explode( ',', $line );
			if ( isset( $row[0] ) && ! empty( $row[0] ) ) {
				$table_data[] .= '<tr>';
				$table_data[] .= '</td><td>' . implode( '<td>', $row ) . '</td>';
				$table_data[] .= '</tr>';
			}
		}
	} // end foreach

	$table .= implode( '', $table_data );
	$table .= '</table>';


	return $table;
}
// End functions.php