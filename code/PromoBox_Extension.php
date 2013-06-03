<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 7
 * Date: 4/06/13
 * Time: 9:33 AM
 * To change this template use File | Settings | File Templates.
 */

class PromoBox_Extension extends SiteTreeExtension
{
	public static $many_many = array(
		'PromoBoxes' => 'PromoBox'
	);

	public static $many_many_extraFields = array(
		'PromoBoxes' => array(
			'SortOrder' => 'Int'
		)
	);


	public static $box_limit = 3;

	public function updateCMSFields(FieldList $fields) {
		if ($this->stat('box_limit')) {
			$fields->insertAfter(new Tab('PromoBoxes'), 'Main');

			//content boxes
			$boxes = PromoBox::get();
			$aBoxes = $this->PromoBoxes()->toArray();

			$dropBoxes = $boxes->map();

			for ($i = 0; $i < $this->stat('box_limit'); $i++) {
				$value = !empty($aBoxes[$i]) ? $aBoxes[$i]->ID : null;

				$fields->addFieldToTab('Root.PromoBoxes', new DropdownField("PromoBoxes[{$i}]", 'Promo Box ' . ($i + 1), $dropBoxes, $value, null, 'No Box'));
			}
		}
	}

	public function PromoBoxes() {
		$limit = ($this->owner->hasMethod('getBoxLimit')) ? $this->getBoxLimit() : $this->owner->stat('box_limit');
		return	$this->getManyManyComponent('PromoBoxes')->sort('SortOrder ASC');
	}

	public function onAfterWrite() {
		//save promo boxes
		if (isset($_POST['PromoBoxes'])) {
			if ($this->PromoBoxes()->count()) foreach ($this->PromoBoxes() as $box) {
				$this->ContentBoxes()->remove($box);
			}
			foreach ($_POST['PromoBoxes'] as $index => $box) {
				if (!empty($box)) {
					$this->PromoBoxes()->add($box, array('SortOrder' => $index));
				}
			}
		}
	}
}