<?php namespace Snappy\Apps\Trakio;

use Guzzle\Http\Client;
use Snappy\Apps\App as BaseApp;
use Snappy\Apps\ContactCreatedHandler;
use Snappy\Apps\IncomingMessageHandler;

class App extends BaseApp implements ContactCreatedHandler, IncomingMessageHandler {

	/**
	 * The name of the application.
	 *
	 * @var string
	 */
	public $name = 'trak.io';

	/**
	 * The application description.
	 *
	 * @var string
	 */
	public $description = 'Send metrics to trak.io';

	/**
	 * Any notes about this application
	 *
	 * @var string
	 */
	public $notes = '<p>You can find your trak.io API token from your <a href="https://dash.trak.io/account/api_token" target="_blank">account settings</a>.</p>';

	/**
	 * The application's icon filename.
	 *
	 * @var string
	 */
	public $icon = 'trakio.png';

	/**
	 * The application author name.
	 *
	 * @var string
	 */
	public $author = 'UserScape, Inc.';

	/**
	 * The application author e-mail.
	 *
	 * @var string
	 */
	public $email = 'it@userscape.com';

	/**
	 * The settings required by the application.
	 *
	 * @var array
	 */
	public $settings = array(
		array('name' => 'token', 'type' => 'text', 'help' => 'Enter your API Token'),
	);

	/**
	 * Add the contact
	 *
	 * @param  array  $ticket
	 * @param  array  $contact
	 * @return void
	 */
	public function handleContactCreated(array $ticket, array $contact)
	{
		$client = $this->getClient();
		$identify = array(
			"distinct_id" => $contact['value'],
			"properties"=>array(
				"name"=> $contact['first_name']. ' '. $contact['last_name'],
				"email"=> $contact['value']
		  )
		);
		$request = $client->post('/v1/identify');
		$request->setPostField('token', $this->config['token']);
		$request->setPostField('data', json_encode($identify));
		$response = $request->send();
	}

	/**
	 * Track an incoming message
	 *
	 * @param  array  $message [description]
	 * @return void
	 */
	public function handleIncomingMessage(array $message)
	{
		$track = array(
			'distinct_id' => $message['contact']['value'],
			'event' => 'Incoming Message',
			'channel' => 'Snappy',
			'properties' => array(
				'ticket' => $message['id'],
				'name' => $message['contact']['first_name']. ' ' . $message['contact']['last_name'],
				'email' => $message['contact']['value'],
			)
		);
		$client = $this->getClient();
		$request = $client->post('/v1/track');
		$request->setPostField('token', $this->config['token']);
		$request->setPostField('data', json_encode($track));
		$response = $request->send();
	}

	/**
	 * Get the client instance.
	 *
	 * @return \Guzzle\Http\Client
	 */
	public function getClient()
	{
		return new Client('http://api.trak.io/');
	}

}
