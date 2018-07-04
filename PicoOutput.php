<?php
/**
 * Output Pico CMS page data as raw text, html, json or xml with `?output`.
 *
 * @author  Nicolas Liautaud
 * @link    https://github.com/nliautaud/pico-content-output
 * @link    http://picocms.org
 * @license http://opensource.org/licenses/MIT The MIT License
 */
class PicoOutput extends AbstractPicoPlugin
{
    const API_VERSION = 2;

    private $format;

    /**
     * Output the page data in the defined format.
     *
     * Triggered after Pico has rendered the page
     *
     * @see DummyPlugin::onPageRendering()
     *
     * @param string &$output contents which will be sent to the user
     *
     * @return void
     */
    public function onPageRendered(&$output)
    {
        if (!isset($_GET['output'])) {
            return;
        }
        $default = $this->getSetting('default');
        if (empty($_GET['output']) && empty($default)) {
            return;
        }
        $this->format = empty($_GET['output']) ? $default : $_GET['output'];

        if ($this->format && $this->canOutput($this->format)) {
            $output = $this->contentOutput();
        }
    }

    /**
     * Check if the requested format is enabled in config.
     *
     * @param string $outputFormat
     *
     * @return bool
     */
    public function canOutput($outputFormat)
    {
        $enabledFormats = $this->getPluginConfig('formats');
        if (!is_array($enabledFormats)) {
            $enabledFormats = array();
        }
        $pageMeta = $this->getFileMeta();
        if (isset($pageMeta['PicoOutput']) && is_array($pageMeta['PicoOutput']['formats'])) {
            $enabledFormats = array_merge($enabledFormats, $pageMeta['PicoOutput']['formats']);
        }
        return in_array($outputFormat, $enabledFormats);
    }

    /**
     * Get a plugin setting, on page metadata or on pico config
     *
     * @return mixed
     */
    public function getSetting($key)
    {
        $pageMeta = $this->getFileMeta();
        if (isset($pageMeta['PicoOutput']) && isset($pageMeta['PicoOutput'][$key])) {
            return $pageMeta['PicoOutput'][$key];
        }
        return $this->getPluginConfig($key);
    }

    /**
     * Return the current page data in the defined format.
     * @return string
     */
    private function contentOutput()
    {
        $pico = $this->getPico();
        $page = $pico->getCurrentPage();
        unset($page['previous_page']);
        unset($page['next_page']);
        unset($page['tree_node']);
        switch ($this->format) {
            case 'raw':
                return $pico->getRawContent();
            case 'prepared':
                return $pico->prepareFileContent($pico->getRawContent(), $pico->getFileMeta());
            case 'json':
                header('Content-Type: application/json;charset=utf-8');
                return json_encode($page);
            case 'xml':
                header("Content-type: text/xml");
                $xml = new SimpleXMLElement('<page/>');
                PicoOutput::arrayToXML($page, $xml);
                return $xml->asXML();
            default:
                return $pico->getFileContent();
        }
    }
    
    /**
     * Convert an array to a SimpleXMLElement
     *
     * @param [arr] $arr
     * @param [SimpleXMLElement] $xmlRoot
     * @return void
     * @see https://stackoverflow.com/a/5965940
     */
    private static function arrayToXML($arr, &$xmlRoot)
    {
        foreach ($arr as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item'.$key; //dealing with <0/>..<n/> issues
            }
            if (is_array($value)) {
                $subnode = $xmlRoot->addChild($key);
                PicoOutput::arrayToXML($value, $subnode);
            } else {
                $xmlRoot->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }
}
