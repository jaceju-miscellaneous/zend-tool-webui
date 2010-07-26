<?php
class WebUI_Client extends Zend_Tool_Framework_Client_Abstract
{
    public function getName()
    {
        return 'all';
    }

    protected function _preDispatch()
    {
    }

    protected function _postDispatch()
    {
    }

    protected function _preInit()
    {
        $config = $this->_registry->getConfig();

        // which classes are essential to initializing Zend_Tool_Framework_Client_Console
        $classesToLoad = array(
            // 'Zend_Tool_Framework_System_Manifest',
            'Zend_Tool_Project_Provider_Manifest',
            );

        // add classes to the basic loader from the config file basicloader.classes.1 ..
        if (isset($config->basicloader) && isset($config->basicloader->classes)) {
            foreach ($config->basicloader->classes as $classKey => $className) {
                array_push($classesToLoad, $className);
            }
        }

        $loader = new Zend_Tool_Framework_Loader_BasicLoader(array(
            'classesToLoad' => $classesToLoad,
        ));
        $this->_registry->setLoader($loader);

        return;
    }

    public function handleInteractiveOutput($output)
    {
        echo $output;
    }
}

