<?php

namespace Icinga\Module\Cube;

use Icinga\Data\Tree\TreeNode;
use Icinga\Data\Tree\TreeNodeIterator;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;
use ipl\Web\Widget\Icon;
use ipl\Web\Widget\Link;
use mysql_xdevapi\Exception;
use RecursiveIteratorIterator;
use SplStack;

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

    public function makeTree()
    {
        ini_set('xdebug.var_display_max_depth', '10');
        ini_set('xdebug.var_display_max_children', '256');
        ini_set('xdebug.var_display_max_data', '1024');

        $pending = new TreeNode();
        $tiers = new SplStack();

        foreach ($this->data as $data) {
            // $data = (object) $data;

            foreach ($this->dimensions as $dimension) {
                if ($data->$dimension === null) {
                    $pending->setValue($data);

                    while (true) {
                        if ($tiers->isEmpty() || $tiers->top()->getValue()->$dimension == null)
                            break;

                        $pending->appendChild($tiers->pop());
                    }

                    $tiers->push($pending);

                    $pending = new TreeNode();
                    continue 2;
                }
            }

            $pending->appendChild((new TreeNode)->setValue($data)); //TODO as in pic
        }

        $pending->appendChild($tiers->pop());
        //$renderedTree =
          return  (new RenderTreeNode($pending))
            ->setDimensions($this->dimensions)
            ->iterateMyOutput();
    }


    protected function assemble()
    {
        $this->add(Html::tag('div',['class' => 'icingadb-cube'], $this->makeTree()));

    }
}
