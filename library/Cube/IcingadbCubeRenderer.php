<?php

namespace Icinga\Module\Cube;

use Icinga\Data\Tree\TreeNode;
use Icinga\Data\Tree\TreeNodeIterator;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;
use ipl\Web\Widget\Icon;
use ipl\Web\Widget\Link;
use mysql_xdevapi\Exception;

/**
 * The detail widget show key-value pairs as simple list
 */
class IcingadbCubeRenderer extends BaseHtmlElement
{
    protected $data;

    protected $tag = 'div';

    protected $dimensions;

    /**
     * Detail widget constructor
     *
     * @param iterable $data
     */
    public function __construct($rawData)
    {
        // this was included to make obj to a array and remove extra ''
        //$loopCount = 0;
        /*$newData = [];
        foreach (json_decode(json_encode($rawData),true) as $itemArr) {
            $newData[$loopCount] = str_replace('"', '', $itemArr);
            $loopCount++;
        }
        $this->data = $newData;*/
        $this->data = $rawData;

    }

    /**
     * Get dimensions
     *
     * @return array
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * Set dimensions
     *
     * @param array $dimensions
     *
     * @return icingadbCubeRenderer
     */
    public function setDimensions($dimensions)
    {
        $this->dimensions = $dimensions;
        return $this;
    }

    public function makeTree ($urlDimensions) {

       $parent = $urlDimensions[0];
       if (count($urlDimensions) == 2 ) $children = $urlDimensions[1];
       $cnt = 'cnt';

        $dim1 = [];
        $dim2 = [];
        $cntAr = [];
        $totalEachDim = [];

        for ($i = 0; $i < count($this->data); $i++) {
            $dim1[] = $this->data[$i]->$parent;
            if (count($urlDimensions) > 1) {
                $dim2[] = $this->data[$i]->$children;
                if ($dim2[$i]) $cntAr[] = $this->data[$i]->$cnt;
                else $totalEachDim[] = $this->data[$i]->$cnt;
            }
        }
        $dim1 =   array_values(array_unique(array_filter(str_replace('"', '', $dim1))));
        if (count($urlDimensions) == 2 )
        $dim2 =   array_values(array_unique(array_filter(str_replace('"', '', $dim2))));

        $root = new TreeNode();
        static $count = 0;

        for ($i = 0; $i < count($dim1); $i++) {
            $rootChild = null;
            $root->appendChild((new TreeNode)->setValue($dim1[$i]));

            if (count($urlDimensions) == 2 )
                foreach ($dim2 as $key => $dim) {

                    $rootChild = $root->getChildren()[$i];
                    $rootChild->appendChild((new TreeNode)->setValue($dim));

                    $setCnt = $rootChild->getChildren()[$key];
                    $setCnt->appendChild((new TreeNode)->setValue($cntAr[$count++]));
                }
        }
    echo  $this->renderDimensionsTree($root, 0 , $totalEachDim);die;
    }

    public function renderDimensionsTree ($root, $indent = 0 , $total = null) {
        $var = new TreeNodeIterator($root);
        static $str  = null;
        static $count = 0;
        do {
            $str .= str_repeat('|_ ', $indent);

            if(! empty($total)) $str .= nl2br($var->current()->getValue() . $total[$count++] . "\n");
            else $str .= nl2br($var->current()->getValue() . "\n");

            if($var->current()->hasChildren()) {
                $this->renderDimensionsTree($var->current(), $indent+1);
            }
            $var->next();
        } while($var->current());

        return $str;
    }

    protected function assemble()
    {
        $this->add(Html::tag('div',['class' => 'icingadb-cube'], $this->makeTree($this->getDimensions())));
    }
}
