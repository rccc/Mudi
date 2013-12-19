<?php

namespace Mudi;

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
	public $errors      = array();
	public $results		= array();
	public $name 		= "";
	public $path 		= "";
	public $authorizedExtensions = array("htm","html", "zip");


	public function __construct($name)
	{
		$this->name = $name;
		$this->path = $this->getPathFromName();

		if(is_file($this->path))
		{

			$this->isFile = true;

			$ext = end(explode('.', $name));
			$this->ext = $ext;

			if(!in_array($ext, $this->authorizedExtensions))
			{
				throw new Exception('Le type de fichier est invalide');    
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

	public function getUrls()
	{
		$urls = array();
		$array = array();

		if($this->isHtml)
		{
			$array[$this->path] = $this->getNodeList($this->path);
		}

		elseif($this->isArchive)
		{
			$tmp_path = $this->createTmpDir();
            $zip = new \ZipArchive();
                if ($zip->open($this->path) === TRUE) {
                    $zip->extractTo($tmp_path);
                    $zip->close();
                } 
                else {
                    //@todo log
                }			
			$array = $this->deepGetNodeList($tmp_path);
			$this->removeTmpDir($tmp_path);
		}

		elseif($this->isDir)
		{
			$array = $this->deepGetNodeList($this->path);	
		}

		foreach($array as $documentPath => $nodeList)
		{
			if($nodeList->length >0)
			{
				foreach($nodeList as $node)
				{
					$urls[$documentPath][] = $node->getAttribute('href');
				}
			}
			else{
				$this->errors[] = array($documentPath => 'Aucun lien trouvé');
			}
		}

		return $urls;
	}


	public function getResourceFilesContent($regex_extension, $max_depth = 2)
	{
		return $this->getFiles($regex_extension, $max_depth);
	}

	//todo à remplacer par getResourceFilesContent
	public function getFiles($regex_extension, $max_depth = 2)
	{
		$files = array();			

		if($this->isHtml)
		{
			$files[$this->path] = file_get_contents($this->path);
		}
		elseif($this->isDir)
		{
	 		$filtered = $this->getFileRecursively($this->path,$regex_extension, $max_depth);

			foreach ($filtered as $index => $file) 
			{
				$files[$file[0]] = file_get_contents($file[0]);	
				//max file @todo -> config
				if($index > 20) break;
			}			
		}
		elseif($this->isArchive)
		{
			$tmp_path = $this->createTmpDir();
		    $zip = new \ZipArchive();
            if ($zip->open($this->path) === TRUE) {
                $zip->extractTo($tmp_path);
                $zip->close();
            } 
            else {
                //@todo log
            }			
	 		$filtered = $this->getFileRecursively($tmp_path,$regex_extension, $max_depth);

			foreach ($filtered as $index => $file) 
			{
				$files[$file[0]] = file_get_contents($file[0]);	
				//max file @todo -> config
				if($index > 20) break;
			}
			$this->removeTmpDir($tmp_path);
		}

		
		return $files;

	}

	protected function getFileRecursively($path, $regex_extension, $max_depth = 2)
	{
		$results = array();
		$dir = new \RecursiveDirectoryIterator($path);
		$it = new \RecursiveIteratorIterator($dir);

			//max Depth @todo -> config
		$it->setMaxDepth($max_depth);

		$filtered = new \RegexIterator($it, '/^.+\.html?$/i', \RecursiveRegexIterator::GET_MATCH);	

		return $filtered;	
	}

	protected function GetNodeList($path)
	{

		libxml_use_internal_errors(true);
		
		$doc = new \DOMDocument();

		if(!$doc->loadHTMLFile($path))
		{
			$errors = array();

			foreach (libxml_get_errors() as $error) {
				$array[] = $error; 
			}

			libxml_clear_errors();
			return $errors();
		}
		else
		{
			return $doc->getElementsByTagName('a');
		}

	}

	public function deepGetNodeList($path)
	{
		$results = array();			
		$filtered = $this->getFileRecursively($path, 'html?');

		foreach ($filtered as $index => $file) 
		{
			$results[$file[0]] = $this->getNodeList($file[0]);	
					//max file @todo -> config
			if($index > 20) break;
		}

		return $results;
	}

    public function createTmpDir()
    {
        $fs = $this->getFilesystem();
        $tmp = tempnam(sys_get_temp_dir(), $this->ext);
        if ($fs->exists($tmp)) $fs->remove($tmp); 
        $fs->mkdir($tmp);
        
        return $tmp;
    }

    public function removeTmpDir($path)
    {
        $fs = $this->getFilesystem();
        $fs->remove($path);
    }

    protected function getFilesystem()
    {
        if(empty($this->fs)){
            $this->fs = new \Symfony\Component\Filesystem\Filesystem();
        }

        return $this->fs;
    }

}