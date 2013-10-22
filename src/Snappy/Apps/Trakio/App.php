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
	 * The application service's main website.
	 *
	 * @var string
	 */
	public $website = 'http://trak.io';

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
		array('name' => 'token', 'type' => 'text', 'help' => 'Enter your API Token', 'validate' => 'required'),
		array('name' => 'channel', 'placeholder' => 'Snappy', 'type' => 'text', 'help' => 'Trak.io Channel Name', 'validate' => 'required'),
		array('name' => 'event', 'placeholder' => 'Incoming Message', 'type' => 'text', 'help' => 'Trak.io event name', 'validate' => 'required'),
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
		$this->send('identify', array(
			'distinct_id' => $contact['value'],
			'properties' => array(
				'name' => $contact['first_name']. ' '. $contact['last_name'],
				'email' => $contact['value']
		  )
		));
	}

	/**
	 * Track an incoming message
	 *
	 * @param  array  $message
	 * @return void
	 */
	public function handleIncomingMessage(array $message)
	{
		$this->send('track', array(
			'distinct_id' => $message['creator']['value'],
			'event' => $this->config['event'],
			'channel' => $this->config['channel'],
			'properties' => array(
				'ticket' => $message['ticket_id'],
				'name' => $message['creator']['first_name']. ' ' . $message['creator']['last_name'],
				'email' => $message['creator']['value'],
			)
		));
	}

	/**
	 * Send the actual request
	 *
	 * @param  string $action The api action
	 * @param  array  $data   Array of properties to sent to the api
	 * @return void
	 */
	protected function send($action, array $data)
	{
		$client = new Client('http://api.trak.io/');
		$request = $client->post('/v1/'.$action);
		$request->setPostField('token', $this->config['token']);
		$request->setPostField('data', json_encode($data));
		$request->send();
	}
}
