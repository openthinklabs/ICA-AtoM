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

class QubitMeta extends sfFilter
{
  public function execute($filterChain)
  {
    $context = $this->getContext();

    $context->response->addMeta('title', sfConfig::get('app_siteTitle'));
    $context->response->addMeta('description', sfConfig::get('app_siteDescription'));

    foreach (array('actor_template', 'informationobject_template', 'repository_template') as $name)
    {
      if (isset($context->request[$name]))
      {
        $context->routing->setDefaultParameter($name, $context->request[$name]);
      }
      else
      {
        $context->routing->setDefaultParameter($name, sfConfig::get('app_default_template_'.substr($name, 0, -9)));
      }
    }

    $filterChain->execute();
  }
}
