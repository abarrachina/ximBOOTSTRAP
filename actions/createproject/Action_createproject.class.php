<?php

/**
 *  \details &copy; 2011  Open Ximdex Evolution SL [http://www.ximdex.org]
 *
 *  Ximdex a Semantic Content Management System (CMS)
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published
 *  by the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  See the Affero GNU General Public License for more details.
 *  You should have received a copy of the Affero GNU General Public License
 *  version 3 along with Ximdex (see LICENSE file).
 *
 *  If not, visit http://gnu.org/licenses/agpl-3.0.html.
 *
 *  @author Ximdex DevTeam <dev@ximdex.com>
 *  @version $Revision$
 */
ModulesManager::file('/inc/modules/Module.class.php');
ModulesManager::file('/inc/cli/CliParser.class.php');
ModulesManager::file('/inc/cli/CliReader.class.php');
ModulesManager::file('/inc/io/BaseIO.class.php');
ModulesManager::file('/inc/fsutils/FsUtils.class.php');
ModulesManager::file(MODULE_XIMBOOTSTRAP_PATH . '/BuildParser.class.php');
ModulesManager::file('/inc/helper/DebugLog.class.php');
ModulesManager::file('/actions/addximlet/Action_addximlet.class.php');

class Action_createproject extends ActionAbstract {

    private $project = null;
    public $name ="Bootstrap";

    /**
     * Main function, first step in the creation project process
     * * */
    public function index() {

        $values = array(
            "go_method" => "createproject"
        );
        $template = "index";
        $jsFolder = "/modules/ximBOOTSTRAP/actions/createproject/resources/js/";
        $cssFolder = "/modules/ximBOOTSTRAP/actions/createproject/resources/css/";
        $this->addJs($jsFolder . "init.js");
        $this->addJs($jsFolder . "colorpicker.js");

        $this->addCss($cssFolder . "colorpicker.css");

        $this->render($values, $template, 'default-3.0.tpl');
    }

    public function createproject() {

        //Creating project
        $nodeID = $this->request->getParam("nodeid");
        $name = $this->request->getParam("name");

        $nodeType = $this->GetTypeOfNewNode($nodeID);
        $nodeTypeName = $nodeType["name"];

        $nodeType = new NodeType();
        $nodeType->SetByName($nodeTypeName);

        $buildFile = sprintf('%s/../../project/build.xml', dirname(__FILE__));
        $b = new BuildParser($buildFile);
        $this->project = $b->getProject();

        //Replacing config element from form values
        $this->changeXimletValues();


        //Creating project
         $data = array(
          'NODETYPENAME' => $nodeTypeName,
          'NAME' => $name,
          'NODETYPE' => $nodeType->GetID(),
          'PARENTID' => 10000
          );

          $io = new BaseIO();
          $projectId = $io->build($data);
          if ($projectId < 1) {
          return false;
          }


          $project = new Node($projectId);
          $this->project->projectid = $projectId;

          $channel = $this->project->channel;
          $channel = $channel == '{?}' ? $this->getChannel() : $channel;
          $this->project->channel = $channel;

          $lang = $this->project->language;
          $lang = $lang == '{?}' ? $this->getLanguage() : $lang;
          $this->project->language = $lang;

          $project->setProperty('Transformer', $this->project->Transformer);
          $project->setProperty('channel', $this->project->channel);
          $project->setProperty('language', $this->project->lang);

          ModulesManager::log(ModulesManager::SUCCESS, "Project creation O.K.");


          // TODO: ximlink
          $links = $this->project->getXimlink();
          $this->templates = $this->insertFiles($this->project->projectid, 'ximlink', $links);


          // RNGs
          $pvds = $this->project->getPVD('RNG');
          $this->templates = $this->insertFiles($this->project->projectid, 'ximpvd', $pvds);


          // Update XSL

          $xsls = $this->project->getPTD('XSL');
          $ret = $this->insertFiles($this->project->projectid, 'xìmptd', $xsls);

          // Servers
          $servers = $this->project->getServers();
          foreach ($servers as $server) {
          $this->insertServer($server);
          }


          return $projectId;
    }

    //Get Possible Channel by default. Giving priority to html or web channel.
    function getChannel() {
        $channels = Channel::GetAllChannels();
        if (is_null($channels)) {
            return false;
        }
        $channelId = false;
        foreach ($channels as $id_channel) {
            $channel = new Channel($id_channel);
            if ($channel->GetName() == "html" or $channel->GetName() == "web") {
                $channelId = $id_channel;
                break;
            }
        }
        if (!$channelId)
            $channelId = $channels[0];
        ModulesManager::log(ModulesManager::SUCCESS, "Using channel with ID " . $channelId);
        return $channelId;
    }

    function getLanguage() {
        $language = new Language();
        $langs = $language->GetList();
        if (is_null($langs)) {
            return false;
        }
        $langId = $langs[0];
        ModulesManager::log(ModulesManager::SUCCESS, "Using language with ID " . $langId);
        return $langId;
    }

    function insertServer($server) {

        $nodeType = new NodeType();
        $nodeType->SetByName($server->nodetypename);
        $idNodeType = ($nodeType->get('IdNodeType') > 0) ? $nodeType->get('IdNodeType') : NULL;

        $data = array(
            'NODETYPENAME' => $server->nodetypename,
            'NAME' => $server->name,
            'NODETYPE' => $idNodeType,
            'PARENTID' => $this->project->projectid
        );

        $io = new BaseIO();
        $serverId = $io->build($data);
        if ($serverId < 1) {
            return false;
        }

        $server->serverid = $serverId;
        $server->url = preg_replace('/\{URL_ROOT\}/', Config::GetValue('UrlRoot'), $server->url);
        $server->initialDirectory = preg_replace('/\{XIMDEX_ROOT_PATH\}/', XIMDEX_ROOT_PATH, $server->initialDirectory);

        $nodeServer = new Node($serverId);
        $physicalServerId = $nodeServer->class->AddPhysicalServer(
                $server->protocol, $server->login, $server->password, $server->host, $server->port, $server->url, $server->initialDirectory, $server->overrideLocalPaths, $server->enabled, $server->previsual, $server->description, $server->isServerOTF
        );

        $nodeServer->class->AddChannel($physicalServerId, $this->project->channel);
        ModulesManager::log(ModulesManager::SUCCESS, "Server creation O.K.");


        // common
        $commonFolderId = $nodeServer->GetChildByName("common");
        $newFolderNodeType = new NodeType();
        $newFolderNodeType->SetByName("CommonFolder");
        $folder = new Node();
        $idFolder = $folder->CreateNode("bootstrap", $commonFolderId, $newFolderNodeType->GetID(), null);
        $common = $server->getCommon("/bootstrap");
        $ret = $this->insertFiles($commonFolderId, 'bootstrap', $common);

         //images
        $imageFolderId = $nodeServer->GetChildByName("images");
        $newFolderNodeType = new NodeType();
        $newFolderNodeType->SetByName("ImagesFolder");
        $folder = new Node();
        $idFolder = $folder->CreateNode("bootstrap", $imageFolderId, $newFolderNodeType->GetID(), null);

        $img = $server->getImages("/bootstrap");
        $ret = $this->insertFiles($imageFolderId, 'bootstrap', $img);

        // CSSs
        $cssFolderId = $nodeServer->GetChildByName("css");
        $newFolderNodeType = new NodeType();
        $newFolderNodeType->SetByName("CssFolder");
        $folder = new Node();
        $idFolder = $folder->CreateNode("bootstrap", $cssFolderId, $newFolderNodeType->GetID(), null);

        $css = $server->getCSS("/bootstrap");
        $ret = $this->insertFiles($cssFolderId, 'bootstrap', $css);


        // ximlet
        $let = $server->getXimlet();
        $ret = $this->insertDocs($server->serverid, $let, true);

        // ximdoc
       /* $docs = $server->getXimdocs();
        $ret = $this->insertDocs($server->serverid, $docs);
*/
        return $serverId;
    }

    function insertDocs($parentId, $files,$isXimlet=false) {

        if ($isXimlet){
            $xFolderName = 'ximlet';
            $nodeTypeName = 'XIMLET';
            $nodeTypeContainer = "XIMLETCONTAINER";
        }else{
            $xFolderName = 'ximdoc';
            $nodeTypeName = 'XMLDOCUMENT';
            $nodeTypeContainer = "XMLCONTAINER";
        }
        $ret = array();
        if (count($files) == 0)
            return $ret;

        $project = new Node($parentId);
        $xFolderId = $project->GetChildByName($xFolderName);

        if (empty($xFolderId)) {
            ModulesManager::log(ModulesManager::ERROR, $xFolderName . ' folder not found');
            return false;
        }

        $nodeType = new NodeType();
        $nodeType->SetByName($nodeTypeName);
        $idNodeType = $nodeType->get('IdNodeType') > 0 ? $nodeType->get('IdNodeType') : NULL;

        $io = new BaseIO();

        $title = $this->request->getParam("title");
        $principalColor = $this->request->getParam("principal_color");
        $secundaryColor = $this->request->getParam("secundary_color");
        $fontColor = $this->request->getParam("font_color");



        foreach ($files as $file) {
            $templateId = $this->templates[$file->templatename];
            $file->channel = $file->channel == '{?}' ? $this->project->channel : $file->channel;
            $file->language = $file->language == '{?}' ? $this->project->language : $file->language;

            $data = array(
                'NODETYPENAME' => $nodeTypeContainer,
                'NAME' => $file->name,
                'PARENTID' => $xFolderId,
                'CHILDRENS' => array(
                    array(
                        'NODETYPENAME' => 'VISUALTEMPLATE',
                        'ID' => $templateId
                    )
                )
            );

            $containerId = $io->build($data);

            if (!($containerId > 0)) {
                ModulesManager::log(ModulesManager::ERROR, "ximdoc container " . $file->name . " couldn't be created ($containerId)");
                continue;
            }

            $data = array(
                'NODETYPENAME' => $nodeTypeName,
                'NAME' => $file->name,
                'NODETYPE' => $idNodeType,
                'PARENTID' => $containerId,
                'CHILDRENS' => array(
                    array('NODETYPENAME' => 'VISUALTEMPLATE', 'ID' => $templateId),
                    array('NODETYPENAME' => 'CHANNEL', 'ID' => $file->channel),
                    array('NODETYPENAME' => 'LANGUAGE', 'ID' => $file->language),
                    array('NODETYPENAME' => 'PATH', 'SRC' => $file->getPath())
                )
            );

            $docId = $io->build($data);
            if ($docId > 0) {
                $docNode = new Node($docId);
                $content = $docNode->GetContent();

                $domDoc = new DOMDocument();
                $domDoc->preserveWhiteSpace = false;
        		$domDoc->validateOnParse = true;
                $domDoc->formatOutput = true;
        		$domDoc->loadXML($content);
        		$xpathObj = new DOMXPath($domDoc);
                $nodeList0 = $xpathObj->query('/config');
                $nodeConf = $nodeList0->item(0);
                $nodeConf->setAttribute("font-color",$fontColor);
                $nodeConf->setAttribute("background-color",$principalColor);
                $nodeConf->setAttribute("secundary-color","$secundaryColor");

                $nodeList0 = $xpathObj->query('/config/config-header/config-header-title');
                $nodeTitle = $nodeList0->item(0);
                $nodeTitle->nodeValue=$title;
                $content = $domDoc->saveXML();
                $content = str_replace('<?xml version="1.0"?>', '', $content);
                $docNode->SetContent($content);

                $ret[$file->filename] = $docId;
                ModulesManager::log(ModulesManager::SUCCESS, "Importing " . $file->name);
            } else {
//              debug::log($project, $file, $data);
                ModulesManager::log(ModulesManager::ERROR, "ximdoc document " . $file->name . " couldn't be created ($docId)");
            }
        }

        if ($isXimlet){
            $actionAddximlet = new Action_addximlet();
            $actionAddximlet->createRelXimletSection($parentId, $containerId, 1);
        }

        if (count($ret) == 0)
            $ret = false;
        return $ret;
    }

    function insertFiles($parentId, $xFolderName, $files) {

        $ret = array();
        if (count($files) == 0)
            return $ret;

        $project = new Node($parentId);
        $xFolderId = $project->GetChildByName($xFolderName);

        if (empty($xFolderId)) {
            ModulesManager::log(ModulesManager::ERROR, $xFolderName . ' folder not found');
            return false;
        }


        $io = new BaseIO();

        foreach ($files as $file) {

            echo ".";
            $nodeType = new NodeType();
            $nodeType->SetByName($file->nodetypename);
            $idNodeType = $nodeType->get('IdNodeType') > 0 ? $nodeType->get('IdNodeType') : NULL;


            $data = array(
                'NODETYPENAME' => $file->nodetypename,
                'NAME' => $file->basename,
                'NODETYPE' => $idNodeType,
                'PARENTID' => $xFolderId,
                'CHILDRENS' => array(
                    array(
                        'NODETYPENAME' => 'PATH',
                        'SRC' => $file->path
                    )
                )
            );

            $id = $io->build($data);

            //Updating url_path for docxap node
            if ($file->basename == "docxap.xsl") {

                $newNode = new Node($id);
                $docxapContent = $newNode->GetContent();
                $urlPath = Config::GetValue("UrlRoot");
                $docxapContent = str_replace("{URL_PATH}", $urlPath, $docxapContent);
                $newNode->SetContent($docxapContent);

            }

            if ($id > 0) {
                $ret[$file->filename] = $id;
                ModulesManager::log(ModulesManager::SUCCESS, "Importing " . $file->basename);
            } else {
                ModulesManager::log(ModulesManager::ERROR, "Error ($id) importing " . $file->basename);
                ModulesManager::log(ModulesManager::ERROR, print_r($io->messages->messages, true));
            }
        }

        if (count($ret) == 0)
            $ret = false;
        return $ret;
    }

    function updateXsl($parentId, $files) {

        if (count($files) == 0)
            return false;

        $project = new Node($parentId);
        $ptdFolderId = $project->GetChildByName('ximptd');

        $nodePtds = new Node($ptdFolderId);
        if (empty($ptdFolderId)) {
            ModulesManager::log(ModulesManager::ERROR, 'Ptd folder not found');
            return false;
        }

        $nodeType = new NodeType();
        $nodeType->SetByName('XSLTEMPLATE');
        $idNodeType = ($nodeType->get('IdNodeType') > 0) ? $nodeType->get('IdNodeType') : NULL;


        $node = new Node($ptdFolderId);
        $io = new BaseIO();

        $ximdexUrl = Config::getValue('UrlRoot');
        $projectUrl = Config::getValue('UrlRoot') . '/data/nodes/' . $this->projectName;
        $servers = $this->project->getServers();
        $serverUrl = $projectUrl . '/' . $servers[0]->name;

        foreach ($files as $file) {
            echo ".";
            $content = $file->getContent();

            if (preg_match('/\{URL_ROOT\}/', $content)) {
                $content = preg_replace('/\{URL_ROOT\}/', $ximdexUrl, $content);
            }
            if (preg_match('/\{URL_PROJECT\}/', $content)) {
                $content = preg_replace('/\{URL_PROJECT\}/', $projectUrl, $content);
            }
            if (preg_match('/\{URL_SERVER\}/', $content)) {
                $content = preg_replace('/\{URL_SERVER\}/', $serverUrl, $content);
            }

            $children = $nodePtds->GetChildByName($file->basename);
            $ch = new Node($children);
            if (!($ch->get('IdNode') > 0)) {
                ModulesManager::log(ModulesManager::ERROR, "Updated xsl not O.K. Cannot find the file " . $file->basename);
                continue;
            }

            $result = $ch->setContent($content);

            if (!$result) {
                ModulesManager::log(ModulesManager::SUCCESS, "Updated xsl O.K. " . $file->basename);
            } else {
                ModulesManager::log(ModulesManager::ERROR, "Updated xsl not O.K. " . $file->basename);
            }
        }
    }

    //Change the default values from form values.
    private function changeXimletValues() {

    }

    public function GetTypeOfNewNode($nodeID) {

        $node = new Node($nodeID);
        if (!$node->get('IdNode') > 0) {
            return null;
        }
        $nodeTypeName = $node->nodeType->GetName();

        switch ($nodeTypeName) {
            case "Projects":
                $newNodeTypeName = "Project";
                $friendlyName = "Project";
                break;


            case "Project":
                $newNodeTypeName = "Server";
                $friendlyName = "Server";
                break;
        }
        $result = array();
        $result["name"] = $newNodeTypeName;
        $result["friendlyName"]=$friendlyName;
        $resukt["idNodeType"]=$node->GetNodeType();
        return $result;
    }

}

?>
