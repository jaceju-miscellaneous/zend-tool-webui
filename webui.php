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

$projectPath = ROOT_PATH;

if ($httpRequest->isPost()) {
    $postData = $httpRequest->getPost();

    $projectPath = realpath($postData['projectPath']);
    if (is_writable($projectPath)) {
        chdir($projectPath);
    } else {
        die("Can't change directory to '$projectPath'.");
    }
    unset($postData['projectPath']);

    $providerName = $postData['provider'];
    $actions = (array) explode('.', $postData['action']);
    $actionName = $actions[0];
    if (isset($actions[1])) {
        $specialtyName = $actions[1];
    } else {
        $specialtyName = '_Global';
    }
    unset($postData['provider']);
    unset($postData['action']);

    $request = $registry->getRequest();
    $request->setActionName($actionName);
    $request->setProviderName($providerName);
    $request->setSpecialtyName($specialtyName);
    foreach ($postData as $parameterName => $parameterValue) {
        $request->setProviderParameter($parameterName, $parameterValue);
    }

    $response = $registry->getResponse();
    $separator = new Zend_Tool_Framework_Client_Response_ContentDecorator_Separator();
    $separator->setSeparator("\n");
    $response->addContentDecorator($separator)
            ->setDefaultDecoratorOptions(array('separator' => true));

    $client->dispatch();

    if ($response->isException()) {
        echo $response->getException()->getMessage();
    } else {
        echo $response->getContent();
    }
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