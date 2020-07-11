<?php

namespace Icinga\Module\Cube;

use Icinga\Data\Tree\TreeNode;
use Icinga\Data\Tree\TreeNodeIterator;
use Icinga\Module\Cube\HostCube;
use ipl\Html\Html;
use ipl\Web\Url;
use ipl\Web\Widget\Link;
use RecursiveIteratorIterator;
use SplStack;

/**
 *
 */
class RenderCubeTreeNode extends RecursiveIteratorIterator
{
    protected $stack;

    protected $dimensions;

    protected $iterableNodes;

    public function __construct(TreeNode $tree)
    {

        parent::__construct(
            new TreeNodeIterator($tree),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        $this->iterableNodes = $this;
    }

    /**
     * @return mixed
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * @param mixed $dimensions
     */
    public function setDimensions($dimensions)
    {
        $this->dimensions = $dimensions;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function beginIteration()
    {
        $this->stack = new SplStack();
        $this->stack->push(Html::tag('div', ['class' => 'cube-main-container']));
    }

    /**
     * {@inheritdoc}
     */
    public function beginChildren()
    {
        $level = count($this->getDimensions()) - 1 - $this->getDepth();
        $this->stack->push(Html::tag('div', ['class' => 'cubes-dimension-wrapper level' . $level]));
    }

    /**
     * @return Html
     */
    public function render()
    {
        /*Url::fromPath('customer/customer/delete')->addParams(['id' => $this->customer->id]),
            'trash',
            ['data-base-target' => '_self']
        );*/
        var_dump(Url::fromPath('icingadb/hosts',['a' => 'abc'])->addParams()->);
        $lastDimension = $this->getDimensions()[count($this->getDimensions()) - 1];
        $cnt = 'cnt';

        foreach($this->iterableNodes as $item) {
            $level = count($this->getDimensions()) - $this->getDepth();

            foreach ($this->getDimensions() as $keys => $dimension) {
                // it is parent
                if ($this->iterableNodes->getInnerIterator()->hasChildren()) {
                    $cube = Html::tag('div', ['class' => 'cube-header level' . $level]);
                    $cube
                        ->add((new Link(ucfirst(str_replace('"','', $item->getValue()->$dimension)) , 'example/NOPAGE', ['class' => 'cube-link']))
                            ->add(Html::tag('span', ['class' => 'sum'], ' ('. $item->getValue()->$cnt. ')'))
                        )
                        ->add(new Link('', 'example/NOPAGE', ['class' => 'icon-filter']));

                    $htmlEl = (Html::tag('div', ['class' => 'dimension-wrapper level' . $level]))
                        ->add($cube)
                        ->add($this->stack->pop());

                    $this->stack->top()->add($htmlEl);
                    continue 2;
                } else {
                    // to just fill the cube with the last dimension value
                    if ($lastDimension != $dimension) {
                        continue;
                    }
                    $cube = (new HostCube())
                        ->setValue($item->getValue())
                        ->setDimension($dimension);
                }
            }
            $this->stack->top()->add($cube);
        }

        return $this->stack->pop();
    }
}
