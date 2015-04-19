<?php
namespace Controllers;

class Login
{
	private $_user;
	private $_client;
	
	public function __construct()
	{
		$this->_user = new \DB\SQL\Mapper(\Base::instance()->get('DB'), 'users');
		
		// Set up a Google Client object according to https://developers.google.com/api-client-library/php/guide/aaa_oauth2_web
		$googleConfig = \Base::instance()->get('google');
		$this->_client = new \Google_Client();
		$this->_client->setAccessType('online');
		$this->_client->setApplicationName('cubelounge');
		$this->_client->setClientId($googleConfig['client-id']);
		$this->_client->setClientSecret($googleConfig['client-secret']);
		
		// Request access to email and basic profile information
		// We don't want Google+ circles crap
		$this->_client->setScopes(array("https://www.googleapis.com/auth/userinfo.email", "https://www.googleapis.com/auth/userinfo.profile"));		
	}
	
	public function index()
	{
		// Show the user's dashboard
		// TODO: There is lots of work to do (usability)!
		\Base::instance()->set('content', 'login/dashboard.htm');
		
		echo \View::instance()->render('layout.htm');
	}

	/**
	 * A function to update your account details
	public function update()
	{
		
	}*/
	
	public function signup()
	{
		if (null !== \Base::instance()->get('POST.name'))
		{
			/*
			 * Validate the POST data and (if valid) send a authentication request to Google
			 * - validate the username
			 * - send a redirect request to Google
			 */
			
			$name = \Base::instance()->clean(\Base::instance()->get('POST.name'));
			// If there is no record
			if (!$this->_user->count(array("name=?", $name)))
			{
				$this->_user->reset();
				$this->_user->name = $name;
				
				// Sanitize the optional variables
				if (null !== \Base::instance()->get('POST.alias'))
					$this->_user->alias = \Base::instance()->clean(\Base::instance()->get('POST.alias'));
				
				if (null !== \Base::instance()->get('POST.pubkey'))
					$this->_user->pubkey = \Base::instance()->clean(\Base::instance()->get('POST.pubkey'));
				
				$this->_user->status = 1; // Set user status to "pending"
				$this->_user->save();
				
				// Prepare a redirect URI
				$redirectUri =  \Base::instance()->get('SCHEME') . '://' . \Base::instance()->get('HOST') . '/login/signup';
				$this->_client->setRedirectUri($redirectUri);
				
				// Save the user id and redirect to Google
				\Base::instance()->set('SESSION.uid', $this->_user->id);
				\Base::instance()->reroute($this->_client->createAuthUrl());
			} /*else {
				TODO: This should be improved by a ajax check
			}*/
		} else if (null !== \Base::instance()->get('GET.code')) {
			/*
			 * Complete the registration by successfully logging in with Google's access code
			 * - authenticate the Google client
			 * - retrieve the email
			 * - update accordingly
			 */
			
			try {
				// Prepare a redirect URI
				$redirectUri =  \Base::instance()->get('SCHEME') . '://' . \Base::instance()->get('HOST') . '/login/signup';
				$this->_client->setRedirectUri($redirectUri);
				
				$this->_client->authenticate(\Base::instance()->get('GET.code'));
				
				// Retrieve user object
				$this->_user->load(array("id=?", \Base::instance()->get('SESSION.uid')));
				$userInfo = $this->requestUserInfo();

				// Update the user accordingly
				$this->_user->email = $userInfo->email;
				$this->_user->auth_id = $userInfo->id;
				$this->_user->status = 2;
				$this->_user->save();
				
				\Base::instance()->set('content', 'login/success.htm');
				echo \View::instance()->render('layout.htm');
			} catch (\Google_Exception $e) {
				//TODO: Add error page / logging
			}
		} else {
			/*
			 * Sign up page
			 */
			
			\Base::instance()->set('content', 'login/signup.htm');
			echo \View::instance()->render('layout.htm');
		}	
	}
	
	public function signin()
	{
		if (null !== \Base::instance()->get('GET.code')) {
			/*
			 * Sign in by retrieving the user's email from Google
			 * - authenticate using the code
			 * - retrieve user information
			 * - load user->id
			 */
			
			$redirectUri =  \Base::instance()->get('SCHEME') . '://' . \Base::instance()->get('HOST') . '/login/signin';
			$this->_client->setRedirectUri($redirectUri);
			
			try {
				$this->_client->authenticate(\Base::instance()->get('GET.code'));
				$userInfo = $this->requestUserInfo();
				
				$this->_user->load(array("auth_id=?", $userInfo->id));
				
				// auth_id is unique, therefore only true/false can apply
				if ($this->_user->count(array("id=?", $this->_user->id))) {
					\Base::instance()->set('SESSION.uid', $this->_user->id);
					\Base::instance()->reroute('/login');
				} else {
					\Base::instance()->error(404);
				}
			} catch (\Google_Exception $e) {
				//TODO: Add logging
			}			
		} else {
			// Prepare a redirect URI
			$redirectUri =  \Base::instance()->get('SCHEME') . '://' . \Base::instance()->get('HOST') . '/login/signin';
			$this->_client->setRedirectUri($redirectUri);
			
			\Base::instance()->reroute($this->_client->createAuthUrl());
		}
		
	}
	
	private function requestUserInfo()
	{
		$oAuth = new \Google_Service_Oauth2($this->_client);
		return $oAuth->userinfo->get();
	}
}