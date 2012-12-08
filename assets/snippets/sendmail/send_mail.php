<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta charset=utf-8" />
</head>

<body>

	<form action="" method="POST" enctype="multipart/form-data">
		Имя<br />
		<input type="text" name="uname" size="40" /><br />
		Фамилия<br />
		<input type="text" name="ulastname" size="40" /><br />
		Email<br />
		<input type="text" name="uemail" size="40" /><br />
		Описание<br />
		<textarea name="udescription" cols="35" rows="5"></textarea><br />
		Прикрепленный файл<br />
		<input type="file" name="attachfile" size="32" /><br /><br />
		<input type="submit" name="submit" value="Отправить" />
	</form>

	<?php
	//Функция отправки почты
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
	
	//Пример использования функции
	//Проверяем нажал ли пользователь на кнопку отправить
	if (isset($_POST['submit']))
		{		
		//Получаем заполненные пользователем данные и записываем их для удобства в переменные
		$uname = $_POST['uname'];
		$ulastname = $_POST['ulastname'];
		$description = $_POST['udescription'];
		$uemail = $_POST['uemail'];
		
		//Формируем данные для письма
		$subj = 'Письмо с сайта'; //Тема письма
		$mail = "name@test.ru"; //Куда отправлять
		//Текст сообщения
		$message = "Пользователь $ulastname $uname. <br />Прислал описание $description";
		
		//Вызываем функцияю
		send_mail($mail, $uemail, '', $subj, $message, 'attachfile');
		echo '<p>Письмо отправлено</p>';
		
		/*Поясню параметры функции
		Кому
		От кого [email]
		От кого [имя]
		Тема письма
		Текст письма
		Имя поля из формы для прикрепленных файлов
		*/
		}
	?>
</body>
</html>