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
 * along with Codendi; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * 
 */


/**
* System Event classes
*
*/
class SystemEvent_SYSTEM_CHECK extends SystemEvent {


    /**
     * Constructor
     * @param $id        : SystemEvent DB ID
     * @param $parameters: Event Parameter 
     * @param $priority  : Event priority
     * @param $status    : Event status
     */
    function __construct($id, $parameters, $priority, $status ) {
        $this->id        = $id;
        $this->type      = SystemEvent::SYSTEM_CHECK;
        $this->parameters= $parameters;
        $this->priority  = $priority;
        $this->status    = $status;
    }



    /** 
     * Process stored event
     */
    function process() {

        $backendSystem      = BackendSystem::instance();
        $backendAliases     = BackendAliases::instance();
        $backendSVN         = BackendSVN::instance();
        $backendCVS         = BackendCVS::instance();
        $backendMailingList = BackendMailingList::instance();

	//TODO: SSH keys ssh_create.pl
	// remove deleted releases and released files
	//cd $CODEX_UTILS_PREFIX/download
	//./download_filemaint.pl
	// User: unix_status vs status??
	// Private project: if codeaxadm is not member of the project: check access to SVN (incl. ViewVC), CVS, Web...
	// CVS Watch?
	// TODO: log event in syslog?

        // Force global updates: aliases, CVS roots, SVN roots
        $backendCVS->setCVSRootListNeedUpdate();
        $backendSVN->setSVNApacheConfNeedUpdate();
        BackendAliases::instance()->setNeedUpdateMailAliases();

        // Check mailing lists
	// (re-)create missing ML
        $mailinglistdao = new MailingListDao(CodendiDataAccess::instance());
        $dar = $mailingListDao()->searchAllActiveML();
        foreach($dar as $row) {
          $list = new MailingList($row);
          if (!$backendMailingList->listExists($list)) {
            $backendMailingList->createList($list->getId());
          }
          // TODO what about lists that changed their setting (description, public/private) ?
        }


	// Check users
	// (re-) create missing home directories
	$userdao = new UserDao(CodendiDataAccess::instance());
        $allowed_statuses=array('A', 'R'); // Active and restricted users
        $dar = $userdao->searchByStatus($allowed_statuses);
        foreach($dar as $row) {
	    if (! $backendSystem->userHomeExists($row['user_name'])) {
	        $backendSystem->createUserHome($row['user_id']);
	    }
	}


        // Projects
        // TODO This should go to a DAO
        $sql = "SELECT * 
                FROM groups 
                WHERE status IN ('A')"; // TODO check query
        $dar = $this->retrieve($sql);
        foreach($dar as $row) {
          $project = new Project($row);

	  // Recreate project directories if they were deleted
	  if (!$backendSystem->createProjectHome($group_id)) {
	      $this->error("Could not create project home");
	      return false;
	  }

          if ($project->usesCVS()) {
              if (!$backendCVS->repositoryExists($project)) {
                  if (!$backendCVS->createProjectCVS($project->group_id)) {
                      $this->error("Could not create/initialize project CVS repository");
                      return false;
                  }
                  $backendCVS->setCVSPrivacy($project, !$project->isPublic() || $project->isCVSPrivate());
              }
	      $backendCVS->createLockDirIfMissing($project);
	      // check post-commit hooks
	      if (!$backendCVS->updatePostCommit($project)) return false;
	      $backendCVS->updateCVSwriters($project->getID());

              // Check access rights
              if (!$backendCVS->isCVSPrivacyOK($project)) {
                  $backendCVS->setCVSPrivacy($project, !$project->isPublic() || $project->isCVSPrivate());
              }
          }
            
	  if ($project->usesSVN()) {
              if (!$backendSVN->repositoryExists($project)) {
	          if (!$backendSVN->createProjectSVN($group_id)) {
		      $this->error("Could not create/initialize project SVN repository");
		      return false;
                  }
		  $backendSVN->updateHooks($project);
		  $backendSVN->updateSVNAccess($project->getID());
		  $backendSVN->setSVNPrivacy($project, !$project->isPublic() || $project->isSVNPrivate());
	      }
	      // Check access rights
              if (!$backendSVN->isSVNPrivacyOK($project)) {
                  $backendSVN->setSVNPrivacy($project, !$project->isPublic() || $project->isSVNPrivate());
              }
            }

            $backendSystem->log("Project ".$project->getUnixName()." created");            
            $this->done();
            return true;
        }
        return false;
    }

}

?>