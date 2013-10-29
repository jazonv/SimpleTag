<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Simple Tag Plugin
 *
 * @package     ExpressionEngine
 * @subpackage  Addons
 * @category    Plugin
 * @author      Jason Varga
 * @link        http://pixelfear.com
 */

$plugin_info = array(
	'pi_name'		=> 'Simple Tag',
	'pi_version'	=> '1.0',
	'pi_author'		=> 'Jason Varga',
	'pi_author_url'	=> 'http://pixelfear.com',
	'pi_description'=> 'Simple Tag',
	'pi_usage'		=> Simpletag::usage()
);


class Simpletag {
    
	private $tags, $tagdata, $prefix;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance(); 

		$this->tagdata = ee()->TMPL->tagdata;
		$this->prefix = ee()->TMPL->fetch_param('prefix', 'tag');
		$tags = ee()->TMPL->fetch_param('tags');
		if (!empty($tags))
		{
			$this->tags = array_map('trim', explode(',', $tags));
		}
	}

	public function pair()
	{
		// take a comma separated list of tags and loop through them
		if (!empty($this->tags))
		{
			$p = $this->prefix.':';
			$vars = array();
			foreach ($this->tags as $i => $tag)
			{
				$vars[] = array(
					$p.'name'      => $tag,
					$p.'url'       => urlencode($tag),
					$p.'index'     => $i,
					$p.'count'     => $i+1,
					$p.'first'     => ($i === 0) ? true : false,
					$p.'not_first' => ($i === 0) ? false : true,
					$p.'last'      => ($i === count($this->tags)-1) ? true : false,
					$p.'not_last'  => ($i === count($this->tags)-1) ? false : true
				);
			}
			return ee()->TMPL->parse_variables($this->tagdata, $vars);
		}
		
	}

	public function count()
	{
		return count($this->tags);
	}

	public function entry_ids()
	{
		// get tag field id
		$field_name = ee()->TMPL->fetch_param('field');
		$query = ee()->db->select('field_id')->where('field_name', $field_name)->get('channel_fields');
		$field_id = $query->result_array()[0]['field_id'];
		$field = 'field_id_'.$field_id;

		// get entries
		$tag = ee()->TMPL->fetch_param('tag');
		$tag = str_replace('+', ' ', $tag);
		$query = ee()->db->select('entry_id')->like($field, $tag)->get('channel_data');
		$entry_ids = array();
		foreach ($query->result_array() as $row)
		{
			$entry_ids[] = $row['entry_id'];
		}
		
		// return them, pipe separated
		return implode('|', $entry_ids);
	}

	
	// ----------------------------------------------------------------
	
	/**
	 * Plugin Usage
	 */
	public static function usage()
	{
		ob_start();
?>
Takes a comma separated list of tags and lets you loop through them.
<ul>
{exp:simpletag:pair tags="{your_tags_field}"}
  <li>{tag}</li>
{/exp:simpletag:pair}
</ul>
or output the count: {exp:simpletag:count tags="{your_tags_field}"}
<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}


/* End of file pi.simpletag.php */
/* Location: /system/expressionengine/third_party/simpletag/pi.simpletag.php */