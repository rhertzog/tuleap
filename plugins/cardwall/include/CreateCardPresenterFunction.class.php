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

require_once 'common/TreeNode/TreeNodeCallback.class.php';
require_once 'CardPresenter.class.php';
require_once TRACKER_BASE_DIR. '/Tracker/TreeNode/CardPresenterNode.class.php';

/**
 * Creates a CardPresenter given a TreeNode with Artifact
 */
class Cardwall_CreateCardPresenterFunction implements TreeNodeCallback {

    public function apply(TreeNode $node) {
        $artifact = $node->getObject();
        if ($artifact) {
            $presenter = new Cardwall_CardPresenter($artifact);
            $new_node  = new Tracker_TreeNode_CardPresenterNode($node, $presenter);
            return $new_node;
        }
        return clone $node;
    }
}

?>
