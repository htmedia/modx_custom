//<?php
/**
 * ManagerManager plugin
 * @version 0.4 (2012-11-14)
 * 
 * Customize the MODx Manager to offer bespoke admin functions for end users.
 *
 * @category plugin
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * 
 * @properties &remove_deprecated_tv_types_pref=Remove deprecated TV types;list;yes,no;yes &which_jquery=jQuery source;list;local (assets/js),remote (google code),manual url (specify below);local (assets/js) &jquery_manual_url=jQuery URL override;text; &config_chunk=Configuration Chunk;text;mm_rules;
 * @events OnDocFormRender,OnDocFormPrerender,OnBeforeDocFormSave,OnPluginFormRender,OnTVFormRender
 * @modx_category Manager and Admin
 * @legacy_names Image TV Preview, Show Image TVs
 * 
 * @link http://code.divandesign.biz/modx/managermanager/0.4
 * 
 * @copyright 2012
 */

// You can put your ManagerManager rules EITHER in a chunk OR in an external file - whichever suits your development style the best

// To use an external file, put your rules in /assets/plugins/managermanager/mm_rules.inc.php 
// (you can rename default.mm_rules.inc.php and use it as an example)
// The chunk SHOULD have php opening tags at the beginning and end

// If you want to put your rules in a chunk (so you can edit them through the Manager),
// create the chunk, and enter its name in the configuration tab.
// The chunk should NOT have php tags at the beginning or end

// ManagerManager requires jQuery 1.7+
// The URL to the jQuery library. Choose from the configuration tab whether you want to use 
// a local copy (which defaults to the jQuery library distributed with ModX 1.0.1)
// a remote copy (which defaults to the Google Code hosted version)
// or specify a URL to a custom location.

// You don't need to change anything else from here onwards
//-------------------------------------------------------

// Run the main code
include($modx->config['base_path'].'assets/plugins/managermanager/mm.inc.php');