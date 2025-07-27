<?php 

/**
 * Used to define the routes in the system.
 * 
 * A route should be defined with a key matching the URL and an
 * controller#action-to-call method. E.g.:
 * 
 * '/' => 'index#index',
 * '/calendar' => 'calendar#index'
 */
$routes = array(
	'/test' => 'test#index',

	//Rutas para gestion de tareas (TaskController)
	'/' => 'task#index',
	'/tasks' => 'task#index',
	'/tasks/create' => 'task#create',
	'/tasks/show' => 'task#show',
	'/tasks/edit' => 'task#edit',
	'/tasks/delete' => 'task#delete',
);
