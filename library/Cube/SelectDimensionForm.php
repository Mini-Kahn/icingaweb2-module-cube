<?php

namespace Icinga\Module\Cube;

use ipl\Web\Compat\CompatForm;

class SelectDimensionForm extends CompatForm
{
    protected $method = 'GET';

    /** @var array Available dimensions */
    protected $dimensions;

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

    protected function assemble()
    {
        $this->addElement('select', 'dimensions', [
            'class' => 'autosubmit',
            'label' => 'Dimension',
            'options' => array_combine($this->getDimensions(), $this->getDimensions())
        ]);
    }
}