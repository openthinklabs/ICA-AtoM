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
 * Controller for editing actor information.
 *
 * @package    qubit
 * @subpackage actor
 * @version    svn: $Id: editAction.class.php 12129 2012-08-17 17:19:59Z david $
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@artefactual.com>
 * @author     David Juhasz <david@artefactual.com>
 */
class ActorEditAction extends DefaultEditAction
{
  protected function earlyExecute()
  {
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    $this->resource = new QubitActor;

    // Make root actor the parent of new actors
    $this->resource->parentId = QubitActor::ROOT_ID;

    if (isset($this->getRoute()->resource))
    {
      $this->resource = $this->getRoute()->resource;

      // Check that this isn't the root
      if (!isset($this->resource->parent))
      {
        $this->forward404();
      }

      // Check user authorization
      if (!QubitAcl::check($this->resource, 'update') && !QubitAcl::check($this->resource, 'translate'))
      {
        QubitAcl::forwardUnauthorized();
      }

      // Add optimistic lock
      $this->form->setDefault('serialNumber', $this->resource->serialNumber);
      $this->form->setValidator('serialNumber', new sfValidatorInteger);
      $this->form->setWidget('serialNumber', new sfWidgetFormInputHidden);
    }
    else
    {
      // Check user authorization against Actor ROOT
      if (!QubitAcl::check(QubitActor::getById(QubitActor::ROOT_ID), 'create'))
      {
        QubitAcl::forwardUnauthorized();
      }
    }

    $this->form->setDefault('next', $this->request->getReferer());
    $this->form->setValidator('next', new sfValidatorString);
    $this->form->setWidget('next', new sfWidgetFormInputHidden);
  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'entityType':
        $this->form->setDefault('entityType', $this->context->routing->generate(null, array($this->resource->entityType, 'module' => 'term')));
        $this->form->setValidator('entityType', new sfValidatorString);

        $choices = array();
        $choices[null] = null;
        foreach (QubitTaxonomy::getTaxonomyTerms(QubitTaxonomy::ACTOR_ENTITY_TYPE_ID) as $item)
        {
          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item;
        }

        $this->form->setWidget('entityType', new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'authorizedFormOfName':
      case 'corporateBodyIdentifiers':
      case 'datesOfExistence':
      case 'descriptionIdentifier':
      case 'institutionResponsibleIdentifier':
        $this->form->setDefault($name, $this->resource[$name]);
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInput);

        break;

      case 'functions':
      case 'generalContext':
      case 'history':
      case 'internalStructures':
      case 'legalStatus':
      case 'mandates':
      case 'places':
      case 'revisionHistory':
      case 'rules':
      case 'sources':
        $this->form->setDefault($name, $this->resource[$name]);
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormTextarea);

        break;

      default:

        return parent::addField($name);
    }
  }

  /**
   * Process form fields
   *
   * @param $field mixed symfony form widget
   * @return void
   */
  protected function processField($field)
  {
    switch ($field->getName())
    {
      case 'entityType':
        unset($this->resource->entityType);

        $value = $this->form->getValue('entityType');
        if (isset($value))
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($value));
          $this->resource->entityType = $params['_sf_route']->resource;
        }

        break;

      default:

        return parent::processField($field);
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());
      if ($this->form->isValid())
      {
        $this->processForm();

        $this->resource->save();

        if (isset($request->id) && (0 < strlen($next = $this->form->getValue('next'))))
        {
          $this->redirect($next);
        }

        $this->redirect(array($this->resource, 'module' => 'actor'));
      }
    }
  }
}
