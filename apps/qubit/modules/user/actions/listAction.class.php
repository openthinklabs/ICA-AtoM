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

class UserListAction extends sfAction
{
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    if (!isset($request->filter))
    {
      $request->filter = 'onlyActive';
    }

    $criteria = new Criteria;
    $criteria->addAscendingOrderByColumn(QubitUser::USERNAME);

    switch ($request->filter)
    {
      case 'onlyInactive':
        $criteria->add(QubitUser::ACTIVE, 0);

        break;

      case 'onlyActive':
      default:
        $criteria->add(QubitUser::ACTIVE, 1); 
    }

    $this->pager = new QubitPager('QubitUser');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage($request->page);

    $this->users = $this->pager->getResults();
  }
}
