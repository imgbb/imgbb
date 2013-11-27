<?php
/**
 * Created by IntelliJ IDEA.
 * User: Admin
 * Date: 10/28/13
 * Time: 12:37 PM
 * To change this template use File | Settings | File Templates.
 *
 * currently UNUSED, NEEDS MORE BRAINSTORMS
 */

$item1 = 'front';

$fURL = array(

	'#/front/(\d+)-#' => array ( 'app' 	=> 'main',
								 'module' => 'front',
								 'area' => 'categories',
								 'action' => '$1'
	)
);