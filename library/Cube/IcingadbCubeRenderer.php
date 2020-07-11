<?php

namespace Icinga\Module\Cube;

use Icinga\Data\Tree\TreeNode;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;
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
    public function __construct($data)
    {
        $this->data = $data;
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

    /**
     * Make Tree Structure out of data
     *
     * @return TreeNode
     */
    public function getCubeTree()
    {
        $pending = new TreeNode();
        $tiers = new SplStack();

        foreach ($this->data as $data) {
            foreach ($this->dimensions as $dimension) {
                if ($data->$dimension === null) {
                    $pending->setValue($data);
                    while (true) {
                        if ($tiers->isEmpty() || $tiers->top()->getValue()->$dimension == null) {
                            break;
                        }
                        $pending->appendChild($tiers->pop());
                    }
                    $tiers->push($pending);

                    $pending = new TreeNode();
                    continue 2;
                }
            }

            $pending->appendChild((new TreeNode)->setValue($data));
        }

        return $pending->appendChild($tiers->pop());
    }


    protected function assemble()
    {
        $this->add(
            (new RenderCubeTreeNode($this->getCubeTree()))
                ->setDimensions($this->dimensions)
                ->render()
        );
    }
}
