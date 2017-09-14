<?php
/**
Because Opcache uses a unique pool of shared memory for every SAPI, you cannot
access the webservers statistics from the console. The call has to be made
through Apache or PHP-FPM.
*/

require_once 'abstract.php';

class Mage_Shell_Opcache extends Mage_Shell_Abstract {

    /**
     * This function compiles a PHP script and adds it to the opcode cache
     * without executing it. This can be used to prime the cache after a Web
     * server restart by pre-caching files that will be included in later
     * requests.
     */
    public function compile() {
        if (FALSE === Mage::getSingleton('opcache/cache')->hasCompiler()) {
            echo sprintf("Error: %s", Mage::helper('opcache')->__('Compiler not available!'));
            die();
        }
        $result = Mage::getSingleton('opcache/cache')->compile($limit = 1);
        $msg = array();
        foreach ($result as $directory => $compiledFiles) {
            $msg[] = $directory . ': ' . $compiledFiles . ' php files';
        }
        echo sprintf("%s\n", Mage::helper('opcache')->__('Compiled files:' . PHP_EOL . '%s', implode(PHP_EOL, $msg)));
    }

    /**
     * This function invalidates a particular script from the opcode cache. If
     * force is unset or FALSE, the script will only be invalidated if the
     * modification time of the script is newer than the cached opcodes.
     */
    public function recheck() {
        $result = Mage::getSingleton('opcache/cache')->recheck();
        if ($result === TRUE) {
            echo sprintf("%s\n", Mage::helper('opcache')->__('Recheck cache successful!'));
        }
        else {
            echo sprintf("Error: %s\n", Mage::helper('opcache')->__('Recheck cache failed: ' . $result));
        }
    }

    /**
     * This function resets the entire opcode cache. After calling
     * opcache_reset(), all scripts will be reloaded and reparsed the next time
     * they are hit.
     */
    public function reset() {
        $result = Mage::getSingleton('opcache/cache')->reset();
        if ($result === TRUE) {
            echo sprintf("%s\n", Mage::helper('opcache')->__('Reset cache successful!'));
        }
        else {
            echo sprintf("Error: %s\n", Mage::helper('opcache')->__('Reset cache failed!'));
        }
    }

    /**
     * show config
     */
    public function config() {
        $config = Mage::getSingleton('opcache/cache')->getConfiguration();
        echo sprintf("%s\n", Mage::helper('opcache')->__('Compiled files:' . print_r($config, true)) );

        $blacklist =  Mage::helper('opcache')->getBlacklist();
        echo sprintf("%s\n", Mage::helper('opcache')->__('blacklist:' . print_r($blacklist, true)) );
    }

    /**
     * Run script
     *
     */
    public function run() {
        $_SESSION = array();
        if ($this->getArg('compile')) {
            $this->compile();
        }
        else if ($this->getArg('recheck')) {
            $this->recheck();
        }
        else if ($this->getArg('reset')) {
            $this->reset();
        }
        else if ($this->getArg('config')) {
            $this->config();
        }
        else {
            echo $this->usageHelp();
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f indexer.php -- [options]

  compile                       compile all files
  recheck                       recheck (invalidate)
  reset                         reset
  config                        show config
  help                          This help

USAGE;
    }
}

$shell = new Mage_Shell_Opcache();
$shell->run();
