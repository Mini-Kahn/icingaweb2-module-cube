<?php

namespace Icinga\Module\Cube\Controllers;

use GuzzleHttp\Psr7\ServerRequest;
use Icinga\Module\Cube\Common\IcingaDb;
use Icinga\Module\Cube\CubeSettings;
use Icinga\Module\Cube\icingadbCubeRenderer;
use Icinga\Module\Cube\SelectDimensionForm;
use ipl\Html\Html;
use ipl\Sql\Select;
use ipl\Web\Compat\CompatController;
use ipl\Web\Url;
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

        $urlDimensions = $this->params->get('dimensions');
        $Header = Html::tag('h1',
            ['class' => 'dimension-header'],
            'Cube: '. str_replace(',', ' -> ', $this->params->get('dimensions'))
        );
        $this->addContent($Header);

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



        if ($urlDimensions) {
            $urlDimensions =  explode(',', $urlDimensions);

            $settings = (new CubeSettings())
                ->setBaseUrl(Url::fromPath('cube/icingadb'))
                ->setDimensions($urlDimensions);
            $this->addContent($settings);

            $select = (new Select())
                ->from('host h');
            $columns = [];
            foreach ($urlDimensions as $dimension) {
                $select
                    ->join("host_customvar {$dimension}_junction","{$dimension}_junction.host_id = h.id")
                    ->join("customvar {$dimension}","{$dimension}.id = {$dimension}_junction.customvar_id AND {$dimension}.name = \"{$dimension}\"");

                $columns[$dimension] = $dimension . '.value';
            }

            $groupByValues = $columns;
            $lastElmKey = array_key_last($columns);

            $groupByValues[$lastElmKey] = $columns[$lastElmKey] . ' WITH ROLLUP';
            $columns['cnt'] = 'SUM(1)';

            $select
                ->columns($columns)
                ->groupBy($groupByValues);

            $rs = $this->getDb()->select($select)->fetchAll();
            $details = (new icingadbCubeRenderer($rs))->setDimensions($urlDimensions);
            $this->addContent($details);
        }
    }
}
