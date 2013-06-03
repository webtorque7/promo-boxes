<?php
class PromoBoxesModelAdmin extends ModelAdmin
{
        public static $menu_title = 'Promo Boxes';

        public static $url_segment = 'promo-boxes';

        public static $managed_models = array('PromoBox');

        static $menu_priority = 8;
}

