<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use App\Models\Entity\Acesso;

require 'bootstrap.php';

$app->get('/', function (Request $request, Response $response) use ($app) {
    $response->getBody()->write("TRACER!!");
    return $response;
});

$app->post('/acesso', function (Request $request, Response $response) use ($app) {
    $params = (object) $request->getParams();

    $entityManager = $this->get('em');

    $acesso = (new Acesso())->setMac($params->mac)
        ->setRssi($params->rssi);

    /**
     * Persiste a entidade no banco de dados
     */
    $entityManager->persist($acesso);
    $entityManager->flush();
    $return = $response->withJson($acesso, 201)
        ->withHeader('Content-type', 'application/json');
    return $return;
});

$app->post('/acessos', function (Request $request, Response $response) use ($app) {
    $params = $request->getParams();

    $entityManager = $this->get('em');

    //TODO VALIDATE received JSON on $params
    foreach ($params as $single_param) {
        $acesso_param = (object) $single_param;

        $acesso = (new Acesso())->setMac($acesso_param->mac)->setRssi($acesso_param->rssi);

        $entityManager->persist($acesso);
        $entityManager->flush();
    }

    $return = $response->withStatus(201);
    return $return;
});

$app->get('/acesso/{id}', function (Request $request, Response $response) use ($app) {
    $route = $request->getAttribute('route');
    $id = $route->getArgument('id');
    $entityManager = $this->get('em');
    $acessosRepository = $entityManager->getRepository('App\Models\Entity\Acesso');
    $acesso = $acessosRepository->find($id);
    $return = $response->withJson($acesso, 200)
        ->withHeader('Content-type', 'application/json');
    return $return;
});

$app->run();
