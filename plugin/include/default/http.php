<?php

	return Affinity\Action::create(['core', 'events'], function($app, $broker) {

		//
		// Boostrap response states and codes
		//

		foreach ($app['engine']->fetch('http', 'response_states') as $status => $data) {
			if (isset($data['code'])) {
				Inkwell\HTTP\Resource\Response::addCode($status, $data['code']);
			}

			if (isset($data['body'])) {
				Inkwell\HTTP\Resource\Response::addMessage($status, $data['body']);
			}
		}

		Inkwell\HTTP\Resource\Response::setDefaultStatus(
			$app['engine']->fetch('http', 'default_status', IW\HTTP\NOT_FOUND)
		);


		//
		// Handle JSON Encoding Output
		//

		$app['events']->on('Router::end', function($action, $data) {
			$response = $data['response'];

			if ($response->headers->get('Content-Type') == 'application/json') {
					$response->set(json_encode($response->get()));
			}
		});

		//
		// Spin up our gateway and populate the request
		//

		$response = $broker->make('Inkwell\HTTP\Resource\Response');
		$request  = $broker->make('Inkwell\HTTP\Resource\Request');
		$gateway  = $broker->make('Inkwell\HTTP\Gateway\Server');

		if (!$app->checkSAPI('cli', 'embed')) {
			$gateway->populate($request);
		}


		//
		// Set up providers
		//

		$app['gateway']  = $gateway;
		$app['request']  = $request;
		$app['response'] = $response;
	});
