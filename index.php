<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use App\Models\Entity\Acesso;
use App\Models\Entity\Usuario;

require 'bootstrap.php';

$app->get('/', function (Request $request, Response $response) use ($app) {
    $response->getBody()->write("TRACER!!");
    return $response;
});

function setOffline($entityManager) {
    $acessosRepository = $entityManager->getRepository("App\Models\Entity\Acesso");

    $currentOnlineUsers = $acessosRepository->findBy(array('online' => true));

    $horarioAtual = (new DateTime());

    foreach ($currentOnlineUsers as $access) {
        error_log ($access->data_hora_visto_por_ultimo->format("Y/m/d H:i:s"));
        if (($horarioAtual->getTimestamp() - $access->data_hora_visto_por_ultimo->getTimestamp()) > 60) {
            $access->online = false;
            $entityManager->persist($access);
            $entityManager->flush();
        }
    }
}

function findUsuario($entityManager, $mac) {
    $usuariosRepository = $entityManager->getRepository("App\Models\Entity\Usuario");

    $usuario = $usuariosRepository->findBy(array('mac_celular' => $mac))[0];

    return $usuario;
}

function listaOnline($entityManager) {
    $acessosRepository = $entityManager->getRepository("App\Models\Entity\Acesso");

    $acessosOnline = $acessosRepository->findBy(array('online' => true));

    return $acessosOnline;
}

function acessoOnline($lista, $mac) {
    foreach ($lista as $acesso) {
        if ($acesso->mac_usuario === $mac) {
            return $acesso;
        }
    }
    return null;
}


//INSERE ACESSO (+1-)
$app->post('/acessos', function (Request $request, Response $response) use ($app) {
    $params = $request->getParams();
    $entityManager = $this->get('em');
    $horario = (new DateTime());

    setOffline($entityManager);

    foreach ($params as $node) {
        $acesso_param = (object) $node;

        //VERIFICA QUAIS DA LISTA RECEBIDA ESTAO ONLINE, SE ESTIVER ONLINE, VAI SETAR O VISTO POR ULTIMO COM O HORARIO DE AGORA
        $acesso_online = acessoOnline(listaOnline($entityManager), $acesso_param->mac);
        if ( $acesso_online != null ) {
            $acesso_online->data_hora_visto_por_ultimo = $horario;
            $acesso_online->intensidade_sinal = $acesso_param->rssi;

            $entityManager->persist($acesso_online);
            $entityManager->flush();
        } else {
            $acesso = new Acesso();
            $acesso->mac_usuario = $acesso_param->mac;
            $acesso->data_hora_entrada = $horario;
            $acesso->intensidade_sinal = $acesso_param->rssi;
            $acesso->online = true;
            $acesso->data_hora_visto_por_ultimo = $horario;

            $entityManager->persist($acesso);
            $entityManager->flush();
        }
    }

    $return = $response->withStatus(201);

    return $return;
});

//LISTA PESSOAS ON
$app->get ("/users/on", function (Request $request, Response $response) use ($app) {
    $entityManager = $this->get('em');
    setOffline($entityManager);
    $acessosRepository = $entityManager->getRepository("App\Models\Entity\Acesso");

    $acessosOnline = $acessosRepository->findBy(array('online' => true));

    $return = $response->withJson($acessosOnline, 200)
        ->withHeader('Content-type', 'application/json');

    return $return;
});

function setDotOnMac($mac_undotted) {
    $result = "";
    for ($i = 0; $i < 6; $i++) {
        $result = $result.substr($mac_undotted, $i*2, 2).".";
    }
    return substr($result, 0, -1);
}

//LISTA MAPA CALOR {mac} = users/ababababab...
$app->get('/users/{mac}', function (Request $request, Response $response) use ($app) {
    $route = $request->getAttribute('route');
    $mac = $route->getArgument('mac');
    $entityManager = $this->get('em');
    setOffline($entityManager);
    $acessosRepository = $entityManager->getRepository("App\Models\Entity\Acesso");

    $acessosPessoa = $acessosRepository->findBy(array('mac_usuario' => setDotOnMac($mac)));

    $return = $response->withJson($acessosPessoa, 200)
        ->withHeader('Content-type', 'application/json');

    return $return;
});


$app->run();






/*
$app->get('/acessos', function(Request $request, Response $response) use ($app) {
	$route = $request->getAttribute('route');
    $entityManager = $this->get('em');
    $acessosRepository = $entityManager->getRepository('App\Models\Entity\Acesso');
    $acesso = $acessosRepository->findAll();

    $return = $response->withJson($acesso, 200)
        ->withHeader('Content-type', 'application/json');

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

$app->get('/analise/{tempo}', function (Request $request, Response $response) use($app) {
    $route = $request->getAttribute('route');
    $tempo = $route->getArgument('tempo');

    $entityManager = $this->get('em');
    $acessosRepository = $entityManager->getRepository('App\Models\Entity\Acesso');

    $usuariosRepository = $entityManager->getRepository('App\Models\Entity\Usuario');

    $usuarios = $usuariosRepository->findAll();

    foreach ($usuarios as $usuario) {
        $acessos = $acessosRepository->findBy(array('mac' => $usuario->mac));

        foreach ($acessos as $acesso) {
            $response->getBody()->write("X");
        }
        $response->getBody()->write("<br>");
    }

    return $response;
});*/