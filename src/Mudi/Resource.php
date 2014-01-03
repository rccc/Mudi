<?php

namespace Mudi;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
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
	public $authorizedExtensions = array("htm","html", "zip");
	public $files       = array(); // tableau contenant une liste de fichiers 


	public function __construct($name)
	{
		$this->name = $this->slugify($name);

		var_dump('SLUG', $this->name);

		$this->path = $this->getPathFromName();

		if(is_file($this->path))
		{
			$this->isFile = true;

			$ext =  substr(strrchr($this->name, '.'), 1);
			$this->ext = $ext;
			if(!in_array($ext, $this->authorizedExtensions))
			{
				throw new \Exception('Le type de fichier est invalide');    
			}

			switch($ext)
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
		if(empty($this->files[$motif]))
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

				foreach ($finder as $file) {
					$this->files[$motif][] = $file;
				}

			}
		}

		return $this->files[$motif];
	}

	public function extractArchive()
	{
		$fs = TemporaryFilesystem::create();
		$this->archive_path = $fs->createTemporaryDirectory();

		$zip = new \ZipArchive();
		if ($zip->open($this->path) === TRUE) {
			$zip->extractTo($this->archive_path);
			$zip->close();
		}
	}

	public function slugify($text) 
	{ 
                // replace non letter or digits by - 
		$text = preg_replace('~[^\\pL\d]+~u', '-', $text); 

                 // trim 
		$text = trim($text, '-'); 

                  // transliterate 
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text); 

                  // lowercase 
		$text = strtolower($text); 

                  // remove unwanted characters 
		$text = preg_replace('~[^-\w]+~', '', $text); 
		echo $text; 
		if (empty($text)) 
		{ 
                         //echo 'in'; 
			return 'n-a'; 
		} 

		return $text; 
	} 

}