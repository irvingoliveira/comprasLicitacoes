<?php

/*
 * Copyright (C) 2014 Irving Fernando de Medeiros Oliveira
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Http\Request;

class MenuAtivo extends AbstractHelper
{
    protected $request;
 
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
 
    public function __invoke($url_menu = '')
    {
        return $this->request->getUri()->getPath() == $url_menu ? 'class="active"' : '';
    }
}