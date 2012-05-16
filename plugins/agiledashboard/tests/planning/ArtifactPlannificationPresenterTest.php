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

require_once 'common/user/User.class.php';
require_once dirname(__FILE__).'/../../../tracker/include/constants.php';
require_once TRACKER_BASE_DIR.'/Tracker/CrossSearch/SearchContentView.class.php';
require_once TRACKER_BASE_DIR.'/../tests/builders/aMockTracker.php';
require_once dirname(__FILE__).'/../../include/Planning/Planning.class.php';
require_once dirname(__FILE__).'/../../include/Planning/ArtifactPlannificationPresenter.class.php';

class Planning_ArtifactPlanificationPresenterTest extends TuleapTestCase {
    
    protected $user;
    
    
    public function setUp() {
        parent::setUp();
        $this->user                = mock('User');
        $this->planning_tracker_id = 191;
        $this->planning_tracker    = mock('Tracker');
        $this->planning            = mock('Planning');
        $this->content_view        = mock('Tracker_CrossSearch_SearchContentView');
        $this->artifacts_to_select = array();
        $this->artifact            = null;
        
        $factory = mock('Tracker_ArtifactFactory');
        Tracker_ArtifactFactory::setInstance($factory);
        
        $this->generateABunchOfArtifacts($factory);
        
        $hierarchy_factory = mock('Tracker_Hierarchy_HierarchicalTrackerFactory');
        Tracker_Hierarchy_HierarchicalTrackerFactory::setInstance($hierarchy_factory);
        
        
        stub($this->planning)->getPlanningTrackerId()->returns($this->planning_tracker_id);
        stub($this->planning)->getPlanningTracker()->returns($this->planning_tracker);
        stub($this->planning_tracker)->getId()->returns($this->planning_tracker_id);
    }
    
    private function generateABunchOfArtifacts($factory) {
        for ($i = 30 ; $i < 40 ; ++$i) {
            $artifact = mock('Tracker_Artifact');
            stub($artifact)->getId()->returns($i);
            stub($artifact)->getTitle()->returns('Artifact '. $i);
            stub($artifact)->getUri()->returns('/bar');
            stub($artifact)->getXRef()->returns('art #'. $i);
            stub($factory)->getArtifactById($i)->returns($artifact);
        }
    }
    
    public function tearDown() {
        Tracker_ArtifactFactory::clearInstance();
        Tracker_Hierarchy_HierarchicalTrackerFactory::clearInstance();
    }
    
    protected function getAPresenter() {
        return new Planning_ArtifactPlanificationPresenter(
            $this->planning,
            $this->content_view,
            $this->artifacts_to_select,
            $this->artifact,                                                                                                                                                        
            $this->user,
            'planning['. (int)$this->planning->getId() .']='
        );
    }
    
    protected function getATreeNode($tree_node_id, $artifact_links = array(), $classname = "planning-draggable-alreadyplanned") {
        $node = new TreeNode(array(
                        'id'                   => $tree_node_id,
                        'artifact_id'          => $tree_node_id,
                        'title'                => 'Artifact '.$tree_node_id,
                        'class'                => $classname,
                        'uri'                  => '/bar',
                        'xref'                 => 'art #'. $tree_node_id,
                        'editLabel'            => null,
                        'allowedChildrenTypes' => array(),
        ));
        $node->setId($tree_node_id);
        foreach($artifact_links as $node_child) {
            $node->addChild($node_child);
        }
        return $node;
    }
    
    protected function getAnArtifact($artifact_id, $children = array()) {
        $artifact = mock('Tracker_Artifact');
        stub($artifact)->getUniqueLinkedArtifacts()->returns($children);
        stub($artifact)->getId()->returns($artifact_id);
        stub($artifact)->getTitle()->returns('Artifact '.$artifact_id);
        stub($artifact)->fetchDirectLinkToArtifact()->returns('');
        return $artifact;
    }
    
    protected function assertEqualTreeNodes($node1, $node2) {
        $this->assertEqual($node1->getData(), $node2->getData());
        $this->assertEqual($node1->getId(), $node2->getId());
        $children1 = $node1->getChildren();
        $children2 = $node2->getChildren();
        $this->assertEqual(count($children1), count($children2));
        foreach($children1 as $child_num => $child) {
            $this->assertEqualTreeNodes($child, $children2[$child_num]);
        }
    }
    
    /**
     * artifacct parent 30
     * 	- artifact 33
     * 	- artifact 34
     * 		- artifact 35
     * 	- artifact 36
     * 		- artifact 37
     * 		- artifact 38
     */
    public function itCanReturnLinkedItemsForADepthOfOne() {
        $artifact33 = $this->getAnArtifact(33);
        $artifact35 = $this->getAnArtifact(35);
        $artifact34 = $this->getAnArtifact(34, array($artifact35));
        $artifact37 = $this->getAnArtifact(37);
        $artifact38 = $this->getAnArtifact(38);
        $artifact36 = $this->getAnArtifact(36, array($artifact37, $artifact38));
        
        $this->artifact = $this->getAnArtifact(30, array($artifact33, $artifact34, $artifact36));

        
        $presenter = $this->getAPresenter();
        
        $node33 = $this->getATreeNode(33);
        $node34 = $this->getATreeNode(34, array($this->getATreeNode(35)));
        $node36 = $this->getATreeNode(36, array($this->getATreeNode(37), $this->getATreeNode(38)));
        $node_parent = $this->getATreeNode(30, array($node33, $node34, $node36));
        
        $result = $presenter->plannedArtifactsTree();
        $this->assertEqualTreeNodes($node_parent, $result);
    }
    
    /**
    * artifacct parent 30
    * 	- artifact 36
    * 		- artifact 37
    * 		- artifact 38
    * 	      - artifact 39
    */
    public function itReturnsOnlyOneLevelOnLinkedItems() {
        $artifact39 = $this->getAnArtifact(39);
        $artifact37 = $this->getAnArtifact(37);
        $artifact38 = $this->getAnArtifact(38, array($artifact39));
        $artifact36 = $this->getAnArtifact(36, array($artifact37, $artifact38));
    
        $this->artifact = $this->getAnArtifact(30, array($artifact36));
    
    
        $presenter = $this->getAPresenter();
    
        $node36 = $this->getATreeNode(36, array($this->getATreeNode(37), $this->getATreeNode(38)));
        $node_parent = $this->getATreeNode(30, array($node36));
    
        $result = $presenter->plannedArtifactsTree();
        $this->assertEqualTreeNodes($node_parent, $result);
    }
}
?>