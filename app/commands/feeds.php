<?php
namespace Commands;

// Use the PicoFeed library
use PicoFeed\Reader\Reader;
use PicoFeed\PicoFeedException;

class Feeds
{
	private $_feed;
	private $_item;
	private $_reader;
	
	public function __construct() {
		// Table objects
		$this->_feed = new \DB\SQL\Mapper(\Base::instance()->get('DB'),'feeds');
		$this->_item = new \DB\SQL\Mapper(\Base::instance()->get('DB'), 'feed_items');
		
		// picoFeed Reader
		$this->_reader = new Reader();
	}
	
	public function run()
	{
		$list = $this->_feed->find(array('status=?', 2));
		
		foreach ($list as $obj) 
		{
			try {
				$resource = $this->_reader->download($obj->url);
				
				// Return the right parser instance according to the feed format
				$parser = $this->_reader->getParser(
						$resource->getUrl(),
						$resource->getContent(),
						$resource->getEncoding()
				);
				
				// Return a Feed object
				$feed = $parser->execute();
				
				// Load the last feed post. 
				$this->_item->load(
						array('feed_id=?',$obj->id),
						array(
								'order'=>'pubDate DESC',
								'limit'=>1
						)
				);
				
				// Parse all feed items
				foreach(array_reverse($feed->items) as $item)
				{	
					if ($this->_item->dry() || $item->getDate() > date_create($this->_item->pubDate)) {
						// Save an instance of our entry
					 	$entry = &$this->_item;
					 	$entry->reset();
					 	
						$entry->feed_id = $obj->id;
						$entry->title = $item->getTitle();
						$entry->description = $item->getContent();
						$entry->author = $item->getAuthor();
						$entry->url = $item->getUrl();
						$entry->pubDate = $item->getDate()->format("Y-m-d H:i:s");
						$entry->insert();
					}					 
				}
				
			} catch (PicoFeedException $e) {
				// TODO: Add a logging system.
			}
		}
	}
}