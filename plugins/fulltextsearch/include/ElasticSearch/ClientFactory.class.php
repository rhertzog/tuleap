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

/**
 * Builds ElasticSearch_ClientFacade instances
 */
class ElasticSearch_ClientFactory {

    /**
     * Build instance of ClientFacade
     *
     * @param string $path_to_elasticsearch_client /usr/share/elasticsearch
     * @param string the host of the search server
     * @param string the port of the search server
     *
     * @return ElasticSearch_ClientFacade
     */
    public function buildIndexClient($path_to_elasticsearch_client, $server_host, $server_port) {
        $client = $this->getClient($path_to_elasticsearch_client, $server_host, $server_port);
        require_once 'IndexClientFacade.class.php';
        return new ElasticSearch_IndexClientFacade($client);
    }
    
    /**
     * Build instance of ClientFacade
     *
     * @param string $path_to_elasticsearch_client /usr/share/elasticsearch
     * @param string the host of the search server
     * @param string the port of the search server
     *
     * @return ElasticSearch_ClientFacade
     */
    public function buildSearchClient($path_to_elasticsearch_client, $server_host, $server_port, ProjectManager $project_manager) {
        $type   = 'docman';
        $client = $this->getClient($path_to_elasticsearch_client, $server_host, $server_port, $type);
        require_once 'SearchClientFacade.class.php';
        return new ElasticSearch_SearchClientFacade($client, $type, $project_manager);
    }
    
    private function getClient($path_to_elasticsearch_client, $server_host, $server_port, $type='docman') {
        //todo use installation dir defined by elasticsearch rpm
        $client_path = $path_to_elasticsearch_client .'/ElasticSearchClient.php';
        if (! file_exists($client_path)) {
            $error_message = $GLOBALS['Language']->getText('plugin_fulltextsearch', 'client_library_not_found');
            $GLOBALS['Response']->addFeedback('error', $error_message);
            $GLOBALS['HTML']->redirect('/docman/?group_id=' . $this->getId());
            die();
        }

        require_once $client_path;

        $transport  = new ElasticSearchTransportHTTP($server_host, $server_port);        
        return new ElasticSearchClient($transport, 'tuleap', $type);
    }
}
?>
