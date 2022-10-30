<?php

namespace app;

class Fsa
{
	const USERNAME = 'anonymous';
	const PASSWORD = 'hrgesf7HDR67Bd';
	const LOGIN_URL = 'https://pub.fsa.gov.ru/login';
	const DECLARATIONS_URL = 'https://pub.fsa.gov.ru/api/v1/rds/common/declarations/get';
	// Названия статусов
	const STATUS_NAMES = [1 => "Архивный", 3 => "Возобновлён", 5 => "Выдано предписание", 6 => "Действует", 10 => "Направлено уведомление о прекращении", 11 => "Недействителен", 14 => "Прекращён", 15 => "Приостановлен"];
	
	private $authorization_token = null;
	
	// получаем токен авторизации
	function login()
	{
		$curl = new \fl\curl\Curl([
			CURLOPT_TIMEOUT        => 10,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_RETURNTRANSFER    => true,
			CURLINFO_HEADER_OUT    => true,
			CURLOPT_SSL_VERIFYPEER    => false,
		]);

		$responsePost = $curl
			->setBody([
				'username' => self::USERNAME,
				'password' => self::PASSWORD,
			], true)
			->post(self::LOGIN_URL);

		// если без ошибки, то устанавливаем токен иначе делаем исключение с описанием ошибки
		if($responsePost->errorCode === 0)
			$this->authorization_token = $responsePost->headers['authorization'][0];	
		else
			throw new Exception('Code: '.$responsePost->errorCode.', text: '.$responsePost->errorText.' description: '.$responsePost->errorDesc);
	}
	
	// получаем данные о декларациях на основе фильтров
	function getDeclarations(int $size, int $page, array $filter)
	{
		$curl = new \fl\curl\Curl([
			CURLOPT_TIMEOUT        => 10,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_RETURNTRANSFER    => true,
			CURLINFO_HEADER_OUT    => true,
			CURLOPT_SSL_VERIFYPEER    => false,
		]);	

		$responsePost = $curl
			->setHeader('Authorization', $this->authorization_token)
			->setBody([
		  "size" => $size,
		  "page" => $page,
		  "filter" => $filter		
			], true)
			->post(self::DECLARATIONS_URL);
		
		// если без ошибки, то возвращаем отформатированный список, иначе делаем исключение с описанием ошибки
		if($responsePost->errorCode === 0)
			return $this->getItems($responsePost->body);	
		else
			throw new Exception('Code: '.$responsePost->errorCode.', text: '.$responsePost->errorText.' description: '.$responsePost->errorDesc);			
				
	}
	
	// готовим фильтр на основе данных из формы
	function prepareFilter(string $filterJson)
	{
		$filter = json_decode($filterJson);
		
		$size = empty($filter->size) ? 25 : $filter->size;
		
		$readyFilter = [];
		
		// заполняем соответствиющие поля только если по ним нужно отфильтровать
		if(!empty($filter->state))
			$readyFilter['status'] = [$filter->state];

		if(!empty($filter->number))
		{
			$readyFilter['columnsSearch'] = [[
				"name" => "number",
				"search" => $filter->number,
				"type" => 0,
				"translated" => false
			  ]];
		}
		
		$registrationDateS = empty($filter->registrationDateS) ? null : date('Y-m-d', strtotime($filter->registrationDateS));
		$registrationDatePo = empty($filter->registrationDatePo) ? null : date('Y-m-d', strtotime($filter->registrationDatePo));

		$readyFilter['regDate'] = [ "minDate" => $registrationDateS, "maxDate" => $registrationDatePo ];
				
		$endDateS = empty($filter->endDateS) ? null : date('Y-m-d', strtotime($filter->endDateS));
		$endDatePo = empty($filter->endDatePo) ? null : date('Y-m-d', strtotime($filter->endDatePo));
		
		$readyFilter['endDate'] = [ "minDate" => $endDateS, "maxDate" => $endDatePo ];
	
		return ["size" => $size, "filter" => $readyFilter];
	}
	
	// оставляем в списке деклараций только нужные поля и преобразуем данные где нужно в более читаемый вид
	function getItems(string $dataJson)
	{
		$result = [];
		
		$data = json_decode($dataJson);
		
		if(isset($data->items))
		{
			foreach($data->items as $item)
			{
				$result[] = [
					"id" => $item->id, 
					"idStatus" => $item->idStatus,
					"statusName" => self::STATUS_NAMES[$item->idStatus], 
					"number" => $item->number, 
					"registrationDate" => date('d.m.Y', strtotime($item->declDate)), 
					"endDate" => date('d.m.Y', strtotime($item->declEndDate)), 
					"productName" => $item->productFullName, 
					"applicantName" => $item->applicantName, 
					"manufacterName" => $item->manufacterName,
					"productOrigin" => $item->productOrig, 
					"objectType" => $item->declObjectType
				];
			}
		}

		return $result;
	}	
}