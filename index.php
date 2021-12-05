<?php
/*
to do:
-param validation
-method name validation (failure is invalid request)- necessary?
-null ids are considered notifications and don't return a response on success
*/
require 'vendor/autoload.php';

$channel = new StaRPC\Channel\HTTP();
$app = new StaRPC\App($channel);

$app->middleware('authentication', function ($request, $response) use ($app){
  if ($app->channel->authentication != 'Bearer 8675309'){
    $response->error(-32222, 'Authentication failure');
  }
});

$app->middleware('authorization',function ($request, $response) use ($app) {
  return;
});

$app->group('group1', ['authentication'], function ($app){
  $app->group('Group2', ['authorization'], function ($app){
    $app->method('Test', ['param1'], [], function ($request, $response) {
      $response->result('test');
    });
  });
});
$app->run();
