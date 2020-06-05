<?php

namespace Icinga\Module\Cube;

use Dompdf\Exception;
use http\Exception\InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;

class ArrayNodes
{

    protected $tree;

    protected $roots;

    protected $treeRootsCount;

    protected $rootChildren;

    protected $rootChildNodes;

    protected $dimensionCount;


    public function __construct(array $data)
    {
        //TODO FIX and throw exception
        if (! is_array($data) && ! empty(array_filter($data))) {

            throw new InvalidArgumentException('Passed argument must be an Array');
        }

        $dimensionKeys =  array_keys($data[0]);

        $tree = [];
        foreach ($data as $item) {
            if (count($dimensionKeys) === 2) $tree[$item[$dimensionKeys[0]]]['cnt'] = $item['cnt'];
            if (count($dimensionKeys) === 3) $tree[$item[$dimensionKeys[0]]][$item[$dimensionKeys[1]]]['cnt'] = $item['cnt'];
            if (count($dimensionKeys) === 4) $tree[$item[$dimensionKeys[0]]][$item[$dimensionKeys[1]]][$item[$dimensionKeys[2]]]['cnt'] = $item['cnt'];
        }
        $this->tree = $tree;
        $this->roots = array_filter(array_keys($this->tree));
        //$this->roots = array_keys($this->tree);
        $this->treeRootsCount = count($this->roots);
        $this->dimensionCount = count($dimensionKeys) - 1;

        $counter = 0;
        $tempArr = [];
        foreach ($dimensionKeys as $dimensionKey) {
            $keys = array_keys($this->tree[$this->roots[$counter]]);
            array_push($tempArr, $keys);
            $counter++;
        }
        $this->rootChildren = $tempArr;
    }

    /**
     * @param int $rootNr
     *
     * @return bool | null
     */
    public function hasChild($rootNr = 0)
    {
        if (is_array($this->tree[$this->roots[$rootNr]]))
            return ! empty($this->tree[$this->roots[$rootNr]]);
        return false;
    }

    /**
     * @param int $rootNr
     *
     * @param int $childNr
     * @return bool | null
     */
    public function hasGrandChild($rootNr = 0, $childNr = 0)
    {
        if (is_array($this->tree[$this->roots[$rootNr]][$this->rootChildren[$rootNr][$childNr]]))
            return ! empty($this->tree[$this->roots[$rootNr]][$this->rootChildren[$rootNr][$childNr]]);
        return false;
    }

    /**
     * @return array
     */
    public function getTree()
    {
        return $this->tree;
    }


    /**
     * @return array
     */
    public function getRoots()
    {
        return $this->roots;
    }

    /**
     * @param $rootNr
     * @return string
     */
    public function getRoot($rootNr = 0)
    {
        return $this->roots[$rootNr];
    }

    /**
     *
     * @param int $rootNr
     * @param int $childNr
     *
     * @return array
     */
    public function getRootChild($rootNr, $childNr)
    {

        return $this->rootChildren[$rootNr][$childNr];
    }

    /**
     * @param int $rootNr
     *
     * @param int $childNr
     *
     * @return array
     */
    public function getRootGrandChild($rootNr = 0, $childNr= 0)
    {

        if ($this->hasGrandChild($rootNr, $childNr)) {
            $keys = array_keys($this->tree[$this->roots[$rootNr]][$this->rootChildren[$rootNr][$childNr]]);

            return $keys[$childNr];
        }
        return null;
    }


    /**
     * @param int $childNr
     *
     * @return array
     */
    public function getRootChildNodes($childNr = 0)
    {
        return $this->tree[$this->roots[$childNr]];
    }

    /**
     * @param int $rootNr
     *
     * @return array
     */
    public function getRootChildren($rootNr = null)
    {
        if (!$rootNr) return array_keys($this->tree[$this->roots[$rootNr]]);
        // array_filter(array_keys($this->tree[$this->roots[$rootNr]]));
        return array_keys($this->tree[$this->roots[$rootNr]]);
    }

    /**
     * @param int $rootNr
     *
     * @param int $childNr
     *
     * @param int $grandChild need if 3 dimensions
     *
     * @return integer
     */
    public function getEachCnt($rootNr, $childNr = null, $grandChild = 0)
    {
        if($this->getDimensionCount() == 1) return $this->getTree()[$this->getRoot($rootNr)]['cnt'];  // 1 dim
        if($this->getDimensionCount() == 2) return $this->getTree()[$this->getRoot($rootNr)][$this->getRootChild($rootNr, $childNr)]['cnt'];  // 2 dim
        if($this->getDimensionCount() == 3) return $this->getTree()[$this->getRoot($rootNr)][$this->getRootChild($rootNr, $childNr)][$this->getRootGrandChild($rootNr, $grandChild)]['cnt'];
        return null;
    }

    /**
     * @param int $rootNr
     *
     * @return integer
     */
    public function getSubTotal($rootNr = 0, $childNr = 0)
    {
        if($this->getDimensionCount() == 1) return $this->getTree()['']['cnt'];  // 1 dim
        if($this->getDimensionCount() == 2) return $this->getTree()[$this->getRoot($rootNr)][''];  // 2 dim
        if($this->getDimensionCount() == 3) return $this->getTree()[$this->getRoot($rootNr)][$this->rootChildren[$rootNr][$childNr]]['']['cnt'];  // 3 dim
        return null;
    }

    /**
     * @param int $rootNr
     *
     * @return integer
     */
    public function getGrandTotal($rootNr = null)
    {
        if($this->getDimensionCount() == 1) return $this->getSubTotal(); //1 dim
        if($this->getDimensionCount() == 2) return $this->getTree()['']['']['cnt']; // 2dim
        if($this->getDimensionCount() == 3) return $this->getTree()[$this->getRoot($rootNr)]['']['']['cnt'];  // 3 dim

        return null;
    }

    /**
     *
     * @return integer
     */
    public function getDimensionCount()
    {
        return $this->dimensionCount;
    }

    /**
     * @param int $rootNr
     *
     * @return integer
     */
    public function getRootChildrenCount($rootNr = 0)
    {
        return count(array_filter(array_keys($this->getTree()[$this->getRoot($rootNr)])));
    }



}
