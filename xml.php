<?php

class Xml
{

    private $node = [];
    private $doc;

    public function __construct()
    {
        $this->doc = new DOMDocument('1.0', 'utf-8');
        $this->doc->formatOutput = true;
    }

    public function addNode($name)
    {
        $element = $this->doc->createElement($name);
        $this->node[] = $element;
        return $element;
    }

    /**
     * 最后整理对应的xml的顺序
     * @return DOMNode
     *
     */
    public function mangeXml()
    {
        $count = count($this->node);
        $i = $count - 1;
        while ($i - 1 >= 0) {
            $this->appendChild($this->node[$i - 1], ($this->node[$i]));
            $i--;
        }
        #设置对应的root节点
        return $this->appendRoot($this->node[0]);
    }

    /**
     *创建对应的node，并且在node上赋值相应的值
     */
    public function attachNodeValue($node, $data)
    {
        foreach ($data as $key => $value) {
            //设置对应的数值和键名
            $attribute = $this->createAttributeAndValue($key,$value);
            //在对应的node上进行append
            $this->appendChild($node, $attribute);
        }
    }

    public function createAttributeAndValue($key,$value){
        $attribute = $this->createAttribute($key);
        $value = $this->createTextNode($value);
        $this->appendChild($attribute, $value);
        return $attribute;
    }

    /**
     *创建对应的attribute
     */
    public function createAttribute($atrribute)
    {
        return $this->doc->createAttribute($atrribute);
    }

    /**
     *创建atrribute对应的值
     */
    public function createTextNode($TextNode)
    {
        return $this->doc->createTextNode($TextNode);
    }

    public function appendChild($name_node, $value_node)
    {
        return $name_node->appendChild($value_node);
    }

    public function appendRoot($name)
    {
        return $this->doc->appendChild($name);
    }

    public function addMutilAppend($nodeArray, $data, $node,$node_data)
    {
        foreach ($data as $value) {
            $node0 = $this->createAndAppend($node, $nodeArray[0]);
            foreach ($value as $v) {
                $node1 = $this->createAndAppend($node0, $nodeArray[1]);
                $node2 = $this->createAndAppend($node1, $nodeArray[2]);
                $this->attachNodeValue($node2,$node_data);
                $this->appendChild($node2, $this->createTextNode($v));
            }
        }
    }

    public function createAndAppend($node, $append_name)
    {
        $element = $this->doc->createElement($append_name);
        $this->appendChild($node, $element);
        return $element;
    }

    public function save($file)
    {
        $this->doc->save($file);
    }
}


//示例
$xml = new Xml();
$root = $xml->addNode('Workbook');
$index = $xml->addNode('Worksheet');
$worksheet_type = [
    "xmlns" => "urn:schemas-microsoft-com:office:spreadsheet",
    "xmlns:o" => "urn:schemas-microsoft-com:office:office",
    "xmlns:x" => "urn:schemas-microsoft-com:office:excel",
    "xmlns:ss" => "urn:schemas-microsoft-com:office:spreadsheet",
    "xmlns:html" => "http://www.w3.org/TR/REC-html40",
];
$xml->attachNodeValue($root, $worksheet_type);
$xml->attachNodeValue($index, ["ss:Name" => "Sheet1"]);
$table = $xml->addNode('Table');
$data = [
    ["字段名", "字段类型", "备注"],
    ["test", "int", "1"],
    ["name", "varchar", "2"]
];
$xml->attachNodeValue($table, ["ss:ExpandedColumnCount" => 3, "ss:ExpandedRowCount" => count($data)]);
$node_array = ["Row", "Cell", "Data"];
$node_data = ["ss:Type" => "String"];
$xml->addMutilAppend($node_array, $data, $table,$node_data);
$xml->mangeXml();//把对应的生成的元素的进行排序
$xml->save("test.xml");

?>