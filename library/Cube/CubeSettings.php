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

    protected function assemble()
    {
        $allDimensions = $this->getDimensions();
        // Combine for key access
        $allDimensions = array_combine($allDimensions, $allDimensions);
        $baseUrl = $this->getBaseUrl();
        $content = [];
        $dimensionsParam = $this->getDimensionsParam();
        foreach ($allDimensions as $dimension) {
            $dimensions = $allDimensions;
            unset($dimensions[$dimension]);
            $content[] = new Link(
                new Icon('cancel'),
                $baseUrl->with([$dimensionsParam => implode(',', $dimensions)])
            );
        }

        $this->add(Html::tag('ul', Html::wrapEach($content, 'li')));
    }
}
