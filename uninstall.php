<?php
// If uninstall/delete not called from WordPress then exit
if( !defined( 'ABSPATH' ) && !defined( 'WP_UNINSTALL_PLUGIN' ) )
   exit();

delete_option('giw3csvfi_delimiter');
delete_option('giw3csvfi_nfields');
delete_option('giw3csvfi_instype');
$filename = dirname(__FILE__).'/myfile.csv';
if(file_exists($filename))
   @unlink($filename);
?>