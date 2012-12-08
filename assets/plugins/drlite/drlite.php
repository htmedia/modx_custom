<?php
/*
Rev 18_04_2011
*/
class drlite 
{
	var $drconfig;
		
		//-------------------------------------------------------------------------------------------------
		function drlite($drconfig, $input)
		{
			$this->drconfig = $drconfig;
			$this->ProcessContent($input);
		}
		//-------------------------------------------------------------------------------------------------
		function ImgSrcFileExists($img_attrib_src)
		{
			global $modx;
			$abs_img_file_name =  $modx->config['base_path'].$img_attrib_src;
			if (!file_exists($abs_img_file_name))
			{	
				return false;
			} 
			else 
			{
				$size = getimagesize($abs_img_file_name);
				$this->img_file_width = $size[0];
				$this->img_file_height = $size[1];
			}
			return $abs_img_file_name;
		}
		//-------------------------------------------------------------------------------------------------
		function CreateThumbFromImage($abs_img_file_name,$ThumbFile,$max_length)
		{
			global $modx;
			if (file_exists($ThumbFile)) return;
			include_once($modx->config['base_path'].DRLITE_PATH.'includes/Thumbnailer.class.php');
			$tn = new Thumbnailer($abs_img_file_name);
			$thumbOptions = array('maxLength'=>$max_length);
			//---------------------------------------------------------------------------------
			$tn->createThumb($thumbOptions,$ThumbFile);
			//---------------------------------------------------------------------------------
		}
		//-------------------------------------------------------------------------------------------------
		function SetTargetFilename($img_attrib_src,$th_width,$th_height)
		{
			$img_src_ext = substr(strrchr($img_attrib_src,'.'),1);
			$img_src_base_name = basename($img_attrib_src, ".".$img_src_ext);
			$prefix = "thm_"; 
			$dir = dirname($img_attrib_src);
			$thmb_file_name = str_replace("://", "---", $dir);
			$thmb_file_name = str_replace("/", "--", $thmb_file_name);
			$thmb_file_name = $prefix.$thmb_file_name."--".$img_src_base_name."_".$img_src_ext."-".$th_width."_".$th_height.".".$img_src_ext;
            $dir_for_thumb = $this->createDir();
		    $thmb_file_name = $dir_for_thumb.$thmb_file_name;
			$this->targetFilename = $thmb_file_name;
		}
		//-------------------------------------------------------------------------------------------------
		function ThumbRequired()
		{
			if ($this->img_attrib_width == 0 && $this->img_attrib_height == 0) return false;
			if ($this->img_attrib_width == $this->img_file_width && $this->img_attrib_height == $this->img_file_height) return false;
			return true;
		}
		//-------------------------------------------------------------------------------------------------
		function ProcessCreateThumb($img_attrib_src,$th_width,$th_height,$max_length) 
		{
			$this->SetTargetFilename($img_attrib_src,$th_width,$th_height);
			$this->CreateThumbFromImage($img_attrib_src,$modx->config['base_path'].$this->targetFilename,$max_length);
				return $this->targetFilename;
		}
		//-------------------------------------------------------------------------------------------------
		function CheckAllowedExt($imgFile)
		{	
			$img_ext = strtolower(substr(strrchr($imgFile,'.'),1));
			if ($img_ext != "jpg" && $img_ext != "jpeg" && $img_ext != "png"  && $img_ext != "gif") return false;
			return true;
		}
		//-------------------------------------------------------------------------------------------------
		function getAbsPath($path)
		{
			global $modx;
			return strstr($path, "://") ? $path : $modx->config['base_path'].$path;
		}
		//-------------------------------------------------------------------------------------------------
		function checkPath($path)
		{
			global $modx;
			
			if (substr($path, 0, 1)=="/") $path = substr($path, 1, strlen($path));
			if (!strstr($path, "http://")) $path =$modx->config['base_path'].$path;
			if (!file_exists($path) && !strstr($path, "http://")) return false;
			$path = dirname($path);
			if (strstr($path, DRLITE_CACHE_DIR)) return true;
			if (!empty($this->drconfig['allow_from'])) 
			 {
				$pathArray =$this->drconfig['allow_from'];
				$mode = "allow";
			 }
			 else{
				$pathArray = $this->drconfig['deny_from'];
				$mode = "deny";
			 }
			foreach($pathArray as $p)
			{
				if (substr($p,strlen($p)-1,1) == "/") $p = substr($p,0 ,strlen($p)-1);
				if (substr($path,0,strlen($p)) == $p || substr($path,0,strlen($modx->config['base_path'].$p)) == $modx->config['base_path'].$p) return $mode == "allow" ? true : false;
			}
			return $mode == "allow" ? false : true;
		}
		//-------------------------------------------------------------------------------------------------
		function ParseImgAndCheckThumbSizes($img)
		{
			preg_match_all('/(src|height|width|alt|title|class|align|hspace|vspace|style)=("[^"]*")/i',$img, $img_tags,PREG_SET_ORDER);

			for ($i=0; $i< count($img_tags); $i++) {
			  $attr_name = $img_tags[$i][1];
			  $attr_value = str_replace('"', '',$img_tags[$i][2]);
				switch ($attr_name) { 
					case "src":
						  $img_HTML_src  = $attr_value; 
						  $this->img_HTML_src = $img_HTML_src; 
						 break; 
					case "height"; 
						 $img_HTML_Height = $attr_value;
						 $this->img_attrib_height = str_replace('"', '',$img_HTML_Height);
						  break; 
					case "width"; 
						 $img_HTML_Width = $attr_value;
						 $this->img_attrib_width = str_replace('"', '',$img_HTML_Width);

						  break; 
					case "alt"; 
						  $this->img_HTML_alt  = $attr_value; 

						  break; 
					case "title"; 
						  $this->img_HTML_title  = $attr_value; 
						  break; 
					case "class"; 
								 $this->img_HTML_class  = $attr_value; 

						  break; 
					case "align"; 
								  $this->img_HTML_align  = $attr_value; 
						  break; 
					case "hspace"; 
								  $this->img_HTML_hspace  = str_replace('"', '',$attr_value); 
						  break; 
					case "vspace"; 
								  $this->img_HTML_vspace  = str_replace('"', '',$attr_value); 
						  break; 
						  
					case "style"; 
								  $this->img_HTML_style  = $attr_value; 

						  break; 
			  }   
			  
			}	  
	
			  
			if ($img_HTML_Height>0 && $img_HTML_Width>0) return true;
			if ($img_HTML_Height>0 || $img_HTML_Width>0) return true;
			return false;
		}
		//-------------------------------------------------------------------------------------------------

		function ProcessContent($o)
		{
			global $modx;
			preg_match_all("/<img[^>]*>/", $o, $imgs, PREG_PATTERN_ORDER); 
			for($n=0;$n<count($imgs[0]);$n++)
			{
				$src_str = $imgs[0][$n];
				preg_match('/<img[^>]*?src="(.*?)"/si', $src_str, $img_attrib_src);
				$img_attrib_src = $img_attrib_src[1];
				$img_attrib_src = preg_replace("('|\")","",$img_attrib_src);
				$img_attrib_src = str_replace($modx->config[site_url], "", $img_attrib_src);
				$img_attrib_src = urldecode($img_attrib_src);
				preg_match('~\[(\+|\*|\()([^:\+\[\]]+)([^\[\]]*?)(\1|\))\]~s', $img_attrib_src, $matches);
				if (!empty($matches)) continue;
				if ( $this->checkPath($img_attrib_src) &&  $this->CheckAllowedExt($img_attrib_src))
				{

					$img = $imgs[0][$n];
					if ($this->ParseImgAndCheckThumbSizes($img))
					{
						$img_file_name = $this->ImgSrcFileExists($this->img_HTML_src);
					
						if ($this->ThumbRequired())
						{				
							$max_length = max($this->img_attrib_width,$this->img_attrib_height);
							$this->thumbPath = $this->ProcessCreateThumb($this->img_HTML_src,$this->img_attrib_width,$this->img_attrib_height,$max_length);
							$HTML = $lien_g[0].$this->thumbPath.$lien_d[0];
							$this->thumbWidth = $this->img_attrib_width;
							$this->thumbHeight = $this->img_attrib_height;
							//Здесь исходные размеры
							$this->bigWidth = $this->img_file_width;
							$this->bigHeight = $this->img_file_height;
							$this->bigPath = $img_attrib_src;
							//Здесь надо проверить - надо ли эффект вписывать
							$HTML = $this->ParseTemplate($imgs[0][$n], $n);
							$o = str_replace($imgs[0][$n],$HTML,$o);	
						}
						$this->imgCounter++;
					}
				}
			}
			$this->output = $o;
		}
		//-------------------------------------------------------------------------------------------------
		function ParseTemplate($tpl, $currId)
		{
			global $modx, $_lang;
			if (!class_exists('DRChunkie')) 
			{
				$chunkieclass = $modx->config['base_path'].DRLITE_PATH.'includes/chunkie.class.inc.php';
				if (file_exists($chunkieclass)) include_once $chunkieclass;
			}	
			$drtemplate = new DRChunkie($this->drconfig['tpl']);

			$tpldata['id'] = $currId;
			$tpldata['thumbWidth'] =$this->thumbWidth;
			$tpldata['thumbHeight'] =$this->thumbHeight;
			$tpldata['thumbPath'] = $this->thumbPath;
			
			$tpldata['bigWidth'] =$this->bigWidth;
			$tpldata['bigHeight'] =$this->bigHeight;
			$tpldata['bigPath'] = $this->bigPath;
			$tpldata['alt'] = $this->img_HTML_alt;
			$tpldata['title'] = $this->img_HTML_title;
			if ($this->img_HTML_class != NULL) {	
				$tpldata['class'] = $this->img_HTML_class; 
				}
			if ($this->img_HTML_align != NULL) {	
				$tpldata['align'] = $this->img_HTML_align;
				}
			if ($this->img_HTML_style != NULL) {	
				$tpldata['style'] = $this->img_HTML_style;
			}	

			if ($this->img_HTML_hspace != NULL) {	
				$tpldata['hspace'] = $this->img_HTML_hspace;
				}
				
			if ($this->img_HTML_vspace != NULL) {	
				$tpldata['vspace'] = $this->img_HTML_vspace;
				}
			
			$drtemplate->addVar('dr', $tpldata);
		
			unset($this->thumbWidth, $this->thumbHeight, $this->thumbPath, $this->bigWidth, $this->bigHeight, $this->bigPath, $this->img_HTML_alt, $this->img_HTML_title, $this->img_HTML_class, $this->img_HTML_vspace, $this->img_HTML_hspace, $this->img_HTML_align, $this->img_HTML_style
			
			);
		
			return $drtemplate->Render();
	}
	//-------------------------------------------------------------------------------------------------
	function CreateDir()
		{
			global $modx;
			$path_to_gal = DRLITE_CACHE_PATH.$this->drconfig['docID']."/";
			if (is_dir($modx->config['base_path'].$path_to_gal)) return $path_to_gal;
				$old_umask = umask(0);
				if(!mkdir($modx->config['base_path'].$path_to_gal, 0777)) {
					$output = 'Directory creation failed!'; 
					return;
				}
				umask($old_umask);
			return $path_to_gal;
		}
	//-------------------------------------------------------------------------------------------------
}

	//-------------------------------------------------------------------------------------------------
	function SureRemoveDir($dir, $DeleteMe = false) 
	// ggarciaa at gmail dot com (04-July-2007 01:57)	
	{
		if(!$dh = @opendir($dir)) return;
		while (false !== ($obj = readdir($dh))) 
		{
			if($obj=='.' || $obj=='..') continue;
			if (!@unlink($dir.'/'.$obj)) SureRemoveDir($dir.'/'.$obj, false);
		}
		if ($DeleteMe)
		{
			closedir($dh);
			@rmdir($dir);
		}
	}
//-------------------------------------------------------------------------------------------------

	function ClearDRCache($clearCache = 0)

/*
OnCacheUpdate - после обновления кэша
Right after the cache file is written.
*/
	{
		global $modx;
		// Ничего не чистим
		if ($clearCache == 0 ) return;
		if ($clearCache == 1 && isset($_REQUEST[id])) 
		{
		// Очистка кэша текущей папки - оптимально?
			SureRemoveDir($modx->config['base_path'].DRLITE_CACHE_PATH.$_REQUEST[id]);
		}
		if ($clearCache == 2) 
		{
		// Очистка всего кэша плагина
			SureRemoveDir($modx->config['base_path'].DRLITE_CACHE_DIR);
		}
	}
//-------------------------------------------------------------------------------------------------

	function RenderOnFrontend($o, $config)
/*
OnWebPagePrerender
*/
	{
		global $modx, $_lang;
		if (isset($config)) include_once $modx->config['base_path'].DRLITE_PATH."configs/$config.config.php";
		$drconfig['allow_from'] = isset($allow_from) ? $allow_from : (isset($deny_from) ? NULL : "assets/images");
		$drconfig['deny_from'] = isset($deny_from) && !isset($allow_from) ? $deny_from : NULL;
		$drconfig['docID'] = $modx->isBackend() ? $_REQUEST[id] : $modx->documentIdentifier;
		$drconfig['tpl'] = (isset($tpl)) ? $tpl : '';
		if (!empty($drconfig['allow_from']))
		{
			$drconfig['allow_from'] = str_replace(" ", "", $drconfig['allow_from']);
			$drconfig['allow_from'] = urldecode($drconfig['allow_from']);
			$drconfig['allow_from'] = explode(",", $drconfig['allow_from']);
		}
		else
			if (!empty($drconfig['deny_from']))
			{
				$drconfig['deny_from'] = str_replace(" ", "", $drconfig['deny_from']);
				$drconfig['deny_from'] = urldecode($drconfig['deny_from']);
				$drconfig['deny_from'] = explode(",", $drconfig['deny_from']);
			}
	//-----------------------------------------------------------------------------
			$direct = new drlite($drconfig, $o);
	//-----------------------------------------------------------------------------
         
   // Работать должен только во фронтенд (вставка скриптов в заголовок) 
		if (isset($header) && !$modx->isBackend() && $direct->imgCounter>0) 
		{
			$head = strstr($direct->output, "</head>") ? "</head>" : "</HEAD>";
			$direct->output = str_replace($head, $header."\n".$head, $direct->output);
		}
		return $direct->output;
	}

?>