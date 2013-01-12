<?php
// cache & synchronise class

class synccache{
    var $cachePath;
    var $showReport;
    var $deletedfiles = array();
    var $aliases = array();
    var $parents = array();

    function setCachepath($path) {
        $this->cachePath = $path;
    }

    function setReport($bool) {
        $this->showReport = $bool;
    }

    function escapeDoubleQuotes($s) {
        $q1 = array("\\","\"","\r","\n","\$");
        $q2 = array("\\\\","\\\"","\\r","\\n","\\$");
        return str_replace($q1,$q2,$s);
    }

    function escapeSingleQuotes($s) {
        $q1 = array("\\","'");
        $q2 = array("\\\\","\\'");
        return str_replace($q1,$q2,$s);
    }

    function getParents($id, $path = '') { // modx:returns child's parent
        global $modx;
        if(empty($this->aliases)) {
            $sql = "SELECT id, IF(alias='', id, alias) AS alias, parent FROM ".$modx->getFullTableName('site_content');
            $qh = $modx->db->query($sql);
            if ($qh && $modx->db->getRecordCount($qh) > 0)  {
                while ($row = $modx->db->getRow($qh)) {
                    $this->aliases[$row['id']] = $row['alias'];
                    $this->parents[$row['id']] = $row['parent'];
                }
            }
        }
        if (isset($this->aliases[$id])) {
            $path = $this->aliases[$id] . ($path != '' ? '/' : '') . $path;
            return $this->getParents($this->parents[$id], $path);
        }
        return $path;
    }

    function emptyCache($modx = null) {
        if((function_exists('is_a') && is_a($modx, 'DocumentParser') === false) || get_class($modx) !== 'DocumentParser') {
            $modx = $GLOBALS['modx'];
        }
        if(!isset($this->cachePath)) {
            echo "Cache path not set.";
            exit;
        }
        $filesincache = 0;
        $deletedfilesincache = 0;
        if (function_exists('glob')) {
            // New and improved!
            $files = glob(realpath($this->cachePath).'/*');
            $filesincache = count($files);
            $deletedfiles = array();
            while ($file = array_shift($files)) {
                $name = basename($file);
                if (preg_match('/\.pageCache/',$name) && !in_array($name, $deletedfiles)) {
                    $deletedfilesincache++;
                    $deletedfiles[] = $name;
                    unlink($file);
                }
            }
        } else {
            // Old way of doing it (no glob function available)
            if ($handle = opendir($this->cachePath)) {
                // Initialize deleted per round counter
                $deletedThisRound = 1;
                while ($deletedThisRound){
                    if(!$handle) $handle = opendir($this->cachePath);
                    $deletedThisRound = 0;
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                            $filesincache += 1;
                            if ( preg_match("/\.pageCache/", $file) && (!is_array($deletedfiles) || !array_search($file,$deletedfiles)) ) {
                                $deletedfilesincache += 1;
                                $deletedThisRound++;
                                $deletedfiles[] = $file;
                                unlink($this->cachePath.$file);
                            } // End if
                        } // End if
                    } // End while
                    closedir($handle);
                    $handle = '';
                } // End while ($deletedThisRound)
            }
        }

        $this->buildCache($modx);

/****************************************************************************/
/*  PUBLISH TIME FILE                                                       */
/****************************************************************************/

        // update publish time file
        $timesArr = array();
        $sql = 'SELECT MIN(pub_date) AS minpub FROM '.$modx->getFullTableName('site_content').' WHERE pub_date>'.time();
        if(@!$result = $modx->db->query($sql)) {
            echo 'Couldn\'t determine next publish event!';
        }

        $tmpRow = $modx->db->getRow($result);
        $minpub = $tmpRow['minpub'];
        if($minpub!=NULL) {
            $timesArr[] = $minpub;
        }

        $sql = 'SELECT MIN(unpub_date) AS minunpub FROM '.$modx->getFullTableName('site_content').' WHERE unpub_date>'.time();
        if(@!$result = $modx->db->query($sql)) {
            echo 'Couldn\'t determine next unpublish event!';
        }
        $tmpRow = $modx->db->getRow($result);
        $minunpub = $tmpRow['minunpub'];
        if($minunpub!=NULL) {
            $timesArr[] = $minunpub;
        }

        if(count($timesArr)>0) {
            $nextevent = min($timesArr);
        } else {
            $nextevent = 0;
        }

        // write the file
        $filename = $this->cachePath.'/sitePublishing.idx.php';
        $somecontent = '<?php $cacheRefreshTime='.$nextevent.'; ?>';

        if (!$handle = fopen($filename, 'w')) {
             echo 'Cannot open file ('.$filename.')';
             exit;
        }

        // Write $somecontent to our opened file.
        if (fwrite($handle, $somecontent) === FALSE) {
           echo 'Cannot write publishing info file! Make sure the assets/cache directory is writable!';
           exit;
        }

        fclose($handle);


/****************************************************************************/
/*  END OF PUBLISH TIME FILE                                                */
/****************************************************************************/

        // finished cache stuff.
        if($this->showReport==true) {
        global $_lang;
            printf($_lang['refresh_cache'], $filesincache, $deletedfilesincache);
            $limit = count($deletedfiles);
            if($limit > 0) {
                echo '<p>'.$_lang['cache_files_deleted'].'</p><ul>';
                for($i=0;$i<$limit; $i++) {
                    echo '<li>',$deletedfiles[$i],'</li>';
                }
                echo '</ul>';
            }
        }
    }

    /**
     * build siteCache file
     * @param  DocumentParser $modx
     * @return boolean success
     */
    function buildCache($modx) {
        $tmpPHP = "<?php\n";

        // SETTINGS & DOCUMENT LISTINGS CACHE

        // get settings
        $sql = 'SELECT * FROM '.$modx->getFullTableName('system_settings');
        $rs = $modx->db->query($sql);
        $limit_tmp = $modx->db->getRecordCount($rs);
        $config = array();
        $tmpPHP .= '$c=&$this->config;'."\n";
        while(list($key,$value) = $modx->db->getRow($rs,'num')) {
            $tmpPHP .= '$c[\''.$key.'\']'.' = "'.$this->escapeDoubleQuotes($value)."\";\n";
            $config[$key] = $value;
        }

        // get aliases modx: support for alias path
        
/*	Modified by thebat053
*	CacheExtender
*	CacheExtender revision:2
*/
$this->processDocumentCacheExtended($modx, $tmpPHP, $config);
/*$tmpPath = '';
        $tmpPHP .= '$this->aliasListing = array();' . "\n";
        $tmpPHP .= '$a = &$this->aliasListing;' . "\n";
        $tmpPHP .= '$d = &$this->documentListing;' . "\n";
        $tmpPHP .= '$m = &$this->documentMap;' . "\n";
        $sql = 'SELECT IF(alias=\'\', id, alias) AS alias, id, contentType, parent FROM '.$modx->getFullTableName('site_content').' WHERE deleted=0 ORDER BY parent, menuindex';
        $rs = $modx->db->query($sql);
        $limit_tmp = $modx->db->getRecordCount($rs);
        for ($i_tmp=0; $i_tmp<$limit_tmp; $i_tmp++) {
            $tmp1 = $modx->db->getRow($rs);
            if ($config['friendly_urls'] == 1 && $config['use_alias_path'] == 1) {
                $tmpPath = $this->getParents($tmp1['parent']);
                $alias= (strlen($tmpPath) > 0 ? "$tmpPath/" : '').$tmp1['alias'];
                $alias= $modx->db->escape($alias);
                $tmpPHP .= '$d[\''.$alias.'\']'." = ".$tmp1['id'].";\n";
            }
            else {
                $tmpPHP .= '$d[\''.$modx->db->escape($tmp1['alias']).'\']'." = ".$tmp1['id'].";\n";
            }
            $tmpPHP .= '$a[' . $tmp1['id'] . ']'." = array('id' => ".$tmp1['id'].", 'alias' => '".$modx->db->escape($tmp1['alias'])."', 'path' => '" . $modx->db->escape($tmpPath)."', 'parent' => " . $tmp1['parent']. ");\n";
            $tmpPHP .= '$m[]'." = array('".$tmp1['parent']."' => '".$tmp1['id']."');\n";
        }*/


        // get content types
        $sql = 'SELECT id, contentType FROM '.$modx->getFullTableName('site_content')." WHERE contentType != 'text/html'";
        $rs = $modx->db->query($sql);
        $limit_tmp = $modx->db->getRecordCount($rs);
        $tmpPHP .= '$c = &$this->contentTypes;' . "\n";
        for ($i_tmp=0; $i_tmp<$limit_tmp; $i_tmp++) {
           $tmp1 = $modx->db->getRow($rs);
           $tmpPHP .= '$c['.$tmp1['id'].']'." = '".$tmp1['contentType']."';\n";
        }

        // WRITE Chunks to cache file
        $sql = 'SELECT * FROM '.$modx->getFullTableName('site_htmlsnippets');
        $rs = $modx->db->query($sql);
        $limit_tmp = $modx->db->getRecordCount($rs);
        $tmpPHP .= '$c = &$this->chunkCache;' . "\n";
        for ($i_tmp=0; $i_tmp<$limit_tmp; $i_tmp++) {
           $tmp1 = $modx->db->getRow($rs);
           $tmpPHP .= '$c[\''.$modx->db->escape($tmp1['name']).'\']'." = '".$this->escapeSingleQuotes($tmp1['snippet'])."';\n";
        }

        // WRITE snippets to cache file
        $sql = 'SELECT ss.*,sm.properties as `sharedproperties` '.
                'FROM '.$modx->getFullTableName('site_snippets').' ss '.
                'LEFT JOIN '.$modx->getFullTableName('site_modules').' sm on sm.guid=ss.moduleguid';
        $rs = $modx->db->query($sql);
        $limit_tmp = $modx->db->getRecordCount($rs);
        $tmpPHP .= '$s = &$this->snippetCache;' . "\n";
        for ($i_tmp=0; $i_tmp<$limit_tmp; $i_tmp++) {
           $tmp1 = $modx->db->getRow($rs);
           $tmpPHP .= '$s[\''.$modx->db->escape($tmp1['name']).'\']'." = '".$this->escapeSingleQuotes($tmp1['snippet'])."';\n";
           // Raymond: save snippet properties to cache
           if ($tmp1['properties']!=""||$tmp1['sharedproperties']!="") $tmpPHP .= '$s[\''.$tmp1['name'].'Props\']'." = '".$this->escapeSingleQuotes($tmp1['properties']." ".$tmp1['sharedproperties'])."';\n";
           // End mod
        }

        // WRITE plugins to cache file
        $sql = 'SELECT sp.*,sm.properties as `sharedproperties`'.
                'FROM '.$modx->getFullTableName('site_plugins').' sp '.
                'LEFT JOIN '.$modx->getFullTableName('site_modules').' sm on sm.guid=sp.moduleguid '.
                'WHERE sp.disabled=0';
        $rs = $modx->db->query($sql);
        $limit_tmp = $modx->db->getRecordCount($rs);
        $tmpPHP .= '$p = &$this->pluginCache;' . "\n";
        for ($i_tmp=0; $i_tmp<$limit_tmp; $i_tmp++) {
           $tmp1 = $modx->db->getRow($rs);
           $tmpPHP .= '$p[\''.$modx->db->escape($tmp1['name']).'\']'." = '".$this->escapeSingleQuotes($tmp1['plugincode'])."';\n";
           if ($tmp1['properties']!=''||$tmp1['sharedproperties']!='') $tmpPHP .= '$p[\''.$tmp1['name'].'Props\']'." = '".$this->escapeSingleQuotes($tmp1['properties'].' '.$tmp1['sharedproperties'])."';\n";
        }


        // WRITE system event triggers
        $sql = 'SELECT sysevt.name as `evtname`, pe.pluginid, plugs.name
                FROM '.$modx->getFullTableName('system_eventnames').' sysevt
                INNER JOIN '.$modx->getFullTableName('site_plugin_events').' pe ON pe.evtid = sysevt.id
                INNER JOIN '.$modx->getFullTableName('site_plugins').' plugs ON plugs.id = pe.pluginid
                WHERE plugs.disabled=0
                ORDER BY sysevt.name,pe.priority';
        $events = array();
        $rs = $modx->db->query($sql);
        $limit_tmp = $modx->db->getRecordCount($rs);
        $tmpPHP .= '$e = &$this->pluginEvent;' . "\n";
        for ($i=0; $i<$limit_tmp; $i++) {
            $evt = $modx->db->getRow($rs);
            if(!$events[$evt['evtname']]) $events[$evt['evtname']] = array();
            $events[$evt['evtname']][] = $evt['name'];
        }
        foreach($events as $evtname => $pluginnames) {
            $tmpPHP .= '$e[\''.$evtname.'\'] = array(\''.implode("','",$this->escapeSingleQuotes($pluginnames))."');\n";
        }

        // close and write the file
        $tmpPHP .= "\n";
        $filename = $this->cachePath.'siteCache.idx.php';
        $somecontent = $tmpPHP;

        // invoke OnBeforeCacheUpdate event
        if ($modx) $modx->invokeEvent('OnBeforeCacheUpdate');

        if (!$handle = fopen($filename, 'w')) {
             echo 'Cannot open file (',$filename,')';
             exit;
        }

        // Write $somecontent to our opened file.
        if (fwrite($handle, $somecontent) === FALSE) {
           echo 'Cannot write main MODx cache file! Make sure the assets/cache directory is writable!';
           exit;
        }
        fclose($handle);

        // invoke OnCacheUpdate event
        if ($modx) $modx->invokeEvent('OnCacheUpdate');

        return true;
    }
/*
*	Modified by thebat053
*	CacheExtender 0.1a
*	CacheExtender revision:2
*/
    var $childs = array(); //modified by thebat053

    function getParentsCacheExtended($id, $path = '') { // modx:returns child's parent
        global $modx;
		if(!isset($this->childs)) $this->childs = array();
        if(empty($this->aliases)) {
            $sql = "SELECT id, IF(alias='', id, alias) AS alias, parent FROM ".$modx->getFullTableName('site_content');
            $qh = $modx->db->query($sql);
            if ($qh && $modx->db->getRecordCount($qh) > 0)  {
                while ($row = $modx->db->getRow($qh)) {
                    $this->aliases[$row['id']] = $row['alias'];
                    $this->parents[$row['id']] = $row['parent'];
					if(isset($row['parent'])){
						if(isset($this->childs[$row['parent']]))
							$this->childs[$row['parent']] .= '||'.$row['id'];
						else
							$this->childs[$row['parent']] = $row['id'];
					}
                }
            }
        }
        if (isset($this->aliases[$id])) {
            $path = $this->aliases[$id] . ($path != '' ? '/' : '') . $path;
            return $this->getParents($this->parents[$id], $path);
        }
        return $path;
    }
	
	function processDocumentCacheExtended($modx, &$tmpPHP, $config){
		$cacheMode = 'full'; //part or full. if full, array $d is indexed and cached too and you must replace all array_.. calls to $this->documentListing->array...
		$report = false;
		$cacheFileName = MODX_BASE_PATH.'assets/cache/siteCache.dat'; //name of the extended cache file
		$cacheFileNameUrl = MODX_BASE_PATH.'assets/cache/siteCacheUrl.dat'; //name of the extended cache file
		global $modx;
        if($report && $modx->checkSession()){
			echo("<br /><b>Cache Extended Active</b><br />");
			$start = $this->getMicroTime();
        }
        $tmpPath = '';
		include_once('cache_sync.create.class.php');
		$extCache = new CacheFill();
		if($cacheMode == 'part')
			$d = '$d = array(';
		else
			$extCacheUrl = new CacheFillUrl();
		$this->getParentsCacheExtended(-1); //precache childs ids $this->childs

		//caching top of tree
		if(isset($this->childs[0]))
			$childs = explode('||', $this->childs[0]);
		else
			$childs = array();
		$extCache->addIndex(0, $extCache->add(serialize(array(0, 'index', '', 0, $childs))));

        $sql = 'SELECT IF(alias=\'\', id, alias) AS alias, id, contentType, parent FROM '.$modx->getFullTableName('site_content').' WHERE deleted=0 ORDER BY parent, menuindex';
        $rs = $modx->db->query($sql);
        $limit_tmp = $modx->db->getRecordCount($rs);
        for ($i_tmp=0; $i_tmp<$limit_tmp; $i_tmp++) {
            $tmp1 = $modx->db->getRow($rs);
            if ($config['friendly_urls'] == 1 && $config['use_alias_path'] == 1) {
                $tmpPath = $this->getParentsCacheExtended($tmp1['parent']);
                $alias= (strlen($tmpPath) > 0 ? "$tmpPath/" : '').$tmp1['alias'];
                $alias= $modx->db->escape($alias);
				if($cacheMode == 'part')
					$d .= '"'.(string)$alias.'"=>'.$tmp1['id'].',';
            	else
					$extCacheUrl->addIndex($alias, $tmp1['id']);
			}
            else {
				if($cacheMode == 'part')
                	$d .= '"'.(string)$modx->db->escape($tmp1['alias']).'"=>'.(int)$tmp1['id'].',';
            	else
					$extCacheUrl->addIndex($modx->db->escape($tmp1['alias']), $tmp1['id']);
			}
			if(isset($this->childs[$tmp1['id']]))
				$childs = explode('||', $this->childs[$tmp1['id']]);
			else
				$childs = array();
			$extCache->addIndex((int)$tmp1['id'], $extCache->add(serialize(array($tmp1['id'], $modx->db->escape($tmp1['alias']), $modx->db->escape($tmpPath), $tmp1['parent'], $childs))));
        }
		$extCache->flush($cacheFileName);
		$tmpPHP .= '$cacheFileName = "'.$cacheFileName.'";'."\n";
		if($cacheMode == 'part'){
			$tmpPHP .= $d.');'."\n";
		} else {
			$extCacheUrl->flush($cacheFileNameUrl);
			$tmpPHP .= '$cacheFileNameUrl = "'.$cacheFileNameUrl.'";'."\n";
		}
		$tmpPHP .= '$cacheMode = "'.$cacheMode.'";'."\n";
		$tmpPHP .= "\n"."include(MODX_BASE_PATH.'manager/processors/cache_sync.wrapper.class.php');"."\n";
        if($report && $modx->checkSession()){
   			$totalTime= ($this->getMicroTime() - $start);
        	$totalTime= sprintf("%2.4f s", $totalTime);
			echo("<b>Reindex completed!</b> Pages processed: ".$limit_tmp.", Total time: ".$totalTime.", Memory used: ".(memory_get_peak_usage(true) / 1024 / 1024)." MB<br /><br />");
		}
    }
	
    function getMicroTime() {
        list ($usec, $sec)= explode(' ', microtime());
        return ((float) $usec + (float) $sec);
    }
}
?>