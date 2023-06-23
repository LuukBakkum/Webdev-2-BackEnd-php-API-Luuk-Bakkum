<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

error_reporting(E_ALL);
ini_set("display_errors", 1);

require __DIR__ . '/../vendor/autoload.php';

// Create Router instance
$router = new \Bramus\Router\Router();

$router->setNamespace('Controllers');

// routes for the products endpoint
$router->get('/products', 'ProductController@getAll');
$router->get('/products/(\d+)', 'ProductController@getOne');
$router->post('/products', 'ProductController@create');
$router->put('/products/(\d+)', 'ProductController@update');
$router->delete('/products/(\d+)', 'ProductController@delete');

// routes for the categories endpoint
$router->get('/categories', 'CategoryController@getAll');
$router->get('/categories/(\d+)', 'CategoryController@getOne');
$router->post('/categories', 'CategoryController@create');
$router->put('/categories/(\d+)', 'CategoryController@update');
$router->delete('/categories/(\d+)', 'CategoryController@delete');


//VOOR DE APPLICATIE
// routes for the users endpoint
$router->post('/login', 'UserController@login');
$router->post('/register', 'UserController@register');
$router->get('/users', 'UserController@getAll');
$router->get('/users/(\d+)', 'UserController@getOne');
$router->post('/users', 'UserController@create');
$router->put('/users/(\d+)', 'UserController@update');
$router->delete('/users/(\d+)', 'UserController@delete');

// routes for the movies endpoint
$router->get('/movies', 'MovieController@getAll');
$router->get('/movies/(\d+)', 'MovieController@getOne');
$router->post('/movies', 'MovieController@create');
$router->put('/movies/(\d+)', 'MovieController@update');
$router->delete('/movies/(\d+)', 'MovieController@delete');

// routes for the series endpoint
$router->get('/series', 'SerieController@getAll');
$router->get('/series/(\d+)', 'SerieController@getOne');
$router->post('/series', 'SerieController@create');
$router->put('/series/(\d+)', 'SerieController@update');
$router->delete('/series/(\d+)', 'SerieController@delete');

// routes for the orders endpoint
$router->get('/orders', 'OrderController@getAll');
$router->get('/orders/user', 'OrderController@getAllByUserId');
$router->get('/orders/(\d+)', 'OrderController@getOne');
$router->post('/orders', 'OrderController@create');
$router->put('/orders/(\d+)', 'OrderController@update');
$router->delete('/orders/(\d+)', 'OrderController@delete');

// Run it!
$router->run();