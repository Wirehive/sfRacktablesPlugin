<?php
/**
 * Racktables Invalid Arguments exception
 *
 * @author Robin Corps <robin@ngse.co.uk>
 * @version 0.1
 * @package sfRacktables
 */
class RacktablesInvalidArgumentsException extends sfException
{
  public function __construct($message = null, $code = null, Exception $previous = null)
  {
    if ($message === null)
    {
      $message = 'You must provide arguments as an array';
    }

    if ($code === null)
    {
      $code = 0;
    }

    parent::__construct($message, $code, $previous);
  }
}