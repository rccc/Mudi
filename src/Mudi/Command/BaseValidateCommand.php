<?php

namespace Mudi\Command;

use Mudi\Command\MudiCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * Base class for Cilex commands.
 *
 * @author Mike van Riel <mike.vanriel@naenius.com>
 *
 * @api
 */
abstract class BaseValidateCommand extends MudiCommand
{

    protected function validate()
    {
    }

    protected function deepValidate()
    {
    }

    protected function checkResourceAndValidate($name){

        $this->checkResource($name);

        if($this->resource->isFile)
        {
            if($this->resource->isHtml)
            {
                $this->validate($this->resource->path);
            }
            elseif($this->resource->isArchive)
            {
                if($this->resource->isZip)
                {
                    $tmp = $this->createTmpDir($this->resource);

                    if (is_dir($tmp)) 
                    { 
                        $zip = new \ZipArchive();
                            if ($zip->open($this->resource->path) === TRUE) {
                                $zip->extractTo($tmp);
                                $zip->close();
                            } 
                            else {
                                //@todo log
                            }
                    }
                    
                    $this->deepValidate($tmp);
                    $this->removeTmpDir($tmp);

                }
            }

        }
        elseif($this->resource->isDir){
            $this->deepValidate($this->resource->path); 
        }
    }

}