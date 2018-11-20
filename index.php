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

function acessoOnline($lista, $mac, $id_device) {
    foreach ($lista as $acesso) {
        if ($acesso->mac_usuario === $mac && $acesso->id_dispositivo->id === $id_device) {
            return $acesso;
        }
    }
    return null;
}

function getDispositivo($entityManager, $id) {
    $dispRepository = $entityManager->getRepository("App\Models\Entity\Dispositivo");

    $dispositivo =  $dispRepository->find($id);

    return $dispositivo;
}


//INSERE ACESSO (+1-)
$app->post('/acessos', function (Request $request, Response $response) use ($app) {
    $params = $request->getParams();
    $entityManager = $this->get('em');
    $horario = (new DateTime());


    $dispositivo1 = getDispositivo($entityManager, 1);
    $dispositivo2 = getDispositivo($entityManager, 2);

    setOffline($entityManager);

    foreach ($params as $node) {
        $acesso_param = (object) $node;

        //VERIFICA QUAIS DA LISTA RECEBIDA ESTAO ONLINE, SE ESTIVER ONLINE, VAI SETAR O VISTO POR ULTIMO COM O HORARIO DE AGORA
        $acesso_online = acessoOnline(listaOnline($entityManager), $acesso_param->mac, $acesso_param->id_dev);
        if ( $acesso_online != null ) {
            $acesso_online->data_hora_visto_por_ultimo = $horario;
            $acesso_online->intensidade_sinal = $acesso_param->rssi;

            $entityManager->persist($acesso_online);
            $entityManager->flush();
        } else {
            if (strlen($acesso_param->mac) == 17) {
                $acesso = new Acesso();
                $acesso->id_dispositivo = ($acesso_param->id_dev == 1)? $dispositivo1 : $dispositivo2;//TODO
                $acesso->mac_usuario = $acesso_param->mac;
                $acesso->data_hora_entrada = $horario;
                $acesso->intensidade_sinal = $acesso_param->rssi;
                $acesso->online = true;
                $acesso->data_hora_visto_por_ultimo = $horario;

                $entityManager->persist($acesso);
                $entityManager->flush();
            }
        }
    }

    $return = $response->withStatus(201);

    return $return;
});

function getJson($lista) {

    $json = "[";
	foreach ($lista as $acesso) {
	    //error_log(json_encode($acesso->id_dispositivo->id));
	    $json .= "{";
	    $json .= '"id":'.$acesso->id.',';
        $json .= '"mac_usuario":"'.$acesso->mac_usuario.'",';
        $json .= '"id_dispositivo":'.json_encode((is_null($acesso->id_dispositivo)?null:$acesso->id_dispositivo->id)).',';
        $json .= '"intensidade_sinal":'.$acesso->intensidade_sinal.',';
        $json .= '"data_hora_entrada":"'.$acesso->data_hora_entrada->format('Y-m-d H:i:s').'",';
        $json .= '"data_hora_visto_por_ultimo":"'.$acesso->data_hora_visto_por_ultimo->format('Y-m-d H:i:s').'",';
        $json .= '"online":'.json_encode($acesso->online)."},";
	}
	if (strlen($json) != 1) {
        $json[strlen($json) - 1] = ']';
    } else {
	    $json .= "]";
    }

	return $json;
}

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

$app->get ('/users', function (Request $request, Response $response) use ($app) {
    $entityManager = $this->get('em');
    setOffline($entityManager);
    $acessosRepository = $entityManager->getRepository("App\Models\Entity\Acesso");
    $acessos = $acessosRepository->findAll();

    $return = $response->withHeader('Content-type', 'application/json')->write(getJson($acessos));

    return $return;
});

$app->get('/allusers', function (Request $request, Response $response) use ($app) {
    $entityManager = $this->get('em');
    setOffline($entityManager);
    $usuariosRepository = $entityManager->getRepository("App\Models\Entity\Usuario");
    $usuarios = $usuariosRepository->findAll();

    $return = $response->withJson($usuarios, 200)
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

    $return = $response->withHeader('Content-type', 'application/json')->write(getJson($acessosPessoa));

    return $return;
});

$app->get('/users/{mac}/nome', function (Request $request, Response $response) use ($app) {
    $route = $request->getAttribute('route');
    $mac = $route->getArgument('mac');
    $entityManager = $this->get('em');
    setOffline($entityManager);
    $usersRepository = $entityManager->getRepository("App\Models\Entity\Usuario");

    $pessoa = $usersRepository->findBy(array('mac_celular' => setDotOnMac($mac)));

    $return = $response->withJson($pessoa[0], 200)
        ->withHeader('Content-type', 'application/json');

    return $return;
});

//LISTA USUARIOS ON NO DISPOSITIVO
$app->get('/users/device/{id}', function (Request $request, Response $response) use ($app) {
    $route = $request->getAttribute('route');
    $id = $route->getArgument('id');
    $entityManager = $this->get('em');
    setOffline($entityManager);
    $acessosRepository = $entityManager->getRepository("App\Models\Entity\Acesso");

    $acessosDispositivo = $acessosRepository->findBy(array('online' => true, 'id_dispositivo' => $id));

    $return = $response->withHeader('Content-type', 'application/json')->write(getJson($acessosDispositivo));

    return $return;
});

$app->post('/users/new', function (Request $request, Response $response) use ($app) {
    $entityManager = $this->get('em');
    $params = (object) $request->getParams();

    //error_log(json_encode($params));

    $horario = (new DateTime());

    $usuario = new Usuario();
	 $usuario->data_cadastro = $horario;
	 $usuario->ativo = true;
	 $usuario->email = $params->email;
	 $usuario->mac_celular = $params->mac;
	 $usuario->nome = $params->nome;

    $entityManager->persist($usuario);
    $entityManager->flush();

    $return = $response->withJson($usuario, 201)
        ->withHeader('Content-type', 'application/json');

    return $return;
});

$app->get('/devices', function (Request $request, Response $response) use ($app) {
    $route = $request->getAttribute('route');
    $mac = $route->getArgument('mac');
    $entityManager = $this->get('em');

    $dispositivosRepository = $entityManager->getRepository("App\Models\Entity\Dispositivo");

    $dipositivos = $dispositivosRepository->findAll();

    $return = $response->withJson($dipositivos, 200)
        ->withHeader('Content-type', 'application/json');

    return $return;
});


$app->get('/devices/{mac}', function (Request $request, Response $response) use ($app) {
    $route = $request->getAttribute('route');
    $mac = $route->getArgument('mac');
    $entityManager = $this->get('em');

    $dispositivosRepository = $entityManager->getRepository("App\Models\Entity\Dispositivo");

    $dipositivo = $dispositivosRepository->findBy(array('mac_usuario' => setDotOnMac($mac)));

    $return = $response->withHeader('Content-Type', 'text/plain')->write($dipositivo[0]->id);

    return $return;
});

//TODO PEGAR ESTATISTICAS DE CADA DISPOSITIVO, COMO QNTD DE CADASTRADOS E USUARIOS

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
