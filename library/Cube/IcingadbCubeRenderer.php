<?php

namespace Icinga\Module\Cube;

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
        $loopCount = 0;
        $newData = [];
        foreach (json_decode(json_encode($rawData),true) as $itemArr) {
            $newData[$loopCount] = str_replace('"', '', $itemArr);
            $loopCount++;
        }
        $this->data = $newData;
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
     * @param $urlDimensions array array of dimensions
     *
     * @return BaseHtmlElement|\ipl\Html\HtmlElement
     */
    public function renderDimensions($urlDimensions) {
        $counter = 0;
        $arrayNodes = new ArrayNodes($this->data);
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
        $dimIndexCount = count($urlDimensions) -1;
        $dimensionEachList = [];
        $dimensionWrapper = Html::tag('div', ['class' => 'cube-dimension-wrapper']);
        $total = $tempSub[array_key_last($tempSub)];
        $subTotal = 0;

        // var_dump($dim[array_key_last ($dim)]);
        // var_dump($temp);
        // var_dump(array_search($loc,$temp));
        //die;
        foreach ($this->data as $val) {

            $value = $val;
            // if dimension 1 or 2
            if ($dimIndexCount) {
                //Create each dimension list header if :
                // $lastValue is null or not same as $value[$dim[$dimIndexCount-1]] and not null
                // $lastValue == null : means $lastValue have never been initialized, so we create first header and initialize $lastValue
                // $value[$dim[$dimIndexCount-1]] == null : it is subtotal of each dimension, we don't need to
                //  create a header for subtotal
                if (! $value[$urlDimensions[$dimIndexCount]]) {

                    if (! $value[$urlDimensions[$dimIndexCount]] && ! $value[$urlDimensions[$dimIndexCount-1]])
                        $total = $value['cnt'];
                    else
                        $subTotal = $tempSub[$counter];
                }

                if (! $lastValue || $lastValue != $value[$urlDimensions[$dimIndexCount-1]] && $value[$urlDimensions[$dimIndexCount-1]]) {
                    $str = json_decode(strtoupper(($value[$urlDimensions[$dimIndexCount-1]]))) . '('. $subTotal .')';
                    //TODO fix this
                    if($str === null) {
                        $str = $value[$urlDimensions[$dimIndexCount-1]];
                    }

                    $dimensionEachListHeader = Html::tag('div', ['class' => 'each-dim-list-header']);
                    $dimensionEachListHeader->add(new Link(ucfirst( $str), 'example/NOPAGE', ['class' => 'cube-link']));
                    $dimensionEachListHeader->add(new Link(new Icon('filter'), 'example/NOPAGE', ['class' => 'icon-cube-filter']));
                    // after break, add new dim header in new line
                    $counter++;
                    $dimensionEachList[$counter] = Html::tag('div', ['class' => 'each-dim-list']);

                    // 3. dimension : create header for each section
                    if ($dimIndexCount === 2) {
                        if(!$lastDim || $lastDim != $value[$urlDimensions[0]]) {
                            //TODO FIX THIS 1 or true
                            $str = json_decode($value[$urlDimensions[0]]) . '('. $total .')';
                            if($str === null) {
                                $str = $value[$urlDimensions[0]];
                            }
                            $thirdDimWrapper = Html::tag('div', ['class' => 'cube-third-dim-wrapper'], strtoupper($str));
                            $dimensionEachList[$counter]->add($thirdDimWrapper);
                            $lastDim = $value[$urlDimensions[0]];
                        }
                    }

                    $dimensionEachList[$counter]->add($dimensionEachListHeader);

                    $lastValue = $value[$urlDimensions[$dimIndexCount-1]];
                }
            }
            //  if $value index is null, it is the subtotal, and we don't create a cube for that
            if ($value[$urlDimensions[$dimIndexCount]]) {
                $eachDimensionEl = Html::tag('div', ['class' => 'cube-each-dimension']);
                $innerHeader = Html::tag('div', ['class' => 'dimension-inner-header']);
                $innerBody = Html::tag('div', ['class' => 'dimension-inner-body']);
                $innerFooter = Html::tag('div', ['class' => 'dimension-inner-footer']);
                $dimValue = json_decode($value[$urlDimensions[$dimIndexCount]]);

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

    public function renderDimensionsTestOne ($urlDimensions) {

        $rowCounter = 0;
        $arrayNodes = new ArrayNodes($this->data);
        $counter = 0;
        $MYCOUNTER = 0;
        $lastValue = null;
        $lastDim = null;
        $dimIndexCount = count($urlDimensions) - 1;
        $dimensionEachList = [];
        $dimensionWrapper = Html::tag('div', ['class' => 'cube-dimension-wrapper']);

        foreach ($this->data as $val) {

            //  if $value index is null, it is the subtotal, and we don't create a cube for that

            if ($arrayNodes->getRoot($rowCounter)) {

                $eachDimensionEl = Html::tag('div', ['class' => 'cube-each-dimension']);
                $innerHeader = Html::tag('div', ['class' => 'dimension-inner-header']);
                $innerBody = Html::tag('div', ['class' => 'dimension-inner-body']);
                $innerFooter = Html::tag('div', ['class' => 'dimension-inner-footer']);

                $innerHeader->add(new Link(ucfirst($arrayNodes->getRoot($rowCounter)), 'example/NOPAGE', ['class' => 'cube-link']));
                $innerHeader->add(new Link(new Icon('filter'), 'example/NOPAGE', ['class' => 'cube-filter-icon']));

                $innerBody->add(Html::tag('span', '199'));
                $innerFooter->add(Html::tag('span', ucfirst($arrayNodes->getEachCnt($rowCounter))));

                $eachDimensionEl->add($innerHeader);
                $eachDimensionEl->add($innerBody);
                $eachDimensionEl->add($innerFooter);

                if ($dimIndexCount === 0) $dimensionWrapper->add($eachDimensionEl);
                else  $dimensionEachList[$counter]->add($eachDimensionEl);
            }
            $rowCounter++;
        }

        if ($dimIndexCount === 0) {
            return $dimensionWrapper;
        }
        return $dimensionWrapper->add($dimensionEachList);
    }

    public function renderDimensionsTestTwo ($urlDimensions) {
        $rowCounter = 0;
        $arrayNodes = new ArrayNodes($this->data);
        $counter = 0;
        $rootNr = 0;
        $childNr = 0;
        $lastValue = null;
        $lastDim = null;
        $dimIndexCount = count($urlDimensions) - 1;
        $dimensionEachList = [];
        $dimensionWrapper = Html::tag('div', ['class' => 'cube-dimension-wrapper']);

        foreach ($arrayNodes->getRoots() as $value) {

            // CREATE HEADER
                    $dimensionEachListHeader = Html::tag('div', ['class' => 'each-dim-list-header']);
                    $dimensionEachListHeader->add(new Link(ucfirst($value), 'example/NOPAGE', ['class' => 'cube-link']));
                    $dimensionEachListHeader->add(new Link(new Icon('filter'), 'example/NOPAGE', ['class' => 'icon-cube-filter']));
                    // after break, add new dim header in new line
                    $dimensionEachList[$counter] = Html::tag('div', ['class' => 'each-dim-list']);

                    $dimensionEachList[$counter]->add($dimensionEachListHeader);

                    $lastValue = $value;

            //  if $value index is null, it is the subtotal, and we don't create a cube for that

            if ($arrayNodes->getRootChild(1,$rowCounter)) {

                $eachDimensionEl = Html::tag('div', ['class' => 'cube-each-dimension']);
                $innerHeader = Html::tag('div', ['class' => 'dimension-inner-header']);
                $innerBody = Html::tag('div', ['class' => 'dimension-inner-body']);
                $innerFooter = Html::tag('div', ['class' => 'dimension-inner-footer']);

                $innerHeader->add(new Link(ucfirst($arrayNodes->getRoot($rowCounter)), 'example/NOPAGE', ['class' => 'cube-link']));
                $innerHeader->add(new Link(new Icon('filter'), 'example/NOPAGE', ['class' => 'cube-filter-icon']));

                $innerBody->add(Html::tag('span', '199'));
                $innerFooter->add(Html::tag('span', ucfirst($arrayNodes->getEachCnt($rootNr++,$rowCounter))));

                $eachDimensionEl->add($innerHeader);
                $eachDimensionEl->add($innerBody);
                $eachDimensionEl->add($innerFooter);

                if ($dimIndexCount === 0) $dimensionWrapper->add($eachDimensionEl);
                else  $dimensionEachList[$counter]->add($eachDimensionEl);
                $rowCounter++;
            }
        $counter++;
        }

        if ($dimIndexCount === 0) {
            return $dimensionWrapper;
        }
        return $dimensionWrapper->add($dimensionEachList);
    }


    public function renderDimensionsTest ($urlDimensions) {

        $rowCounter = 0;
        $arrayNodes = new ArrayNodes($this->data);
        $counter = 0;
        $rootNr = 0;
        $childNr = 0;
        $MYCOUNTER = 0;
        $lastValue = null;
        $lastDim = null;
        $dimIndexCount = count($urlDimensions) - 1;
        $dimensionEachList = [];
        $dimensionWrapper = Html::tag('div', ['class' => 'cube-dimension-wrapper']);

        foreach ($this->data as $val) {

            $value = $val;
            // if dimension 1 or 2
            if ($dimIndexCount) {
                //Create each dimension list header if :
                // $lastValue is null or not same as $value[$dim[$dimIndexCount-1]] and not null
                // $lastValue == null : means $lastValue have never been initialized, so we create first header and initialize $lastValue
                // $value[$dim[$dimIndexCount-1]] == null : it is subtotal of each dimension, we don't need to
                //  create a header for subtotal


                if (! $lastValue || $lastValue != $value[$urlDimensions[$dimIndexCount-1]] && $value[$urlDimensions[$dimIndexCount-1]]) {
                    $str = json_decode(strtoupper(($value[$urlDimensions[$dimIndexCount-1]]))) . '('. 33 .')';
                    //TODO fix this
                    if($str === null) {
                        $str = $value[$urlDimensions[$dimIndexCount-1]];
                    }

                    $dimensionEachListHeader = Html::tag('div', ['class' => 'each-dim-list-header']);
                    $dimensionEachListHeader->add(new Link(ucfirst( $str), 'example/NOPAGE', ['class' => 'cube-link']));
                    $dimensionEachListHeader->add(new Link(new Icon('filter'), 'example/NOPAGE', ['class' => 'icon-cube-filter']));
                    // after break, add new dim header in new line
                    $counter++;
                    $dimensionEachList[$counter] = Html::tag('div', ['class' => 'each-dim-list']);

                    // 3. dimension : create header for each section
                    if ($dimIndexCount === 2) {
                        if(!$lastDim || $lastDim != $value[$urlDimensions[0]]) {
                            //TODO FIX THIS 1 or true
                            $str = json_decode($value[$urlDimensions[0]]) . '('. $total .')';
                            if($str === null) {
                                $str = $value[$urlDimensions[0]];
                            }
                            $thirdDimWrapper = Html::tag('div', ['class' => 'cube-third-dim-wrapper'], strtoupper($str));
                            $dimensionEachList[$counter]->add($thirdDimWrapper);
                            $lastDim = $value[$urlDimensions[0]];
                        }
                    }

                    $dimensionEachList[$counter]->add($dimensionEachListHeader);

                    $lastValue = $value[$urlDimensions[$dimIndexCount-1]];
                }
            }
            //  if $value index is null, it is the subtotal, and we don't create a cube for that

            if ($arrayNodes->getRoot($rowCounter)) {

                $eachDimensionEl = Html::tag('div', ['class' => 'cube-each-dimension']);
                $innerHeader = Html::tag('div', ['class' => 'dimension-inner-header']);
                $innerBody = Html::tag('div', ['class' => 'dimension-inner-body']);
                $innerFooter = Html::tag('div', ['class' => 'dimension-inner-footer']);

                $innerHeader->add(new Link(ucfirst($arrayNodes->getRoot($rowCounter)), 'example/NOPAGE', ['class' => 'cube-link']));
                $innerHeader->add(new Link(new Icon('filter'), 'example/NOPAGE', ['class' => 'cube-filter-icon']));

                $innerBody->add(Html::tag('span', '199'));
                $innerFooter->add(Html::tag('span', ucfirst($arrayNodes->getEachCnt($rowCounter))));

                $eachDimensionEl->add($innerHeader);
                $eachDimensionEl->add($innerBody);
                $eachDimensionEl->add($innerFooter);

                if ($dimIndexCount === 0) $dimensionWrapper->add($eachDimensionEl);
                else  $dimensionEachList[$counter]->add($eachDimensionEl);
            }
            $rowCounter++;
        }

        if ($dimIndexCount === 0) {
            return $dimensionWrapper;
        }
        return $dimensionWrapper->add($dimensionEachList);
    }

    protected function assemble()
    {
       // $this->add(Html::tag('div',['class' => 'icingadb-cube'], $this->renderDimensions($this->getDimensions())));
        $this->add(Html::tag('div',['class' => 'icingadb-cube'], $this->renderDimensionsTestTwo($this->getDimensions())));
        // $this->renderDimensionsTest($this->getDimensions());
    }
}
