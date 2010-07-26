<?php

// Define path to application directory
defined('ROOT_PATH')
    || define('ROOT_PATH', realpath(dirname(__FILE__) . '/'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(ROOT_PATH . '/library'),
    get_include_path(),
)));

require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('WebUI');

$registry = new Zend_Tool_Framework_Registry();
$client = new WebUI_Client();

$registry->setClient($client);

$httpRequest = new Zend_Controller_Request_Http();

if ($httpRequest->isPost()) {
    $request = $registry->getRequest();
    $request->setActionName('Show');
    $request->setProviderName('Phpinfo');
    $request->setSpecialtyName('_Global');
    $client->dispatch();
} else {
    $request = $registry->getRequest();
    $request->setDispatchable(false);
    $client->dispatch();

    $manifestRepository = $registry->getManifestRepository();

    $providerMetadatasSearch = array(
            'type'       => 'Tool',
            'name'       => 'normalizedProviderName',
            'clientName' => 'all'
    );

    $displayProviderMetadatas = $manifestRepository->getMetadatas($providerMetadatasSearch);

    $options = array();
    foreach ($displayProviderMetadatas as $providerMetadata) {
        $providerName = $providerMetadata->getProviderName();
        $options[$providerName] = array(
            'actions' => array(),
            'parameters' => array(),
        );
        $providerSignature = $providerMetadata->getReference();
        if ($providerSignature instanceof Zend_Tool_Framework_Provider_Signature) {
            foreach ($providerSignature->getActionableMethods() as $action) {
                $actionName = $action['actionName'];
                $methodName = $action['methodName'];
                $specialty  = $action['specialty'];
                $parameterInfo = $action['parameterInfo'];
                $actionValue = ('_Global' !== $specialty)
                             ? "$actionName.$specialty"
                             : $actionName;
                $options[$providerName]['actions'][$actionValue] = $methodName;
                $options[$providerName]['parameters'][$actionValue] = $parameterInfo;
            }
        }
    }

    $response = $registry->getResponse();

    include ROOT_PATH . '/webui.phtml';
}