<?php

namespace Icinga\Module\Cube\Controllers;

use GuzzleHttp\Psr7\ServerRequest;
use Icinga\Module\Cube\Common\IcingaDb;
use Icinga\Module\Cube\SelectDimensionForm;
use ipl\Sql\Select;
use ipl\Web\Compat\CompatController;
use PDO;

/**
 * TODOs:
 *
 * - Database connection to Icinga DB
 * - Welche custom vars gibt es für unsere Hosts? (Das ist eine Query gegen die Icinga DB)
 * select c.name from host h inner join host_customvar hc on hc.host_id = h.id inner join customvar c on c.id = hc.customvar_id group by c.name;
 * - ipl Form erstellen, die die custom var Liste von oben anzeigt
 * - Nach der Auswahl einer custom var, werden die unterschiedlichen Werte dieser custom var gezählt und angezeigt
 * select c.value, sum(1) from host h inner join host_customvar hc on hc.host_id = h.id inner join customvar c on c.id = hc.customvar_id where c.name = "app" group by c.value;
 * select c.value, sum(1) from host h inner join host_customvar hc on hc.host_id = h.id inner join customvar c on c.id = hc.customvar_id where c.name = "app" group by c.value with rollup;
 */
class IcingadbController extends CompatController
{
    use IcingaDb;

    public function indexAction()
    {
        $this->setTitle('Icinga DB Host Cube');

        $select = (new Select())
            ->columns('name')
            ->from('host');

        $dimensions = $this->getDb()->select($select)->fetchAll(PDO::FETCH_COLUMN, 0);

        $form = (new SelectDimensionForm())
            ->setDimensions($dimensions)
            ->handleRequest(ServerRequest::fromGlobals());

        $this->addContent($form);

        if ($this->params->has('dimensions')) {
            $dimensions = $this->params->get('dimensions');
        }
    }
}
