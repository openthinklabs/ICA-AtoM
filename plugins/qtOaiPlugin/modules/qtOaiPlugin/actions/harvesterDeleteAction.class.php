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
 * Generate the OAI-PMH response
 *
 * @package    qubit
 * @subpackage oai
 * @version    svn: $Id: harvesterDeleteAction.class.php 10314 2011-11-14 20:23:01Z david $
 * @author     Mathieu Fortin Library and Archives Canada <mathieu.fortin@lac-bac.gc.ca>
 */
class qtOaiPluginHarvesterDeleteAction extends sfAction
{
   /*
   * Executes action
   *
   * @param sfRequest $request A request object
   */
  public function execute($request)
  {
    $repositoryId = $request->repositoryId;
    $repository = QubitOaiRepository::getById($repositoryId);
    $this->repositoryName = $repository->getName();
    $repository->delete();
  }
}
