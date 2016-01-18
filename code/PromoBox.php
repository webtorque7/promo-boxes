<?php
class PromoBox extends DataObject
{

    public static $singular_name = 'Promo Box';
    public static $plural_name = 'Promo Boxes';

    public static $db = array(
                'Title' => 'Varchar(150)',
        );

    public static $belongs_many_many = array(
                'Pages' => 'Page',
        );

    public static $defaults = array(
                'ClassName' => 'ImagePromoBox'
        );

    public function searchableFields()
    {
        return array(
                        'Title' => array(
                                'title' => 'Title',
                                'filter' => 'PartialMatch'
                        ),
                        /*'Locale' => array(
                                'title' => 'Language',
                                'filter' => 'ExactMatch',
                                'field' => new LanguageDropdownField('Locale', 'Locale')
                        )*/
                        // leaves out the 'Price' field, removing it from the search
                );
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $boxes = array();

        if ($this->hasExtension('Translatable')) {
            $fields->removeByName('Locale');
            $fields->insertBefore(new LanguageDropdownField(
                                'Locale',
                                _t('CMSMain.LANGUAGEDROPDOWNLABEL', 'Language'),
                                array(),
                                'SiteTree',
                                'Locale-English',
                                singleton('SiteTree')), 'Title');

            $fields->removeByName('Translations');
            Translatable::set_current_locale($this->Locale);
        }
                
        foreach (ClassInfo::getValidSubClasses('PromoBox') as $boxClass) {
            if ($boxClass != 'PromoBox') {
                $instance = singleton($boxClass);
                $boxes[$boxClass] = $instance->i18n_singular_name();
            }
        }

        $fields->addFieldToTab('Root.Main', new DropdownField('ClassName', 'Type (save after changing this to see fields)', $boxes, 'ImagePromoBox'));
        $fields->removeByName('Pages');

        return $fields;
    }


    public function forTemplate()
    {
        return $this->renderWith($this->ClassName);
    }

    public function Display($pos)
    {
        return $this->customise(array('Pos' => $pos))->renderWith($this->ClassName);
    }

    public function getCSSClass()
    {
        return strtolower(str_replace(' ', '-', $this->i18n_singular_name()));
    }

        /**
         * @param Member $member
         * @return boolean
         */
        public function canView($member = null)
        {
            return Permission::check('CMS_ACCESS_PromoBoxesModelAdmin', 'any', $member);
        }

        /**
         * @param Member $member
         * @return boolean
         */
        public function canEdit($member = null)
        {
            return Permission::check('CMS_ACCESS_PromoBoxesModelAdmin', 'any', $member);
        }
}

class ImagePromoBox extends PromoBox
{
    public static $singular_name = 'Image Promo Box';

    public static $resize_method = 'CroppedImage';
    public static $width = 258;
    public static $height = 195;

    public static $db = array(
                'CaptionLarge' => 'Varchar(75)',
                'CaptionSmall' => 'Varchar(75)',
                'Link' => 'WTLink'

        );

    public static $has_one = array(
                'Image' => 'Image',
        );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeFieldsFromTab('Root.Main', array('Image', 'Caption', 'Link', 'Anchor'));
        $fields->addFieldsToTab('Root.Main', array(
                        new UploadField('Image'),
                        new TextField('CaptionLarge', 'Large Caption (large text at top)'),
                        new TextareaField('CaptionSmall', 'Large Caption (smaller text underneath)'),
                        new WTLinkField('Link', 'Link (optional)'),
                ));

        return $fields;
    }

    public function ResizedImage()
    {

                //hack, can't get the reverse object
                $class = Controller::curr()->ClassName;

        if (isset($class::$image_box_resize_method)) {
            $method = $class::$image_box_resize_method;

            switch ($method) {
                                case 'SetWidth' :
                                        return $this->Image()->getFormattedImage($method, $class::$image_box_width);
                                break;
                                case 'CroppedImage' :
                                        return $this->Image()->getFormattedImage($method, $class::$image_box_width, $class::$image_box_height);
                                break;

                                default: return $this->Image()->getFormattedImage(self::$resize_method, self::$width, self::$height);



                        }
        }

        return $this->Image()->getFormattedImage(self::$resize_method, self::$width, self::$height);
    }

    public function getPageLink()
    {
        if ($this->Link() && $this->Link()->exists()) {
            $link = $this->Link()->Link();
            if (!empty($this->Anchor)) {
                $link .= '#' . $this->Anchor;
            }

            return $link;
        }
        return false;
    }

    public function Caption()
    {
        return $this->CaptionLarge . ' - ' . $this->CaptionSmall;
    }

    public function getNiceCaptionSmall()
    {
        return (nl2br(Convert::raw2xml($this->CaptionSmall), true));
    }
}
