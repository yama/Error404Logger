//<?php
/**
 * Error 404 Logger
 *
 * Plugin logs requests that trigger an Page not found error.
 *
 * @category    plugin
 * @version     0.1
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @author      Andraz Kozelj (andraz dot kozelj at amis dot net) date created: 14.03.2007
 * @author      yama (http://kyms.jp)
 * @internal    @events        OnPageNotFound, OnWebPageInit
 * @internal    @modx_category Manager and Admin
 * @internal    @properties &found_ref_only=Found ref only;list;yes,no;yes &count_robots=Robots count;list;yes,no;no; &robots=Robots list;text;googlebot,baidu,msnbot;&limit=Number of limit logs;1000 &trim=Number deleted at a time;100 &remoteIPIndexName=RemoteIP Index Name;text;REMOTE_ADDR
 */

$found_ref_only = (empty($found_ref_only)) ? 'no' : $found_ref_only;
$count_robots   = (empty($count_robots))   ? 'yes' : $count_robots;
$robots         = (empty($robots))         ? 'googlebot,baidu,msnbot' : $robots;
if(empty($limit)) $limit = 1000;
if(empty($trim))  $trim = 100;

$e = & $modx->event;

if($e->name=='OnWebPageInit' && isset($_SESSION['mgrValidated']))
{
	if($_GET['e404_redirect'])
	{
		$url = $_GET['e404_redirect'];
		$entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
		$replacements = array('!', '*', "'", '(', ')', ';', ':', '@', '&', '=', '+', '$', ',', '/', '?', '%', '#', '[', ']');
		$url = str_replace($entities, $replacements, urlencode($url));
		header('Refresh: 0.5; URL=' . $url);
		exit;
	}
	return;
}
elseif($e->name=='OnPageNotFound'  && !isset($_SESSION['mgrValidated']))
{
	if($found_ref_only == 'yes' && empty($_SERVER['HTTP_REFERER'])) return;
	if($count_robots   == 'no')
	{
		$host_name = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		foreach(explode(',',$robots) as $robot)
		{
			if(strstr($host_name, $robot)!==false) return;
		}
	}
	
	include_once($modx->config['base_path'] . 'assets/modules/error404logger/e404logger.class.inc.php');
	$e404 = new Error404Logger();
	
	$e404->insert($remoteIPIndexName);
	$e404->purge_log($limit,$trim);
}
else return;
