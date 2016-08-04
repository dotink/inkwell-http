<?php

	return Affinity\Action::create(['core', 'events'], function($app, $broker) {

		//
		// Boostrap HTTP response states and codes
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
		// If we're being accessed via CLI, we're done
		//

		if ($app->checkSAPI('cli', 'embed')) {
			return;
		}

		//
		// Spin up our gateway and populate the request
		//

		$response = $broker->make('Inkwell\HTTP\Resource\Response');
		$request  = $broker->make('Inkwell\HTTP\Resource\Request');
		$gateway  = $broker->make('Inkwell\HTTP\Gateway\Server');

		$gateway->populate($request);

		//
		// Set up providers
		//

		$app['gateway']  = $gateway;
		$app['request']  = $request;
		$app['response'] = $response;
	});
