//<?php
/**
 * feedback
 * 
 * Send feedback
 *
 * @category snippet
 * @version 1.0
 * @internal @modx_category Content
 * @internal @properties &usite=Адрес сайта;text; &uemail=Email;text;htmedia@mail.ru
 */
 
include_once 'assets/snippets/sendmail/send_mail.php';

echo '<div id="feedback">';
if (!isset($_POST['submit'])) {
	echo '
		<form action="" method="POST">
			<div>Ваше имя<span>*</span>:<br />
			<input name="name" type="text" /></div>
			
			<div>Ваш E-mail<span>*</span>:<br />
			<input name="email" type="text" /></div>
			
			<div>Тема сообщения<span>*</span>:<br />
			<input name="subj" type="text" /></div>
			
			<div>Сообщение<span>*</span>:<br />
			<textarea name="message"></textarea></div>
			
			<div><input type="submit" name="submit" value="Отправить" /></div>
		</form>
	';
}
else {
	if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['subj']) || empty($_POST['message'])) {
		echo '<p>Вы заполнили не все обязательные поля</p><input type="button" value="Вернуться" onclick="history.back();">';
	}
	else {
		$site = $usite;
		$mail = $uemail;
		$from = $site;
		$subj = 'Сообщение с сайта ' . $site;
		$message = '
			<strong>Сообщение с сайта: </strong><br />
			Имя: ' . $_POST['name'] . '<br />
			E-mail: ' . $_POST['email'] . '<br />
			Тема: ' . $_POST['subj'] . '<br />
			Сообщение: ' .  $_POST['message'] . '<br />
			';
		
		send_mail($mail, '', $from, $subj, $message);
		echo '<p>Ваше сообщение успешно отправлено.</p>';
	}
}
echo '<div class="clear"></div>';
echo '</div>';