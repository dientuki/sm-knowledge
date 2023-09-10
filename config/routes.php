<?php

// Define app routes

use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    // Redirect to Swagger documentation
    $app->get('/', \App\Action\Home\HomeAction::class)->setName('home');

    // API
    #$app->group(
    #    '/knwl',
    #    function (RouteCollectorProxy $app) {
    #        $app->get('/customers', \App\Action\Customer\CustomerFinderAction::class);
    #        $app->post('/customers', \App\Action\Customer\CustomerCreatorAction::class);
    #        $app->get('/customers/{customer_id}', \App\Action\Customer\CustomerReaderAction::class);
    #        $app->put('/customers/{customer_id}', \App\Action\Customer\CustomerUpdaterAction::class);
    #        $app->delete('/customers/{customer_id}', \App\Action\Customer\CustomerDeleterAction::class);
    #    }
    #);
    $app->group(
        '/knwl',
        function (RouteCollectorProxy $app) {
            $app->group(
                '/brands',
                function (RouteCollectorProxy $app) {
                    $app->get('/', \App\Action\Brand\BrandFinderAction::class);
                    $app->post('/', \App\Action\Brand\BrandCreatorAction::class);
                    $app->get('/{brand_id}', \App\Action\Brand\BrandReaderAction::class);
                    $app->put('/{brand_id}', \App\Action\Brand\BrandUpdaterAction::class);
                    $app->delete('/{brand_id}', \App\Action\Brand\BrandDeleterAction::class);
                }
                
            );
        }
    );    
};
