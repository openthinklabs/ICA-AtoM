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
 * Information Object - editIsad
 *
 * @package    qubit
 * @subpackage informationObject - initialize an editIsad template for updating an information object
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jesús García Crespo <correo@sevein.com>
 * @version    SVN: $Id: editAction.class.php 12129 2012-08-17 17:19:59Z david $
 */
class sfIsadPluginEditAction extends InformationObjectEditAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'accessConditions',
      'accruals',
      'acquisition',
      'appraisal',
      'archivalHistory',
      'arrangement',
      'creators',
      'descriptionDetail',
      'descriptionIdentifier',
      'extentAndMedium',
      'findingAids',
      'identifier',
      'institutionResponsibleIdentifier',
      'language',
      'languageNotes',
      'languageOfDescription',
      'levelOfDescription',
      'locationOfCopies',
      'locationOfOriginals',
      'nameAccessPoints',
      'physicalCharacteristics',
      'placeAccessPoints',
      'relatedUnitsOfDescription',
      'repository',
      'reproductionConditions',
      'revisionHistory',
      'rules',
      'scopeAndContent',
      'scriptOfDescription',
      'script',
      'sources',
      'subjectAccessPoints',
      'descriptionStatus',
      'publicationStatus',
      'title');

  protected function earlyExecute()
  {
    parent::earlyExecute();

    $this->isad = new sfIsadPlugin($this->resource);

    $title = $this->context->i18n->__('Add new archival description');
    if (isset($this->getRoute()->resource))
    {
      if (1 > strlen($title = $this->resource->__toString()))
      {
        $title = $this->context->i18n->__('Untitled');
      }

      $title = $this->context->i18n->__('Edit %1%', array('%1%' => $title));
    }

    $this->response->setTitle("$title - {$this->response->getTitle()}");

    $this->eventComponent = new sfIsadPluginEventComponent($this->context, 'sfIsadPlugin', 'event');
    $this->eventComponent->resource = $this->resource;
    $this->eventComponent->execute($this->request);

    $this->rightEditComponent = new RightEditComponent($this->context, 'right', 'edit');
    $this->rightEditComponent->resource = $this->resource;
    $this->rightEditComponent->execute($this->request);
  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'creators':
        $criteria = new Criteria;
        $this->resource->addEventsCriteria($criteria);
        $criteria->add(QubitEvent::ACTOR_ID, null, Criteria::ISNOTNULL);
        $criteria->add(QubitEvent::TYPE_ID, QubitTerm::CREATION_ID);

        $value = $choices = array();
        foreach ($this->events = QubitEvent::get($criteria) as $item)
        {
          $choices[$value[] = $this->context->routing->generate(null, array($item->actor, 'module' => 'actor'))] = $item->actor;
        }

        $this->form->setDefault('creators', $value);
        $this->form->setValidator('creators', new sfValidatorPass);
        $this->form->setWidget('creators', new sfWidgetFormSelect(array('choices' => $choices, 'multiple' => true)));

        break;

      case 'appraisal':
        $this->form->setDefault('appraisal', $this->resource['appraisal']);
        $this->form->setValidator('appraisal', new sfValidatorString);
        $this->form->setWidget('appraisal', new sfWidgetFormTextarea);

        break;

      case 'languageNotes':

        $this->form->setDefault('languageNotes', $this->isad['languageNotes']);
        $this->form->setValidator('languageNotes', new sfValidatorString);
        $this->form->setWidget('languageNotes', new sfWidgetFormTextarea);

        break;

      default:

        return parent::addField($name);
    }
  }

  protected function processField($field)
  {
    switch ($field->getName())
    {
      case 'creators':
        $value = $filtered = array();
        foreach ($this->form->getValue('creators') as $item)
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($item));
          $resource = $params['_sf_route']->resource;
          $value[$resource->id] = $filtered[$resource->id] = $resource;
        }

        foreach ($this->events as $item)
        {
          if (isset($value[$item->actor->id]))
          {
            unset($filtered[$item->actor->id]);
          }
          else if (!isset($this->request->sourceId))
          {
            $item->delete();
          }
        }

        foreach ($filtered as $item)
        {
          $event = new QubitEvent;
          $event->actor = $item;
          $event->typeId = QubitTerm::CREATION_ID;

          $this->resource->events[] = $event;
        }

        break;

      case 'languageNotes':

        $this->isad['languageNotes'] = $this->form->getValue('languageNotes');

        break;

      default:

        return parent::processField($field);
    }
  }

  protected function processForm()
  {
    $this->resource->sourceStandard = 'ISAD(G) 2nd edition';

    $this->eventComponent->processForm();

    $this->rightEditComponent->processForm();

    $this->updateNotes();

    return parent::processForm();
  }

  /**
   * Update ISAD notes
   *
   * @param QubitInformationObject $informationObject
   */
  protected function updateNotes()
  {
    if ($this->request->hasParameter('csvimport'))
    {
      // remap notes from parameters to request object
      if ($this->request->getParameterHolder()->has('newArchivistNote'))
      {
        $this->request->new_archivist_note = $this->request->getParameterHolder()->get('newArchivistNote');
      }

      if ($this->request->getParameterHolder()->has('newPublicationNote'))
      {
        $this->request->new_publication_note = $this->request->getParameterHolder()->get('newPublicationNote');
      }

      if ($this->request->getParameterHolder()->has('newNote'))
      {
        $this->request->new_note = $this->request->getParameterHolder()->get('newNote');
      }
    }

    // Update archivist's notes (multiple)
    foreach ((array) $this->request->new_archivist_note as $content)
    {
      if (0 < strlen($content))
      {
        $note = new QubitNote;
        $note->content = $content;
        $note->typeId = QubitTerm::ARCHIVIST_NOTE_ID;
        $note->userId = $this->context->user->getAttribute('user_id');

        $this->resource->notes[] = $note;
      }
    }

    // Update publication notes (multiple)
    foreach ((array) $this->request->new_publication_note as $content)
    {
      if (0 < strlen($content))
      {
        $note = new QubitNote;
        $note->content = $content;
        $note->typeId = QubitTerm::PUBLICATION_NOTE_ID;
        $note->userId = $this->context->user->getAttribute('user_id');

        $this->resource->notes[] = $note;
      }
    }

    // Update general notes (multiple)
    foreach ((array) $this->request->new_note as $content)
    {
      if (0 < strlen($content))
      {
        $note = new QubitNote;
        $note->content = $content;
        $note->typeId = QubitTerm::GENERAL_NOTE_ID;
        $note->userId = $this->context->user->getAttribute('user_id');

        $this->resource->notes[] = $note;
      }
    }
  }
}
