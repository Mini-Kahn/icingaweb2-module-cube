<?php

namespace Icinga\Module\Cube;

use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;

/**
 * The detail widget show key-value pairs as simple list
 */
class icingadbCubeRenderer extends BaseHtmlElement
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
     * Render dimentions
     *
     * dynamically render up to 3 dimensions
     */
    public function renderDimension()
    {
        $dim = $this->getDimensions();
        $lastValue = null;
        $lastDim = null;
        $dimIndexCount = count($this->getDimensions()) -1;

        foreach ($this->data as $val) {
            $value = json_decode(json_encode($val),true);

            if ($dimIndexCount > 1) {
                if (! $lastDim || $lastDim != $value[$dim[0]]) {
                    $this->add(Html::tag('br'));
                    $this->add(Html::tag('br'));
                    $this->add(Html::tag('div', strtoupper(json_decode($value[$dim[0]]))));
                    $lastDim = $value[$dim[0]];
                }
            }
            if ($dimIndexCount) {
                if (! $lastValue || $lastValue != $value[$dim[$dimIndexCount-1]]) {

                    $str = strtoupper(json_decode($value[$dim[$dimIndexCount-1]]));

                    $this->add(Html::tag('br'));
                    $this->add(Html::tag('span',$str . '  ==>  ' ));
                    $lastValue = $value[$dim[$dimIndexCount-1]];
                }
            }

            $this->add(Html::tag('span', ucfirst( json_decode($value[$dim[$dimIndexCount]]))));
            $this->add(Html::tag('span', ucfirst( ' ('. json_decode($value['cnt']). ')  ||  ')));

            $subTotal = null;
        }
    }

    protected function assemble()
    {
       $this->renderDimension();
    }
}
