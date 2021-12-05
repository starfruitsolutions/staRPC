<?php
/*
to do:
-param validation
-method name validation (failure is invalid request)- necessary?
-null ids are considered notifications and don't return a response on success
*/
require 'vendor/autoload.php';

$source = new StaRPC\Source\HTTP();
$app = new StaRPC\App($source);

$app->middleware('authentication', function ($request, $response) use ($app){
  if ($app->source->authentication != 'Bearer 8675309'){
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
