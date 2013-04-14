<?php
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

    require dirname(__DIR__) . '/vendor/autoload.php';

    $server = IoServer::factory(
        new WsServer(new DevBieres\Server())
      , 8080
    );

    $server->run();
