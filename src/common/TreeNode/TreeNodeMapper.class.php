<?php

/**
 * Copyright (c) Enalean, 2012. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'TreeNodeCallback.class.php';

/**
 * Like array_map this produces a new node tree by calling $callback on every node in the current tree
 */
class TreeNodeMapper {

    /** @var TreeNodeCallback */
    private $function;
    
    public function __construct(TreeNodeCallback $function) {
        $this->function = $function;
    }
    
    public function visit(TreeNode $node) {
        $new_node = $this->function->apply($node);
        $new_node->setChildren($this->applyToChildren($node));
        return $new_node;
    }

    private function applyToChildren(TreeNode $node) {
        $children = array();
        foreach ($node->getChildren() as $child) {
            $children[] = $child->accept($this);
        }
        return $children;
    }
}

?>
