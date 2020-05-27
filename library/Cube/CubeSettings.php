<?php

namespace Icinga\Module\Cube;

use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;
use ipl\Web\Url;
use ipl\Web\Widget\Icon;
use ipl\Web\Widget\Link;

class CubeSettings extends BaseHtmlElement
{
    /** @var Url */
    protected $baseUrl;

    /** @var array */
    protected $dimensions = [];

    /** @var string */
    protected $dimensionsParam = 'dimensions';

    protected $defaultAttributes = ['class' => 'cube-settings'];

    protected $tag = 'div';

    /**
     * @return Url
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param Url $baseUrl
     *
     * @return $this
     */
    public function setBaseUrl(Url $baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * @return array
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * @param array $dimensions
     *
     * @return $this
     */
    public function setDimensions($dimensions)
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    /**
     * @return string
     */
    public function getDimensionsParam()
    {
        return $this->dimensionsParam;
    }

    /**
     * @param string $dimensionsParam
     *
     * @return $this
     */
    public function setDimensionsParam($dimensionsParam)
    {
        $this->dimensionsParam = $dimensionsParam;

        return $this;
    }

    /**
     * @param int $indexToMove index value of array value that has to be moved
     *
     * @param boolean $isDirectionUp move direction
     *
     * @return array swapped associative array
     */
    protected function swapArray($indexToMove, $isDirectionUp) {
        $myDimensions = $this->getDimensions();
        if($isDirectionUp) {
            $tempVal = $myDimensions[$indexToMove-1];
            $myDimensions[$indexToMove-1] = $myDimensions[$indexToMove];
            $myDimensions[$indexToMove] = $tempVal;

            return array_combine($myDimensions, $myDimensions);
        }
        $tempVal = $myDimensions[$indexToMove+1];
        $myDimensions[$indexToMove+1] = $myDimensions[$indexToMove];
        $myDimensions[$indexToMove] = $tempVal;

        return array_combine($myDimensions, $myDimensions);
    }

    protected function assemble()
    {
        $allDimensions = $this->getDimensions();
        // Combine for key access
        $allDimensions = array_combine($allDimensions, $allDimensions);
        $baseUrl = $this->getBaseUrl();
        $content = [];
        $dimensionsParam = $this->getDimensionsParam();
        $indexCounter = 0;
        foreach ($allDimensions as $dimension) {
            $dimensions = $allDimensions;
            unset($dimensions[$dimension]);
            $element = Html::tag('div');
            $element->add(new Link(
                new Icon('cancel'),
                !empty($dimensions) ? $baseUrl->with([$dimensionsParam => implode(',', $dimensions)]) : $baseUrl->with([])
            ));
            if($indexCounter) {
                $element->add(new Link(
                    new Icon('angle-double-up'),
                    !empty($dimensions) ? $baseUrl->with([$dimensionsParam => implode(',', $this->swapArray($indexCounter, true))]) : $baseUrl->with([])
                ));
            } else { //TODO (SD) fix this workaround, class is doing the trick here
                $element->add(Html::tag('span',['class' => 'dimension-name']));
            }
            if ($indexCounter < 2) {
                $element->add(new Link(
                    new Icon('angle-double-down'),
                    !empty($dimensions) ? $baseUrl->with([$dimensionsParam => implode(',', $this->swapArray($indexCounter,false))]) : $baseUrl->with([])
                ));
            } else { //TODO (SD) fix this workaround, class is doing the trick here
                $element->add(Html::tag('span',['class' => 'dimension-name']));
            }

            $element->add(Html::tag('span',['class' => 'dimension-name'], $dimension));
            $content[] = $element;
            $indexCounter++;
        };

        $this->add(Html::tag('ul', Html::wrapEach($content, 'li')));
    }
}
