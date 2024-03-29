<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * This class is used to provide methods that supplement the core Qubit information object with behaviour or
 * presentation features that are specific to the Metadata Object Description Schema (MODS) standard
 *
 * @package    Qubit
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @version    svn:$Id: sfModsPlugin.class.php 11781 2012-06-20 18:52:24Z david $
 */

class sfModsPlugin implements ArrayAccess
{
  protected
    $resource;

  public function __construct(QubitInformationObject $resource)
  {
    $this->resource = $resource;
  }

  public function __toString()
  {
    $string = array();

    $levelOfDescriptionAndIdentifier = array();

    if (isset($this->resource->levelOfDescription))
    {
      $levelOfDescriptionAndIdentifier[] = $this->resource->levelOfDescription->__toString();
    }

    if (isset($this->resource->identifier))
    {
      $levelOfDescriptionAndIdentifier[] = $this->resource->identifier;
    }

    if (0 < count($levelOfDescriptionAndIdentifier))
    {
      $string[] = implode($levelOfDescriptionAndIdentifier, ' ');
    }

    $resourceAndPublicationStatus = array();

    if (0 < strlen($title = $this->resource->__toString()))
    {
      $resourceAndPublicationStatus[] = $title;
    }

    $publicationStatus = $this->resource->getPublicationStatus();
    if (isset($publicationStatus) && QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $publicationStatus->statusId)
    {
      $resourceAndPublicationStatus[] = "({$publicationStatus->status->__toString()})";
    }

    if (0 < count($resourceAndPublicationStatus))
    {
      $string[] = implode($resourceAndPublicationStatus, ' ');
    }

    return implode(' - ', $string);
  }

  public function offsetExists($offset)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__isset'), $args);
  }

  public function __get($name)
  {
    switch ($name)
    {
      case 'identifier':

        return $this->resource->referenceCode;

      case 'name':
        $name = array();
        foreach ($this->resource->getActorEvents() as $item)
        {
          if (isset($item->actor))
          {
            $name[] = $item;
          }
        }

        return $name;

      case 'physicalLocation':
        $list = array();

        if (isset($this->resource->repository))
        {
          $list[] = $resource->repository->identifier;
          $list[] = $resource->repository;

          if (null !== $contact = $this->resource->repository->getPrimaryContact())
          {
            $physicalLocation = array();

            if (isset($contact->city))
            {
              $physicalLocation[] = $contact->city;
            }

            if (isset($contact->region))
            {
              $physicalLocation[] = $contact->region;
            }

            if (isset($contact->countryCode))
            {
              $physicalLocation[] = format_country($contact->countryCode);
            }

            $list[] = implode(', ', $physicalLocation);
          }
        }

        return $list;

      case 'sourceCulture':

        return $this->resource->sourceCulture;

      case 'typeOfResource':

        return $this->resource->getTermRelations(QubitTaxonomy::MODS_RESOURCE_TYPE_ID);
    }
  }

  public function offsetGet($offset)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__get'), $args);
  }

  public function offsetSet($offset, $value)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__set'), $args);
  }

  public function offsetUnset($offset)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__unset'), $args);
  }
}
