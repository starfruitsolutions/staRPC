<?php
/*
to do:
-param validation
-method name validation (failure is invalid request)- necessary?
-null ids are considered notifications and don't return a response on success
*/
namespace StaRPC;

require 'vendor/autoload.php';
$config = include('config.php');

$app = App::get();

//register channel
$app->channel(new Channel\HTTP());

//register source
$source = new Source\MySQL();
$source->connect($config['mysql']);
$app->source($source);

$app->middleware('authentication', function ($request, $response) use ($app){
  if ($app->channel->authentication != 'Bearer 8675309'){
    $response->error(-32222, 'Authentication failure');
  }
});

$app->middleware('authorization',function ($request, $response) use ($app) {
  return;
});

$app->group('company/', ['authentication'], function ($app){
  $app->group('client/', ['authorization'], function ($app){
    $app->method('getInvoice', ['invoiceID'], [], function ($request, $response){
      $request = [
        'sql'=>'SELECT * FROM Invoice WHERE invoiceID=:invoiceID',
        'params'=>[
          'invoiceID'=> $request->params['invoiceID']
        ]
      ];
      $data = App::get()->source->request($request);
      $response->result($data);
    });
  });
});
$app->run();
