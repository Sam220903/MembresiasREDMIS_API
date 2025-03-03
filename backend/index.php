<?php
use Slim\App;
use Controllers\MembresiaController;

return function (App $app) {
    $app->post('/membresia', [MembresiaController::class, 'crearMembresia']);
};
