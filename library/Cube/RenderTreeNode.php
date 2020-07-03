<?php

namespace Icinga\Module\Cube;

use Icinga\Data\Tree\TreeNode;
use Icinga\Data\Tree\TreeNodeIterator;
use ipl\Html\FormElement\SelectElement;
use ipl\Html\Html;
use ipl\Web\Widget\Icon;
use ipl\Web\Widget\Link;
use RecursiveIteratorIterator;
use SplStack;
use function Sodium\add;

/**
 *
 */
class RenderTreeNode extends RecursiveIteratorIterator
{
    protected $stack;

    protected $dimensions;

    protected $iterableNodes;

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

    public function __construct(TreeNode $tree)
    {

        parent::__construct(
            new TreeNodeIterator($tree),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        $this->iterableNodes = $this;
        $this->stack = new SplStack();
    }

    /**
     * {@inheritdoc}
     */
    public function beginIteration()
    {
        $this->stack->push(Html::tag('div', ['class' => 'cube-main-container']));
    }

    /**
     * {@inheritdoc}
     */
    public function beginChildren()
    {
        $this->stack->push(Html::tag('div', ['class' => 'cubes-dimension-wrapper level' . $this->getDepth()]));
    }

    public function iterateMyOutput()
    {
        $toIgnoreDim = $this->getDimensions();
        array_pop($toIgnoreDim);
        /*var_dump($toIgnoreDim[count($toIgnoreDim)-1]);die;*/
        $this->dimensions[] = 'cnt';

        foreach($this->iterableNodes as $key => $item) {
            $cube = Html::tag('div', ['class' => 'icingadbcube']);
            $body = (Html::tag('div', ['class' => 'body']));
            foreach ($this->getDimensions() as $keys => $dimension) {
                // it is parent
                if ($this->getInnerIterator()->hasChildren()) {

                    // to ignore same dimension values in parents, so just put the unique value in child parent
                    if($keys < $this->getDepth() - 1 || $item->getValue()->$dimension === null) {
                        continue;
                    }
                  //  var_dump($dimension, $toIgnoreDim[count($toIgnoreDim)-1]);
                    if(! is_numeric($item->getValue()->$dimension)) {
                        $cube->add(new Link(ucfirst(str_replace('"','', $item->getValue()->$dimension)), 'example/NOPAGE', ['class' => 'cube-link']));
                    }
                    else {
                        $cube
                            ->add(Html::tag('span', ['class' => 'cube-item' . $this->iterableNodes->getDepth()], str_replace('"','', $item->getValue()->$dimension)))
                            ->add(new Link('', 'example/NOPAGE', ['class' => 'cube-filter-icon']));;
                    }
                } // it is child
                else {
                    // to just put the last dim value in span and the cnt
                    if( in_array($dimension, $toIgnoreDim)) {
                        continue;
                    }
                    if(! is_numeric($item->getValue()->$dimension)) {
                        $cube
                            ->add((Html::tag('div', ['class' => 'header']))
                                ->add(new Link(ucfirst(str_replace('"','', $item->getValue()->$dimension)), 'example/NOPAGE', ['class' => 'cube-link']))
                                ->add(new Link('', 'example/NOPAGE', ['class' => 'icon-filter']))
                            );
                            $body->add((Html::tag('span', ['class' => 'critical'], '21')));
                        //TODO ADD
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
                $htmlEl = (Html::tag('div', ['class' => 'dimension-wrapper level' . $this->getDepth()]))
                    ->add($cube->setAttribute('class' ,'cube-header level' . $this->getDepth()))
                    ->add($this->stack->pop());

                $this->stack->top()->add($htmlEl);
            } else {
                $this->stack->top()->add($cube);
            }
        }

        return $this->stack->pop();
    }

}
