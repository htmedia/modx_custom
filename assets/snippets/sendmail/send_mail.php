<?php
function send_mail($to="", $from_mail="", $from_name="", $thm="", $html="", $attachment="", $kod="utf-8"){
		$razd=explode("|", $attachment);
		$go=-1;
		for($i=0;$i<count($razd);$i++){
			if(!empty($razd[$i])){
				$go++;
				if(file_exists($razd[$i])){
					$upfile[$go]=$razd[$i];
					$upfile_name[$go]=basename($razd[$i]);
				}
				if(!file_exists($razd[$i])){
					$tfile=$razd[$i];
					$upfile[$go]=$_FILES["$tfile"]["tmp_name"];
					$upfile_name[$go]=$_FILES["$tfile"]["name"];
				}
				@$fp=fopen($upfile[$go],"r");
				if($fp){
					$file[$go]=fread($fp, filesize($upfile[$go]));
					fclose($fp);
				}
			}
		}
		$boundary = "--".md5(uniqid(time()));
		$headers="from: \"$from_name\" <$from_mail>\n";
		$headers.="to: $to\n";
		$headers.="subject: $thm\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .="Content-Type: multipart/mixed; boundary=\"$boundary\"\n";
		$multipart .= "--$boundary\n";
		$multipart .= "Content-Type: text/html; charset=$kod\n";
		$multipart .= "Content-Transfer-Encoding: Quot-Printed\n\n";
		$multipart .= "$html \n\n";
		for($i=0;$i<=$go;$i++){
			$message_part .= "--$boundary\n";
			$message_part .= "Content-Type: application/octet-stream;name=\"$upfile_name[$i]\"\n";
			$message_part .= "Content-Transfer-Encoding: base64\n";
			$message_part .= "Content-Disposition: attachment; filename = \"$upfile_name[$i]\"\n\n";
			$message_part .= chunk_split(base64_encode($file[$i]))."\n";
		}
		$multipart .= $message_part."--$boundary--\n";
		if(!mail($to, $thm, $multipart, $headers)){
			echo "К сожалению, письмо не отправлено";
			exit();
		}
	}
?>