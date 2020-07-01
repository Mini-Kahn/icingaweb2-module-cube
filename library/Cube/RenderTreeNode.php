<?php

namespace Icinga\Module\Cube;

use Icinga\Data\Tree\TreeNode;
use Icinga\Data\Tree\TreeNodeIterator;
use ipl\Html\Html;
use RecursiveIteratorIterator;
use SplStack;
use function Sodium\add;

/**
 *
 */
class RenderTreeNode extends RecursiveIteratorIterator
{
    protected $htmlElement;

    protected $htmlWrapper;

    protected $currentValue;

    protected $dimensions;

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
        $this->dimensions[] = 'cnt';
        return $this;
    }

    public function __construct(TreeNode $tree)
    {

        parent::__construct(
            new TreeNodeIterator($tree),
            RecursiveIteratorIterator::CHILD_FIRST
        );
    }

    /**
     * {@inheritdoc}
     */
    public function beginIteration()
    {
       //var_dump(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function endIteration()
    {
        //var_dump(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function beginChildren()
    {
        //var_dump(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function endChildren()
    {
        //$this->htmlWrapper->add($this->htmlElement);
        unset($this->htmlElement);
        //var_dump(__METHOD__);
    }


    public function iterateMyOutput()
    {
        $stack = new SplStack();
        $lastNullDim = null;
        $cnt = 'cnt';
        $myHtmlElement = Html::tag('div', ['class' => 'cube-dimension-wrapper']);
        foreach($this as $key => $item) {
            $el = $myHtmlElement->add(Html::tag('div', ['class' => 'cube-dimension-wrapper']));
            foreach ($this->getDimensions()  as $keys => $dimension) {
                if ($item->getValue()->$dimension == null) {

                    $el->add(Html::tag('span', ['class' => 'cube-dimension-wrapper, header , level-' . $this->getDepth()], 'HEAD->' . $item->getValue()->$cnt));
                        while(true) {
                            if (! $lastNullDim || $lastNullDim == $dimension  || $stack->isEmpty()) {
                                break;
                            }
                            $el->add(Html::tag('div', ['class' => 'cube-dimension-wrapper, level-' . $this->getDepth()], $stack->pop()));
                        }
                        $lastNullDim = $dimension;
                        $stack->push($myHtmlElement);
                        $myHtmlElement = Html::tag('div', ['class' => 'cube-dimension-wrapper']);
                        break;
                    }
                else
                    $el->add(Html::tag('span', ['class' => 'cube-dimension-wrapper, level-' . $this->getDepth()], $item->getValue()->$dimension));
                }

            }

        return $stack->pop();
    }

}
