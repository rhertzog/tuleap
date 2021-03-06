<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once('Tracker_FormElement_Field_List_BindValue.class.php');

class Tracker_FormElement_Field_List_Bind_StaticValue extends Tracker_FormElement_Field_List_BindValue {
    
    protected $label;
    protected $description;
    protected $rank;
    protected $is_hidden;
    
    public function __construct($id, $label, $description, $rank, $is_hidden) {
        parent::__construct($id);
        $this->label       = $label;
        $this->description = $description;
        $this->rank        = $rank;
        $this->is_hidden   = $is_hidden;
    }
    
    public function __toString() {      
        return $this->label ? $this->label : '';
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getLabel() {
        return $this->label;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    public function getRank() {
        return $this->rank;
    }
    
    public function isHidden() {
        return $this->is_hidden;
    }
}
?>
