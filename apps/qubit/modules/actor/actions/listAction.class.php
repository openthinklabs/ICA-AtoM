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
 * Show paginated list of actors.
 *
 * @package    qubit
 * @subpackage actor
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     David Juhasz <david@artefactual.com>
 * @version    svn:$Id: listAction.class.php 10314 2011-11-14 20:23:01Z david $
 */
class ActorListAction extends sfAction
{
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    if (isset($request->subquery))
    {
      try
      {
        // Parse query string
        $query = QubitSearch::getInstance()->parse($request->subquery);
      }
      catch (Exception $e)
      {
        $this->error = $e->getMessage();

        return;
      }
    }
    else
    {
      $this->redirect(array('module' => 'actor', 'action' => 'browse'));
    }

    $query->addSubquery(QubitSearch::getInstance()->addTerm('QubitActor', 'className'), true);
    $query = QubitAcl::searchFilterByResource($query, QubitActor::getById(QubitActor::ROOT_ID));

    $this->pager = new QubitArrayPager;

    try
    {
      $this->pager->hits = QubitSearch::getInstance()->getEngine()->getIndex()->find($query);
    }
    catch (Exception $e)
    {
      $this->error = $e->getMessage();

      return;
    }

    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage($request->page);

    $ids = array();
    foreach ($this->pager->getResults() as $hit)
    {
      $ids[] = $hit->getDocument()->id;
    }

    $criteria = new Criteria;
    $criteria->add(QubitActor::ID, $ids, Criteria::IN);

    $this->actors = QubitActor::get($criteria);
  }
}
