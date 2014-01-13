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

}