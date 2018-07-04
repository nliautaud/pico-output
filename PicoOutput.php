<?php
/**
 * Output Pico CMS page data as raw text, html, json or xml with `?output`.
 *
 * @author	Nicolas Liautaud
 * @link	https://github.com/nliautaud/pico-content-output
 * @link    http://picocms.org
 * @license http://opensource.org/licenses/MIT The MIT License
 */
final class PicoOutput extends AbstractPicoPlugin
{
    const API_VERSION = 2;

    private $serveContent;
    private $contentFormat;

    /**
     * Look for ?output=format in url (format as `content` by default)
     *
     * Triggered after Pico has evaluated the request URL
     *
     * @see    Pico::getRequestUrl()
     * @param  string &$url part of the URL describing the requested contents
     * @return void
     */
     public function onRequestUrl(&$url)
     {
        $this->serveContent = isset($_GET['output']);
        if ($this->serveContent)
            $this->contentFormat = $_GET['output'] ? $_GET['output'] : 'content';
    }
    /**
     * Output the page data in the defined format.
     *
     * Triggered after Pico has rendered the page
     *
     * @param  string &$output contents which will be sent to the user
     * @return void
     */
    public function onPageRendered(&$output)
    {
        if ($this->serveContent && $this->enabledFormat())
            $output = $this->contentOutput();
    } 
    /**
     * Check if the requested format is enabled in config.
     *
     * @return bool
     */
    public function enabledFormat()
    {
        $enabledFormats = $this->getPico()->getConfig('PicoOutput.enabledFormats');
        return is_array($enabledFormats) && in_array($this->contentFormat, $enabledFormats);
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
        switch ($this->contentFormat) {
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
                $this->array_to_xml($page, $xml);
                return $xml->asXML();
            default:
                return $pico->getFileContent();
        }
    }

    // function defination to convert array to xml
    private function array_to_xml( $data, &$xml_data )
    {
        foreach( $data as $key => $value ) {
            if( is_numeric($key) ){
                $key = 'item'.$key; //dealing with <0/>..<n/> issues
            }
            if( is_array($value) ) {
                $subnode = $xml_data->addChild($key);
                $this->array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild("$key",htmlspecialchars("$value"));
            }
         }
    }
}
?>
