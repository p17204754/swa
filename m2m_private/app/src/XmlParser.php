<?php

namespace Messages;

class XmlParser
{
    private $xml_parser;							  // handle to instance of the XML parser
    private $parsed_data;	          // array holds extracted data
    private $element_name;	            // store the current element name
    private $arr_temporary_attributes;	// temporarily store tag attributes and values
    private $xml_string_to_parse;

    public function __construct()
    {
        $this->parsed_data = [];
        $this->arr_temporary_attributes = [];
    }

    // release retained memory
    public function __destruct()
    {
        xml_parser_free($this->xml_parser);
    }

    /**
     * Used to set the XML string to parse
     * @param $xml_string_to_parse
     */
    public function setXmlStringToParse($xml_string_to_parse)
    {
        $this->xml_string_to_parse = $xml_string_to_parse;
    }

    /**
     * Used to get the parsed data of the XML string
     * @return array
     */
    public function getParsedData()
    {
        return $this->parsed_data;
    }

    /**
     * Function to parse each element in the XML string
     */
    public function parseTheXmlString()
    {
        $this->xml_parser = xml_parser_create();

        xml_set_object($this->xml_parser, $this);

        xml_set_element_handler($this->xml_parser, "open_element", "close_element");

        xml_set_character_data_handler($this->xml_parser, "process_element_data");

        $this->parseTheDataString();
    }

    // use the parser to step through the element tags

    /**
     * Parse each the whole data string
     */
    private function parseTheDataString()
    {
        xml_parse($this->xml_parser, $this->xml_string_to_parse);
    }

    /**
     * process an open element event & store the tag name
     * extract the attribute names and values, if any
     * @param $parser
     * @param $element_name
     * @param $attributes
     */
    private function open_element($parser, $element_name, $attributes)
    {
        $this->element_name = $element_name;
        if (sizeof($attributes) > 0)
        {
            foreach ($attributes as $att_name => $att_value)
            {
                $tag_att = $element_name . "." . $att_name;
                $this->arr_temporary_attributes[$tag_att] = $att_value;
            }
        }
    }

    /**
     * Process data from each element in the data string
     * @param $parser
     * @param $element_data
     */
    private function process_element_data($parser, $element_data)
    {
        //if (array_key_exists($this->element_name, $this->parsed_data) === false) {
            $this->parsed_data[$this->element_name] = $element_data;
            if (sizeof($this->arr_temporary_attributes) > 0) {
                foreach ($this->arr_temporary_attributes as $tag_att_name => $tag_att_value) {
                    $this->parsed_data[$tag_att_name] = $tag_att_value;
                }
            }
            //}
        }

    /**
     * Process end element tag
     * @param $parser
     * @param $element_name
     */
    private function close_element($parser, $element_name)
    {
        // do nothing here
    }
}