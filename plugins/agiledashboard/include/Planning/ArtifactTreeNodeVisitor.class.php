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

class Planning_ArtifactTreeNodeVisitor {
    
    /**
     * @var string the css class name
     */
    private $classname;
    
    /**
     * @var string
     */
    private $current_url;
    
    /**
     * @var Tracker_ArtifactFactory
     */
    private $artifact_factory;
    
    /**
     * @var Tracker_Hierarchy_HierarchicalTrackerFactory
     */
    private $hierarchy_factory;
    
    public function __construct(Tracker_ArtifactFactory $artifact_factory, Tracker_Hierarchy_HierarchicalTrackerFactory $hierarchy_factory, $classname, $current_url) {
        $this->artifact_factory  = $artifact_factory;
        $this->classname         = $classname;
        $this->current_url       = $current_url;
        $this->hierarchy_factory = $hierarchy_factory;
    }
    
    /**
     * @param string $classname The css classname to inject in TreeNode
     *
     * @return Planning_ArtifactTreeNodeVisitor
     */
    public static function build($classname, $current_url) {
        $artifact_factory  = Tracker_ArtifactFactory::instance();
        $hierarchy_factory = Tracker_Hierarchy_HierarchicalTrackerFactory::instance();
        return new Planning_ArtifactTreeNodeVisitor($artifact_factory, $hierarchy_factory, $classname, $current_url);
    }
    
    private function injectArtifactInChildren(TreeNode $node) {
        foreach ($node->getChildren() as $child) {
            $child->accept($this);
        }
    }

    public function visit(TreeNode $node) {
        $row = $node->getData();
        $artifact = $this->artifact_factory->getArtifactById($row['id']);
        if ($artifact) {
            $row['artifact_id']          = $artifact->getId();
            $row['title']                = $artifact->getTitle();
            $row['class']                = $this->classname;
            $row['uri']                  = $artifact->getUri();
            $row['xref']                 = $artifact->getXRef();
            $row['editLabel']            = $GLOBALS['Language']->getText('plugin_agiledashboard', 'edit_item');
            $row['return_to']            = urlencode($this->current_url);
            if (!isset($row['allowedChildrenTypes'])) {
                $row['allowedChildrenTypes'] = $this->hierarchy_factory->getChildren($artifact->getTracker());
            }
            $node->setData($row);
        }
        $this->injectArtifactInChildren($node);
    }
}

?>