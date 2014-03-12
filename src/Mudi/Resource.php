<?php

namespace Mudi;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Neutron\TemporaryFilesystem\TemporaryFilesystem;

/**
 * la ressource devant être validée.
 * Peut être un fichier, dossier ou archive 'zip'
 * à terme : tout type d'archives, url.
 */
class Resource
{
	public $isFile		= false;
	public $isHtml		= false;
	public $isDir		= false;
	public $isArchive	= false;
	public $isZip		= false;
	public $ext			= "";
	public $name 		= "";
	public $path 		= "";
	public $archive_path = "";  //chemin du dossier temporaire contenant le contenu de l'archive
	public $authorizedExtensions = array("htm","html", "css", "zip");
	public $files       = array(); // tableau contenant une liste de fichiers 


	public function __construct($name)
	{
		$this->name = $name;
		$this->ext = substr(strrchr($this->name, '.'), 1);
		$this->path = $this->getPathFromName();

		$this->name = self::slugify( pathinfo($name)['basename'] );
		
		if(is_file($this->path))
		{
			$this->isFile = true;

			if(!in_array($this->ext, $this->authorizedExtensions))
			{
				throw new \Exception('Le type de fichier est invalide');    
			}

			switch($this->ext)
			{
				case 'htm':
				case 'html':
				$this->isHtml = true;
				break;
				case 'zip':
				$this->isArchive = true;
				$this->isZip = true;
				$this->extractArchive();
				break;

			}

		}
		elseif(is_dir($this->path)){
			$this->isDir = true;
		}
		else 
		{
			throw new \Exception(sprintf('La ressource spécifiée est invalide : %s', $name));
		}	
	}

	public function getPathFromName()
	{
		return stream_resolve_include_path($this->name);
	}


	public function getFiles($motif = '*.html')
	{
		if(!isset($this->files[$motif]))
		{	
			if($this->isHtml)
			{
				$this->files[$motif][] = new \SplFileInfo($this->name);
				
			}
			elseif($this->isArchive || $this->isDir)
			{

				$path = $this->isArchive ?  $this->archive_path : $this->path;
				$finder = new Finder();        
				$finder->files()->in($path)->name($motif);

				if(count($finder)=== 0)
				{
					return array();
				}

				foreach ($finder as $file) {
					$this->files[$motif][] = $file;
				}

			}
		}

		return $this->files[$motif];
	}

	public function extractArchive()
	{
		var_dump('Resource::extractArchive');

		$fs = TemporaryFilesystem::create();
		$this->archive_path = $fs->createTemporaryDirectory();

		var_dump('archive_path', $this->archive_path);

		$zip = new \ZipArchive();
		if ($zip->open($this->path) === TRUE) {
			$zip->extractTo($this->archive_path);
			$zip->close();
		}

	}

	public static function slugify($text) 
	{ 
		$text = preg_replace('~[^\\pL\d]+~u', '-', $text); 
		$text = trim($text, '-'); 
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text); 
		$text = strtolower($text); 
		$text = preg_replace('~[^-\w]+~', '', $text); 
		if (empty($text)) 
		{ 
			return 'n-a'; 
		} 

		return $text; 
	} 

	public function __destruct()
	{
		if(!empty($this->archive_path))
		{
			$this->delete_archive();
		}
	}

	public function delete_archive($file = "") { 

		$fs = new Filesystem();
		$fs->remove($this->archive_path);

	}
}