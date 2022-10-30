<?php

require "../../vendor/autoload.php";

if(isset($_POST['filter']))
{
	// получаем данные и возвращаем список деклараций в случае успеха или информацию об ошибке
	try
	{
		$fsa = new app\Fsa();
		$fsa->login();
		$readyFilter = $fsa->prepareFilter($_POST['filter']);
		$declarations = $fsa->getDeclarations($readyFilter['size'], 0, $readyFilter['filter']);
		
		$result = ['result' => 'ok', 'items' => $declarations];
	}
	catch (Exception $e) 
	{
		$result = ['result' => 'error', 'errorMsg' => 'Ошибка! '.$e->getMessage()];
	}
	
	$answer = json_encode($result);
		
	echo $answer;
}