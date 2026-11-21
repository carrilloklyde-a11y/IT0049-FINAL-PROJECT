<?php
use CodeIgniter\Router\RouteCollection;
/** @var RouteCollection $routes */
$routes->setAutoRoute(false);
$routes->get('login','Auth::login');
$routes->post('login','Auth::attempt');
$routes->get('health','Home::health');
$routes->group('', ['filter'=>'auth'], static function ($routes) {
    $routes->get('/','Home::index');
    $routes->get('about','Home::about');
    $routes->post('logout','Auth::logout');
    $routes->get('products','Products::index');
    $routes->get('products/new','Products::form');
    $routes->post('products','Products::save');
    $routes->get('products/(:num)/edit','Products::form/$1');
    $routes->post('products/(:num)','Products::save/$1');
    $routes->post('products/(:num)/archive','Products::archive/$1');
    $routes->post('products/(:num)/restore','Products::restore/$1');
    $routes->get('customers','Customers::index');
    $routes->get('customers/new','Customers::form');
    $routes->post('customers','Customers::save');
    $routes->get('customers/(:num)/edit','Customers::form/$1');
    $routes->post('customers/(:num)','Customers::save/$1');
    $routes->post('customers/(:num)/archive','Customers::archive/$1');
    $routes->post('customers/(:num)/restore','Customers::restore/$1');
    $routes->get('staff','Staff::index');
    $routes->get('staff/new','Staff::form');
    $routes->post('staff','Staff::save');
    $routes->get('staff/(:num)/edit','Staff::form/$1');
    $routes->post('staff/(:num)','Staff::save/$1');
    $routes->post('staff/(:num)/archive','Staff::archive/$1');
    $routes->post('staff/(:num)/restore','Staff::restore/$1');
    $routes->get('pos','Sales::form');
    $routes->post('sales','Sales::record');
    $routes->get('sales','Sales::index');
    $routes->get('sales/(:num)','Sales::receipt/$1');
    $routes->get('media/([a-f0-9]{32}\.webp)','Media::show/$1');
});
