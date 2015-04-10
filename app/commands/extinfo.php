<?php
namespace Commands;

// Use killme's extinfo library
// c = connection, s = socket
use Killme\SauerPHPQuery;
use Killme\SauerPHPQuery\SauerbratenQueryManager;
use Killme\SauerPHPQuery\Server;

class Extinfo
{
	private $_queryManager;
	private $_storage;
	
	// What is happening right now?
	private $_servers;
	private $_matches;
	private $_players;
	
	public function __construct()
	{
		$this->_queryManager = new SauerbratenQueryManager();
		$this->_storage = new \DB\Jig(\Base::instance()->get('TEMP')); // Use Jig for temporary databases
		$this->_storage->drop();
		
		// Add new local databases
		$this->_servers = new \DB\Jig\Mapper($this->_storage, 'servers.json');
		$this->_matches = new \DB\Jig\Mapper($this->_storage, 'matches.json');
		$this->_players = new \DB\Jig\Mapper($this->_storage, 'players.json');
	}
	
	public function run()
	{
		$this->updateFromMaster();
		
		foreach($this->_servers->find() as $server) {
			try {
				$c = new Server($server->ip, $server->port +1);
				$ext = $this->_queryManager->query($c)->getQueryData();
			} catch (\UnderflowException $e) {
				//TODO: Add logging
			}
			
			// Update the basic server information
			$server->description = $ext['serverDescription'];
			$server->maxclients = $ext['maxClients'];
			$server->protocol = $ext['protocolVersion'];
			$server->master = $ext['serverMode']['name'];
			$server->save();
			
			// Update the currently running matches
			$this->_matches->reset();
			$this->_matches->server_id = $server->_id;
			$this->_matches->mode = $ext['gameMode']['name'];
			$this->_matches->players = $ext['playerCount'];
			$this->_matches->remaining = $ext['timeLeft'];
			$this->_matches->map = $ext['mapName'];
			$this->_matches->save();
			
			// Update the players according to the matches
			foreach($ext['players'] as $player) {
				$this->_players->reset();
				$this->_players->match_id = $this->_matches->_id;
				$this->_players->cn = $player['cn'];
				$this->_players->name = $player['name'];
				$this->_players->team = $player['team'];
				$this->_players->frags = $player['frags'];
				$this->_players->flags = $player['flags'];
				$this->_players->deaths = $player['deaths'];
				$this->_players->teamkills = $player['teamkills'];
				$this->_players->accuracy = $player['accuracy'];
				$this->_players->health = $player['health'];
				//$this->_players->ip = implode(".", $players['ip']);
				
				$this->_players->save();
			}
			
			break;
			
		}
	}
	
	// Preset sauerbraten's masterserver
	private function updateFromMaster($host = 'sauerbraten.org', $port = 28787)
	{
		try {
			$s = stream_socket_client('tcp://'.gethostbyname($host).':'.$port);
			fwrite($s, "list\n");

			$connections = "";
			while($buf = fread($s, 4096)) {
				$connections .= $buf;
			}
			
			fclose($s);
		} catch (\Exception $e) {
			//TODO: Add logging
		}
		
		$buf = explode("\n", $connections); // Use "" here because we would need an escape
		array_pop($buf); // Pop the last array because it is always an empty line
		
		array_walk($buf, function($value) {
			$c = explode(' ', substr($value, 10));
			
			// This can be true/false
			if (! $this->_servers->count(array('@ip = ? and @port = ?', $c[0], $c[1]))) {
				$this->_servers->reset();
				$this->_servers->ip = $c[0];
				$this->_servers->port = $c[1];
				$this->_servers->save();				
			}
		});
	}
}