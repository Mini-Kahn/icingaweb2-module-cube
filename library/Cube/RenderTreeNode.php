<?php

namespace Icinga\Module\Cube;

use Icinga\Data\Tree\TreeNode;
use Icinga\Data\Tree\TreeNodeIterator;
use ipl\Html\Html;
use ipl\Web\Widget\Link;
use RecursiveIteratorIterator;
use SplStack;

/**
 *
 */
class RenderTreeNode extends RecursiveIteratorIterator
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
        $toIgnoreDim = $this->getDimensions();
        array_pop($toIgnoreDim);
        $this->dimensions[] = 'cnt';

        foreach($this->iterableNodes as $item) {
            $level = count($this->getDimensions()) - 1 - $this->getDepth();
            $cube = Html::tag('div', ['class' => 'icingadbcube']);
            $body = (Html::tag('div', ['class' => 'body']));
            $data = null;
            foreach ($this->getDimensions() as $keys => $dimension) {
                // it is parent
                if ($this->getInnerIterator()->hasChildren()) {
                    // to ignore same value in every parent header and empty tags
                    if($keys < $this->getDepth() - 1 || $item->getValue()->$dimension === null) {
                        continue;
                    }
                    if (! is_numeric($item->getValue()->$dimension)) {
                    $data = ucfirst(str_replace('"','', $item->getValue()->$dimension));
                    } else {
                        $cube
                            ->add((new Link($data , 'example/NOPAGE', ['class' => 'cube-link']))
                                ->add(Html::tag('span', ['class' => 'sum' . $level], ' ('. $item->getValue()->$dimension. ')'))
                            )
                            ->add(new Link('', 'example/NOPAGE', ['class' => 'icon-filter']));
                    }
                } else { // it is child
                    // to just put the last dim value and cnt in span
                    if(in_array($dimension, $toIgnoreDim)) {
                        continue;
                    }
                    if(! is_numeric($item->getValue()->$dimension)) {
                        $cube
                            ->add((Html::tag('div', ['class' => 'header']))
                                ->add(new Link(ucfirst(str_replace('"','', $item->getValue()->$dimension)), 'example/NOPAGE', ['class' => 'cube-link']))
                                ->add(new Link('', 'example/NOPAGE', ['class' => 'icon-filter']))
                            );
                            $body->add((Html::tag('span', ['class' => 'critical'], '21')));
                    }
                    else {
                        //TODO ADD SPAN FOR CRITICAL OR OK HERE, maybe we need a if condition to avoid creating other span, if all hosts are ok
                        $cube->add(
                            $body->add((Html::tag('span', ['class' => 'others']))
                                    ->add((Html::tag('span', ['class' => 'ok'], str_replace('"','', $item->getValue()->$dimension))))
                                )
                        );
                    }
                }
            }
            if ($this->getInnerIterator()->hasChildren()) {
                $htmlEl = (Html::tag('div', ['class' => 'dimension-wrapper level' . $level]))
                    ->add($cube->setAttribute('class' ,'cube-header level' . $level))
                    ->add($this->stack->pop());

                $this->stack->top()->add($htmlEl);
            } else {
                $this->stack->top()->add($cube);
            }
        }

        return $this->stack->pop();
    }
}
