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
use ipl\Web\Widget\ActionLink;
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
        $isSetSettingParam = (bool) $this->params->get('Showsettings');
        //$urlDimensions is null or string and (! $urlDimensions) is same as null
        $urlDimensions = $this->params->get('dimensions');

        $Header = Html::tag('h1',
            ['class' => 'dimension-header'],
            'Cube: '. str_replace(',', ' -> ', $urlDimensions)
        );
        $this->addControl($Header);

        $select = (new Select())
            ->columns('customvar.name')
            ->from('host')
            ->join('host_customvar','host_customvar.host_id = host.id')
            ->join('customvar','customvar.id = host_customvar.customvar_id')
            ->groupBy('customvar.name');

        $dimensions = $this->getDb()->select($select)->fetchAll(PDO::FETCH_COLUMN, 0);

        // remove already selected items from the option list
        foreach (explode(',', $urlDimensions) as $item) {
            if (($key = array_search($item, $dimensions)) !== false) {
                unset($dimensions[$key]);
            }
        }

        if(! $urlDimensions || $isSetSettingParam) {
            $showSettings = new ActionLink(
                $this->translate('Hide settings'),
                Url::fromRequest()->remove('Showsettings'),
                'wrench',
                ['data-base-target' => '_self']
            );
        } else {
            $showSettings = new ActionLink(
                $this->translate('Show settings'),
                Url::fromRequest()->addParams(['Showsettings' => 1]),
                'wrench',
                ['data-base-target' => '_self']
            );
        }
        $this->addControl($showSettings);

        $selectForm = (new SelectDimensionForm())
            ->on(SelectDimensionForm::ON_SUCCESS, function ($selectForm) use($urlDimensions) {
                if (! $urlDimensions) {
                    $toSetDimension = $selectForm->getValue('dimensions');
                } else {
                    $toAddParam = $selectForm->getValue('dimensions');
                    $toSetDimension = $urlDimensions . ',' . $toAddParam;
                }
                $this->redirectNow(Url::fromRequest()->with('dimensions', $toSetDimension));
            })
            ->setDimensions($dimensions)
            ->handleRequest(ServerRequest::fromGlobals());

        if (count(explode(',', $urlDimensions)) === 3) {
            $selectForm->remove($selectForm->getElement('dimensions'));
        }
        if ($isSetSettingParam || ! $urlDimensions) $this->addContent($selectForm);

        if ($urlDimensions) {
            $urlDimensions =  explode(',', $urlDimensions);

            $settings = (new CubeSettings())
                ->setBaseUrl(Url::fromRequest())
                ->setDimensions($urlDimensions);

            if ($isSetSettingParam) $this->addContent($settings);

            $select = (new Select())
                ->from('host h');

            $columns = [];
            foreach ($urlDimensions as $dimension) {
                $select
                    ->join(
                        "host_customvar {$dimension}_junction",
                        "{$dimension}_junction.host_id = h.id"
                    )
                    ->join(
                        "customvar {$dimension}",
                        "{$dimension}.id = {$dimension}_junction.customvar_id AND {$dimension}.name = \"{$dimension}\""
                    );

                $columns[$dimension] = $dimension . '.value';
            }

            $groupByValues = $columns;
            $columns['cnt'] = 'SUM(1)';
            $lastElmKey = array_key_last($groupByValues);
            $groupByValues[$lastElmKey] = $groupByValues[$lastElmKey] . ' WITH ROLLUP';


            $select
                ->columns($columns)
                ->groupBy($groupByValues);

            $rs = $this->getDb()->select($select)->fetchAll();
            $details = (new icingadbCubeRenderer($rs))->setDimensions($urlDimensions);
            $this->addContent($details);
        }
    }
}
