<?php

namespace Icinga\Module\Cube;

use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;
use ipl\Web\Widget\Icon;
use ipl\Web\Widget\Link;

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
     * Render dimentions
     *
     * Dynamically render up to 3 dimensions
     *
     * @param $dim array array of dimensions
     *
     * @return BaseHtmlElement|\ipl\Html\HtmlElement
     */
    public function renderDimensions($dim) {
        $counter = 0;
        $temp = json_decode(json_encode($this->data),true);
        $tempSub= [];
        foreach ($temp as $one) {
            if (array_search(null,$one)) {
                $tempSub[] = $temp[$counter]['cnt'];
            }
            $counter++;
        }
        $counter = 0;
        $lastValue = null;
        $lastDim = null;
        $dimIndexCount = count($dim) -1;
        $dimensionEachList = [];
        $dimensionWrapper = Html::tag('div', ['class' => 'cube-dimension-wrapper']);
        $total = $tempSub[array_key_last($tempSub)];
        $subTotal = 0;
        var_dump($tempSub);
        // var_dump($dim[array_key_last ($dim)]);
        // var_dump($temp);
        // var_dump(array_search($loc,$temp));
        //die;
        foreach ($this->data as $val) {

            $value = json_decode(json_encode($val),true);
            // if dimension 1 or 2
            if ($dimIndexCount) {
                //Create each dimension list header if :
                // $lastValue is null or not same as $value[$dim[$dimIndexCount-1]] and not null
                // $lastValue == null : means $lastValue have never been initialized, so we create first header and initialize $lastValue
                // $value[$dim[$dimIndexCount-1]] == null : it is subtotal of each dimension, we don't need to
                //  create a header for subtotal
                if (! $value[$dim[$dimIndexCount]]) {

                    if (! $value[$dim[$dimIndexCount]] && ! $value[$dim[$dimIndexCount-1]])
                        $total = $value['cnt'];
                    else
                        $subTotal = $tempSub[$counter];
                }
                var_dump($subTotal);
                if (! $lastValue || $lastValue != $value[$dim[$dimIndexCount-1]] && $value[$dim[$dimIndexCount-1]]) {
                    $str = json_decode(strtoupper(($value[$dim[$dimIndexCount-1]]))) . '('. $subTotal .')';
                    //TODO fix this
                    if($str === null) {
                        $str = $value[$dim[$dimIndexCount-1]];
                    }

                    $dimensionEachListHeader = Html::tag('div', ['class' => 'each-dim-list-header']);
                    $dimensionEachListHeader->add(new Link(ucfirst( $str), 'example/NOPAGE', ['class' => 'cube-link']));
                    $dimensionEachListHeader->add(new Link(new Icon('filter'), 'example/NOPAGE', ['class' => 'icon-cube-filter']));
                    // after break, add new dim header in new line
                    $counter++;
                    $dimensionEachList[$counter] = Html::tag('div', ['class' => 'each-dim-list']);

                    // 3. dimension : create header for each section
                    if ($dimIndexCount === 2) {
                        if(!$lastDim || $lastDim != $value[$dim[0]]) {
                            //TODO FIX THIS 1 or true
                            $str = json_decode($value[$dim[0]]) . '('. $total .')';
                            if($str === null) {
                                $str = $value[$dim[0]];
                            }
                            $thirdDimWrapper = Html::tag('div', ['class' => 'cube-third-dim-wrapper'], strtoupper($str));
                            $dimensionEachList[$counter]->add($thirdDimWrapper);
                            $lastDim = $value[$dim[0]];
                        }
                    }

                    $dimensionEachList[$counter]->add($dimensionEachListHeader);

                    $lastValue = $value[$dim[$dimIndexCount-1]];
                }
            }
            //  if $value index is null, it is the subtotal, and we don't create a cube for that
            if ($value[$dim[$dimIndexCount]]) {
                $eachDimensionEl = Html::tag('div', ['class' => 'cube-each-dimension']);
                $innerHeader = Html::tag('div', ['class' => 'dimension-inner-header']);
                $innerBody = Html::tag('div', ['class' => 'dimension-inner-body']);
                $innerFooter = Html::tag('div', ['class' => 'dimension-inner-footer']);
                $dimValue = json_decode($value[$dim[$dimIndexCount]]);

                if(is_bool($dimValue)) {
                    $dimValue = $dimValue ? 'true' :'false';
                }

                $innerHeader->add(new Link(ucfirst( $dimValue), 'example/NOPAGE', ['class' => 'cube-link']));
                $innerHeader->add(new Link(new Icon('filter'), 'example/NOPAGE', ['class' => 'cube-filter-icon']));
                $innerBody->add(Html::tag('span', '199'));
                $innerFooter->add(Html::tag('span', ucfirst(json_decode($value['cnt']))));

                $eachDimensionEl->add($innerHeader);
                $eachDimensionEl->add($innerBody);
                $eachDimensionEl->add($innerFooter);

                if ($dimIndexCount === 0) $dimensionWrapper->add($eachDimensionEl);
                else  $dimensionEachList[$counter]->add($eachDimensionEl);
            }
        }

        if ($dimIndexCount === 0) {
            return $dimensionWrapper;
        }
        return $dimensionWrapper->add($dimensionEachList);
    }

    protected function assemble()
    {
        $this->add(Html::tag('div',['class' => 'icingadb-cube'], $this->renderDimensions($this->getDimensions())));
    }
}
