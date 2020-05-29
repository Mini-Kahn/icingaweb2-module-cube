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
     * dynamically render up to 3 dimensions
     */
    public function renderDimensions()
    {

    }

    public function renderOneDimension($dim) {
        $dimensionWrapper = Html::tag('div', ['class' => 'cube-dimension-wrapper']);
        $lastValue = null;
        $lastDim = null;
        $dimIndexCount = count($this->getDimensions()) -1;

        foreach ($this->data as $val) {
            $value = json_decode(json_encode($val),true);

            // if value not null, means dont create subtotal box
            if ($value[$dim[$dimIndexCount]]) {
                $eachDimensionEl = Html::tag('div', ['class' => 'cube-each-dimension']);
                $innerHeader = Html::tag('div', ['class' => 'dimension-inner-header']);
                $innerBody = Html::tag('div', ['class' => 'dimension-inner-body']);
                $innerFooter = Html::tag('div', ['class' => 'dimension-inner-footer']);
                $dimValue = json_decode($value[$dim[$dimIndexCount]]);
                if(is_bool($dimValue)) {
                    $dimValue = $dimValue ? "true" :"false;";
                }
                $innerHeader->add(new Link(ucfirst( $dimValue), 'example/NOPAGE', ['class' => 'cube-link']));
                $innerHeader->add(new Link(new Icon('filter'), 'example/NOPAGE', ['class' => 'cube-filter-icon']));
                $innerBody->add(Html::tag('span', '199'));
                $innerFooter->add(Html::tag('span', ucfirst(json_decode($value['cnt']))));

                $eachDimensionEl->add($innerHeader);
                $eachDimensionEl->add($innerBody);
                $eachDimensionEl->add($innerFooter);
                $dimensionWrapper->add($eachDimensionEl);
            }
        }

        return $dimensionWrapper;
    }

    public function renderTwoDimensions($dim)
    {
        $lastValue = null;
        $lastDim = null;
        $dimIndexCount = count($this->getDimensions()) -1;
        $counter = 0;
        $dimensionEachList = [];
        $dimensionWrapper = Html::tag('div', ['class' => 'cube-dimension-wrapper']);

        foreach ($this->data as $val) {
            // $val is an object, to change it in normal array, we encode and then decode this
            $value = json_decode(json_encode($val),true);
            // 2 dim always true here

            if ($dimIndexCount) {
                // create header on every new line, if its not null
                if (! $lastValue || $lastValue != $value[$dim[$dimIndexCount-1]] && $value[$dim[$dimIndexCount-1]]) {
                    $str = json_decode(strtoupper(($value[$dim[$dimIndexCount-1]])));
                    $dimensionEachListHeader = Html::tag('div', ['class' => 'each-dim-list-header']);
                    $dimensionEachListHeader->add(new Link(ucfirst( $str), 'example/NOPAGE', ['class' => 'cube-link']));
                    $dimensionEachListHeader->add(new Link(new Icon('filter'), 'example/NOPAGE', ['class' => 'icon-cube-filter']));
                    // after break, add new dim in new line
                    $counter++;
                    $dimensionEachList[$counter] = Html::tag('div', ['class' => 'each-dim-list']);
                    $dimensionEachList[$counter]->add($dimensionEachListHeader);

                    $lastValue = $value[$dim[$dimIndexCount-1]];
                }
            }
            if ($value[$dim[$dimIndexCount]]) {
                $eachDimensionEl = Html::tag('div', ['class' => 'cube-each-dimension']);
                $innerHeader = Html::tag('div', ['class' => 'dimension-inner-header']);
                $innerBody = Html::tag('div', ['class' => 'dimension-inner-body']);
                $innerFooter = Html::tag('div', ['class' => 'dimension-inner-footer']);

                $dimValue = json_decode($value[$dim[$dimIndexCount]]);
                if(is_bool($dimValue)) {
                    $dimValue = $dimValue ? "true" :"false;";
                }
                $innerHeader->add(new Link(ucfirst($dimValue), 'example/NOPAGE', ['class' => 'cube-link']));
                $innerHeader->add(new Link(new Icon('filter'), 'example/NOPAGE', ['class' => 'icon-cube-filter']));

                $innerBody->add(Html::tag('span', '199'));
                $innerFooter->add(Html::tag('span', ucfirst(json_decode($value['cnt']))));

                $eachDimensionEl->add($innerHeader);
                $eachDimensionEl->add($innerBody);
                $eachDimensionEl->add($innerFooter);

                $dimensionEachList[$counter]->add($eachDimensionEl);
            }
        }

        return $dimensionWrapper->add($dimensionEachList);
    }


    public function renderThreeDimensions($dim)
    {
        $lastValue = null;
        $lastDim = null;
        $dimIndexCount = count($this->getDimensions()) - 1;
        $counter = 0;
        $dimensionEachList = [];
        $dimensionWrapper = Html::tag('div', ['class' => 'cube-dimension-wrapper']);

        foreach ($this->data as $val) {
            $value = json_decode(json_encode($val), true);
               // always true
            if ($dimIndexCount) {
                // create header on every new line, if its not null
                if (! $lastValue || $lastValue != $value[$dim[$dimIndexCount-1]] && $value[$dim[$dimIndexCount-1]]) {
                    $str = json_decode(strtoupper(($value[$dim[$dimIndexCount-1]])));
                    $dimensionEachListHeader = Html::tag('div', ['class' => 'each-dim-list-header']);
                    $dimensionEachListHeader->add(new Link(ucfirst( $str), 'example/NOPAGE', ['class' => 'cube-link']));
                    $dimensionEachListHeader->add(new Link(new Icon('filter'), 'example/NOPAGE', ['class' => 'icon-cube-filter']));
                    // after break, add new dim in new line
                    $counter++;
                    $dimensionEachList[$counter] = Html::tag('div', ['class' => 'each-dim-list']);
                    if ($dimIndexCount > 1) {
                        if(!$lastDim || $lastDim != $value[$dim[0]]) {
                            $thirdDimWrapper = Html::tag('div', ['class' => 'cube-third-dim-wrapper'], strtoupper(json_decode($value[$dim[0]])));
                            $dimensionEachList[$counter]->add($thirdDimWrapper);
                            $lastDim = $value[$dim[0]];
                        }
                    }
                    $dimensionEachList[$counter]->add($dimensionEachListHeader);

                    $lastValue = $value[$dim[$dimIndexCount-1]];

                }
            }
            if ($value[$dim[$dimIndexCount]]) {
                $eachDimensionEl = Html::tag('div', ['class' => 'cube-each-dimension']);
                $innerHeader = Html::tag('div', ['class' => 'dimension-inner-header']);
                $innerBody = Html::tag('div', ['class' => 'dimension-inner-body']);
                $innerFooter = Html::tag('div', ['class' => 'dimension-inner-footer']);

                $dimValue = json_decode($value[$dim[$dimIndexCount]]);
                if(is_bool($dimValue)) {
                    $dimValue = $dimValue ? "true" :"false;";
                }
                $innerHeader->add(new Link(ucfirst($dimValue), 'example/NOPAGE', ['class' => 'cube-link']));
                $innerHeader->add(new Link(new Icon('filter'), 'example/NOPAGE', ['class' => 'icon-cube-filter']));

                $innerBody->add(Html::tag('span', '199'));
                $innerFooter->add(Html::tag('span', ucfirst(json_decode($value['cnt']))));

                $eachDimensionEl->add($innerHeader);
                $eachDimensionEl->add($innerBody);
                $eachDimensionEl->add($innerFooter);

                $dimensionEachList[$counter]->add($eachDimensionEl);
            }
        }
        return $dimensionWrapper->add($dimensionEachList);
    }


    protected function assemble()
    {

       switch(count($this->getDimensions())) {
           case 1:
               $this->add(Html::tag('div',['class' => 'icingadb-cube'], $this->renderOneDimension($this->getDimensions())));
               break;
           case 2:
               $this->add(Html::tag('div',['class' => 'icingadb-cube'], $this->renderTwoDimensions($this->getDimensions())));
               break;
           case 3:
               $this->add(Html::tag('div',['class' => 'icingadb-cube'], $this->renderThreeDimensions($this->getDimensions())));
               break;
           default:
               break;
       }
    }
}
