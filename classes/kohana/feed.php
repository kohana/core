<?php defined('SYSPATH') or die('No direct script access.');
/**
 * RSS and Atom feed helper.
 *
 * @package    Kohana
 * @category   Helpers
 * @license    http://kohanaframework.org/license
 */
class Kohana_Feed {

    const FEED_FORMAT_ATOM = 'atom';
    const FEED_FORMAT_RSS2 = 'rss2';

    private $format = Feed::FEED_FORMAT_RSS2;
    private $encoding = 'UTF-8';

    private $feed = array();
    private $entry = array();
    private $current_entry = array();


    /**
     * Setter for encoding
     */
    public function set_encoding($encoding)
    {
    	return $this->encoding = $encoding;
    }

    /**
     * Setter for format
     */
    public function set_format($format)
    {
    	if (in_array($format, array(Feed::FEED_FORMAT_ATOM, Feed::FEED_FORMAT_RSS2)))
    	{
    		return $this->format = $format;
    	}
    	throw new Exception('invalid feed format');
    }

    /**
     * Add one field to field
     * 
     * When $value is a scalar
     * <field attribue1="foo">value</field>
     * 
     * When $value is an array
     * <field attribue1="foo">
     *   <name>foo</name>
     *   <url>example.com</url>
     * </field>
     *
     * When $value is null
     * <field attribute1="foo" />
     *
     * @param	string 	tag name
     * @param	mixed  	tag value
     * @param	array   set of tag attributes as key value pairs
     */ 
    public function add_feed_info($field, $value = null, array $attributes = array())
    {
    	$this->feed[] = array($field, $value, $attributes);
    }

    /**
     * Add one field to current entry
     *
     */
    public function add_entry_info($field, $value = null, array $attributes = array())
    {
    	$this->current_entry[] = array($field, $value, $attributes);
    }


    /**
     * Close current entry and open a new one
     */
    public function next_entry()
    {
    	if (!empty($this->current_entry))
    	{
    		$this->entry[] = $this->current_entry;
    		$this->current_entry = array();
    	}
    }

    /**
     * Ouput XML from data collected
     *
     */
    public function render() 
    {	
    	$this->next_entry();
    	switch ($this->format)
    	{
    		case Feed::FEED_FORMAT_ATOM:
    			$feed = simplexml_load_string('<?xml version="1.0" encoding="'.$this->encoding.'"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>');
    			$entry_tag = 'entry';
    			break;
    		case Feed::FEED_FORMAT_RSS2:
    		default:
    			$feed = simplexml_load_string('<?xml version="1.0" encoding="'.$this->encoding.'"?><rss version="2.0"><channel></channel></rss>');
    			$entry_tag = 'item';
    			break;

    	}

    	$this->push_fields($this->feed, $feed);
    	foreach ($this->entry as $entry)
    	{
    		$curr_entry = $feed->addChild($entry_tag);
    		$this->push_fields($entry, $curr_entry);
    	}

    	// DOM is preferred, as it generates more readable XML
    	if (function_exists('dom_import_simplexml'))
    	{
    		$feed = dom_import_simplexml($feed)->ownerDocument;
    		$feed->formatOutput = TRUE;
    		return $feed->saveXML();
    	}

    	// Export the document as XML
    	return $feed->asXML();
    }

    /**
     * Helper which handles pushing sub fields and attributes
     *
     * @param array 
     * @param simplexml object
     */
    protected function push_fields($fields, $sxo)
    {
    	foreach ($fields as $curr)
    	{
    		list($field, $value, $attributes) = $curr;

    		if (is_array($value))
    		{
    			$child = $sxo->addChild($field);
    			foreach ($value as $subfield => $subvalue)
    			{
    				$child->addChild($subfield, $this->scrub_field($subfield, $subvalue));
    			}
    		}
    		else
    		{	
    			$child = $sxo->addChild($field, $this->scrub_field($field, $value));
    		}
    		foreach ($attributes as $attribute_name => $attribute_value)
    		{
    			$child->addAttribute($attribute_name, $this->scrub_field($attribute_name, $attribute_value));
    		}
    	}
    }


    /**
     * Filters field values
     *
     * Currently it tries to detect URL fields and attributes, and set them with current site domain if not already set.
     * May be extended to do more.
     * 
     * @param 	string	tag name
     * @param   string	tag value
     * @return	string  scrubbed value
     */
    protected function scrub_field($field, $value)
    {
    	if (in_array($field, array('href','url','uri','link'))
    		&& strpos($value, '://') === FALSE
    		&& !empty($value)
    	)
    	{
    		return URL::site($value, 'http');
    	}
    	return $value;
    }

    /**
     * Parses a remote feed into an array.
     *
     * @param   string   remote feed URL
     * @param   integer  item limit to fetch
     * @return  array
     */
    public static function parse($feed, $limit = 0)
    {
    	// Check if SimpleXML is installed
    	if ( ! function_exists('simplexml_load_file'))
    		throw new Kohana_Exception('SimpleXML must be installed!');

    	// Make limit an integer
    	$limit = (int) $limit;

    	// Disable error reporting while opening the feed
    	$error_level = error_reporting(0);

    	// Allow loading by filename or raw XML string
    	$load = (is_file($feed) OR Valid::url($feed)) ? 'simplexml_load_file' : 'simplexml_load_string';

    	// Load the feed
    	$feed = $load($feed, 'SimpleXMLElement', LIBXML_NOCDATA);

    	// Restore error reporting
    	error_reporting($error_level);

    	// Feed could not be loaded
    	if ($feed === FALSE)
    		return array();

    	$namespaces = $feed->getNamespaces(true);

    	// Detect the feed type. RSS 1.0/2.0 and Atom 1.0 are supported.
    	$feed = isset($feed->channel) ? $feed->xpath('//item') : $feed->entry;

    	$i = 0;
    	$items = array();

    	foreach ($feed as $item)
    	{
    		if ($limit > 0 AND $i++ === $limit)
    			break;
    		$item_fields = (array) $item;

    		// get namespaced tags
    		foreach ($namespaces as $ns)
    		{
    			$item_fields += (array) $item->children($ns);
    		}
    		$items[] = $item_fields;
    	}

    	return $items;
    }

    /**
     * Creates a feed from the given parameters.
     *
     * Deprecated
     * This function provides backwards compatability
     *
     * @param   array   feed information
     * @param   array   items to add to the feed
     * @param   string  define which format to use (rss2 or atom)
     * @param   string  define which encoding to use
     * @return  string
     */
    public static function create($info, $items, $format = Feed::FEED_FORMAT_RSS2, $encoding = 'UTF-8')
    {

        $info += array('title' => 'Generated Feed', 'link' => '', 'generator' => 'KohanaPHP');

    	$feed = new Feed();

        $feed->set_encoding($encoding);
        $feed->set_format($format);

        foreach ($info as $field => $value)
        {    
            $feed->add_feed_info($field, $value, array());
        }

        foreach ($items as $item)
        {
            $feed->next_entry();
            foreach ($item as $field => $value) $feed->add_entry_info($field, $value, array());
        }

        return $feed->render();

    }

} // End Feed