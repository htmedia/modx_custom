/**
 * mm_demo_rules
 * 
 * Default ManagerManager rules. Should be modified for your own sites.
 * 
 * @category	chunk
 * @version 	1.0.5
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal 	@modx_category Demo Content
 * @internal    @overwrite false
 * @internal    @installset base, sample
 */

// more example rules are in assets/plugins/managermanager/example_mm_rules.inc.php

// example of how PHP is allowed - check that a TV named documentTags exists before creating rule
if($modx->db->getValue("SELECT COUNT(id) FROM " . $modx->getFullTableName('site_tmplvars') . " WHERE name='documentTags'")) {
    mm_widget_tags('documentTags',' '); // Give blog tag editing capabilities to the 'documentTags (3)' TV
}

mm_widget_showimagetvs(); // Always give a preview of Image TVs

mm_renameField('log', 'Дочерние ресурсы отображаются в дереве');
mm_changeFieldHelp('log', 'Это поле используется для папок с большим числом вложенных страниц');

mm_createTab('SEO', 'seo');
mm_moveFieldsToTab('seo_title,seo_keywords,seo_description,seo_noindex,sitemap_priority,sitemap_changefreq,seo_canonical', 'seo');

//mm_widget_evogallery(2, 'Галерея');

//mm_hideFields('longtitle,description,introtext,link_attributes,menutitle,menuindex,show_in_menu,parent,alias,template,content', '', '6');
