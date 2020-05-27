<?php

namespace Icinga\Module\Cube\Controllers;

use GuzzleHttp\Psr7\ServerRequest;
use Icinga\Module\Cube\Common\IcingaDb;
use Icinga\Module\Cube\icingadbCubeRenderer;
use Icinga\Module\Cube\SelectDimensionForm;
use ipl\Sql\Select;
use ipl\Web\Compat\CompatController;
use PDO;

/**
 * Icingadb cube controller class
 */
class IcingadbController extends CompatController
{
    use IcingaDb;

    public function indexAction()
    {
        $this->setTitle('Icinga DB Host Cube');

        $select = (new Select())
            ->columns('customvar.name')
            ->from('host')
            ->join('host_customvar','host_customvar.host_id = host.id')
            ->join('customvar','customvar.id = host_customvar.customvar_id')
            ->groupBy('customvar.name');

        $dimensions = $this->getDb()->select($select)->fetchAll(PDO::FETCH_COLUMN, 0);

        $form = (new SelectDimensionForm())
            ->setDimensions($dimensions)
            ->handleRequest(ServerRequest::fromGlobals());

        $this->addContent($form);

        if ($this->params->has('dimensions')) {
            $dimensions =  explode(',', $this->params->get('dimensions'));

            $select = (new Select())
                ->from('host h');
            $columns = [];
            foreach ($dimensions as $dim) {
                $select
                    ->join("host_customvar {$dim}_junction","{$dim}_junction.host_id = h.id")
                    ->join("customvar {$dim}","{$dim}.id = {$dim}_junction.customvar_id AND {$dim}.name = \"{$dim}\"");

                $columns[$dim] = $dim . '.value';
            }

            $groupByValues = $columns;
            $lastElmKey = array_key_last($columns);

            $groupByValues[$lastElmKey] = $columns[$lastElmKey] . ' WITH ROLLUP' ;
            $columns['cnt'] = 'SUM(1)';

            $select
                ->columns($columns)
                ->groupBy($groupByValues);

            $rs = $this->getDb()->select($select)->fetchAll();
            $details = (new icingadbCubeRenderer($rs))->setDimensions($dimensions);
            $this->addContent($details);
        }
    }
}
