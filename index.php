<?php
include 'framework/App.php';

// HTTP
include 'framework/extensions/HTTP.php';
/*
to do:
-param validation
-method name validation (failure is invalid request)- necessary?
-null ids are considered notifications and don't return a response on success
*/

$app = new App(new HTTP());

$app->middleware('authentication', function ($request, $response){
  return $response->result('authentication');
});

$app->middleware('authorization',function ($request, $response){
  return $response->result('authorization');
});

$app->group('this', ['authentication'], function ($app){
  $app->group('That', ['authorization'], function ($app){
    $app->method('Other', ['param1', 'param2'], [], function ($request, $response) {
      return $response->result(shell_exec('google-chrome dgg.gg'));
    });
  });
});
$app->run();
