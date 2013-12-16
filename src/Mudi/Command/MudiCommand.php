<?php

namespace Mudi\Command;

use Symfony\Component\Console;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * Base class for Cilex commands.
 *
 * @author Mike van Riel <mike.vanriel@naenius.com>
 *
 * @api
 */
abstract class MudiCommand extends Console\Command\Command
{

    protected $curentResource;
    protected $fs;

    public function __construct()
    {
        parent::__construct();
        $this->resource = new \Mudi\Resource();
    }


    protected function checkResource($name)
    {

        $this->resource->name = $name;
        $this->resource->path = $this->resource->getPathFromName($name);

        if(is_file($this->resource->path))
        {

            $ext = end(explode('.', $name));
            $this->resource->isFile = true;
            $this->resource->ext = $ext;

            if(!in_array($ext, $this->resource->authorizedExtensions))
            {
                throw new Exception('Le type de fichier est invalide');    
            }

            switch($ext)
            {
                case 'htm':
                case 'html':
                    $this->resource->isHtml = true;
                    break;
                case 'zip':
                    $this->resource->isArchive = true;
                    $this->resource->isZip = true;
                    break;
            }

        }
        elseif(is_dir($this->resource->path)){
            $this->resource->isDir = true;
        }
        else 
        {
            throw new \Exception(sprintf('La ressource spécifiée est invalide : %s', $name));
        }
    }


    protected function createTmpDir($resource)
    {

        $fs = $this->getFilesystem();
        $tmp = tempnam(sys_get_temp_dir(), $resource->ext);
        if ($fs->exists($tmp)) $fs->remove($tmp); 
        $fs->mkdir($tmp);
        if (is_dir($tmp)) 
        { 
            $zip = new \ZipArchive();
                if ($zip->open($resource->path) === TRUE) {
                    $zip->extractTo($tmp);
                    $zip->close();
                } 
                else {
                    //@todo log
                }
        }
      
        return $tmp;
    }

    protected function removeTmpDir($path)
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