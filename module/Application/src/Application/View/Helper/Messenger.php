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
use Zend\Mvc\Controller\Plugin\FlashMessenger as FlashMessenger;

class Messenger extends AbstractHelper{

    protected $flashMessenger;
    protected $message;

    public function __construct(FlashMessenger $flashMessenger)
    {
        $this->setFlashMessenger($flashMessenger);
        $this->setMessage();
    }

    public function __invoke()
    {
        return $this->renderHtml();
    }
    
    public function renderHtml()
    {
        $html = '';
        $message = $this->getMessage();
        
        if ($message) {
            $key   = key($message);
            $html .= '<div id="alert-message" class="center">';
                $html .= '<div class="'. $key . ' alert-block fade in">';
                    $html .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
                    $html .= '<span class="center">'.$message[$key].'</span>';
                $html .= '</div>';
            $html .= '</div>';
        }
        
        return $html;
    }

    public function getFlashMessenger()
    {
        return $this->flashMessenger;
    }

    public function setFlashMessenger($flashMessenger)
    {
        $this->flashMessenger = $flashMessenger;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage()
    {
        $flashMessenger = $this->getFlashMessenger();

        if ($flashMessenger->hasMessages()) {
            $message = $flashMessenger->getMessages();
            $this->message = array('alert alert-warning' => array_shift($message));
        }
        
        if ($flashMessenger->hasInfoMessages()) {
            $messageInfo = $flashMessenger->getInfoMessages();
            $this->message = array('alert alert-info' => array_shift($messageInfo));
        }

        if ($flashMessenger->hasSuccessMessages()) {
            $messageSuccess = $flashMessenger->getSuccessMessages();
            $this->message = array('alert alert-success' => array_shift($messageSuccess));
        }

        if ($flashMessenger->hasErrorMessages()) {
            $messageError = $flashMessenger->getErrorMessages();
            $this->message = array('alert alert-danger' => array_shift($messageError));
        }
    }
}
