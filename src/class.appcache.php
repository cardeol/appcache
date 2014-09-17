<?php
/*
* Clase Cache
* carlos de oliveira
* cardeol@gmail.com
*/

// cache dir requires read/write permissions 
define("APPCACHE_DEFAULT_DIR",dirname(dirname(__FILE__))."/cache/");


class AppCache
{
	var $cache_dir; 
	var $cache_time;
	var $caching = false; 
	var $cleaning = false; 
	var $compression = false;
	var $file = ''; 
	var $key;
	var $keylink;
	var $prefix;

	const CACHE_TIME = 28800;
	
	function __construct() {
		$this->cache_time = self::CACHE_TIME;
		$this->cache_dir = APPCACHE_DEFAULT_DIR;	
		$this->cleaning = NULL;
		$this->key = "appcacheid";	
		$this->keylink = urlencode($_SERVER['REQUEST_URI']);
		$this->prefix = "cache_";
	}
	
	function deleteCache() {
		$files = glob($this->cache_dir."cache_*"); 
		foreach($files as $f) {
			if(file_exists($f)) @unlink($f);
		}
		return true;
	}

	public function compressData($needcompression) {
		$this->compression = $needcompression;
	}
	
	public function setKey($link) {
		$this->keylink = serialize($link);
	}

	
	public function setTime($seconds) {
		$this->cache_time = $seconds;	
	}
	
	public function getFile() {
		return $this->cache_dir.$this->prefix.md5($this->key.$this->keylink).".txt"; 
	}

	public function clear() {
		$file = $this->getFile();
		if(file_exists($file) && is_file($file)) @unlink($file);
		return true;
	}
	
	public function getCache() {
		return $this->start(true);	
	}
	
	public function start($return = false)	{				
		$this->file = $this->getFile();
		if (file_exists($this->file) && (
			fileatime($this->file)+$this->cache_time)>time() && 
			$this->cleaning == false)	{
			$data = file_get_contents($this->file);		
			if($this->compression) $data = bzdecompress($data);
			$data = unserialize($data);
			if($return) return $data;
			echo $data;			
		} else {		
			$this->caching = true;
			return false;
		}
	}
	
	public function saveOutput($data) {
		$this->finish($data,false);
	}
	
	public function finish($data, $echo = true){
		$data = serialize($data);
		if ($this->caching){
			if($echo) echo $data;
			if(file_exists($this->file)) unlink($this->file);
			$fp = fopen( $this->file , 'w' );
			if($this->compression) $data = bzcompress($data);
			fwrite ( $fp , $data );
			fclose ( $fp );
		}
	}	
} 

?>
