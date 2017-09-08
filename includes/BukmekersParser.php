<?php 
	// disable notices
	ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

	require_once "HttpHelper.php";
	require_once "SimpleHtmlDom.php";

	class BukmekersParser
	{
		private $bukmekersMirrorUrl = "https://bk-info27.online/zerkala_bukmekerov.php";

		private $mainUrl = "https://bk-info27.online";

		public static function getInstance()
		{
			$instance = null;
			if($instance == null)
			{
				$instance = new BukmekersParser();
			}  
			return $instance;
		}

		/*
			Extract bookmaker name from string like "Зеркало ПариМатч (Parimatch)"
		*/
		public function getBookmakerNameFromString($bookmakerString)
		{
			mb_internal_encoding("UTF-8");

			$firstSubstr = mb_substr($bookmakerString, 8);

			$explodedSubstr = explode(" (", $firstSubstr);

			return $explodedSubstr[0];
		}

		/* 
		    returns parsed bukmekers list
		*/
		public function getBukmekersList()
		{
			$result = array();

			$bukmekersMirrorPageHtml = $this->getHtml("https://bk-info27.online/zerkala_bukmekerov.php");

			// get bookmakers list from page https://bk-info27.online/zerkala_bukmekerov.php
			$bukmekersList = $this->parseBukmekersList($bukmekersMirrorPageHtml);

			$bukmekersLinksToMirrorsLinks = array();

			if(!$bukmekersList)
			{
				return;
			}

			foreach($bukmekersList as $singleBukmekerListItem)
			{
				$bukmekersLinksToMirrorsLinks[] = $this->mainUrl . $singleBukmekerListItem["url"];
			}

			foreach($bukmekersLinksToMirrorsLinks as $singleMirrorLink)
			{
				$mirrorPageHtml = $this->getHtml($singleMirrorLink);

				// get url to button
				$mirrorUrl = $this->parseMirrorUrl($mirrorPageHtml);

				// get last url from location
				$lastUrl = $this->httpHelper->getLastUrl($this->mainUrl . $mirrorUrl, true);

				for($i = 0; $i < count($bukmekersList); $i++)
				{
					if(($this->mainUrl . $bukmekersList[$i]["url"]) == $singleMirrorLink)
					{
						$current_url = explode('?', $lastUrl);
						$parsedUrl = parse_url($current_url[0]);

						// delete url line, leave only host
						$lastUrlMade = $parsedUrl["scheme"] . "://" . $parsedUrl["host"];
						
						$bukmekersList[$i]["url"] = $lastUrlMade;

						$singleResult = array();

						$bukmekerId = $bukmekersList[$i]["title"];

						if($$bukmekersList[$i]["title"] == "Фонбет")
						{
							$bukmekerId = 1;
						}
					
						if($$bukmekersList[$i]["title"] == "Марафон")
						{
							$bukmekerId = 2;
						}

						if($$bukmekersList[$i]["title"] == "William Hill")
						{
							$bukmekerId = 3;
						}

						if($bukmekersList[$i]["title"] == "Pinnacle")
						{
							$bukmekerId = 4;
						}

						if($bukmekersList[$i]["title"] == "Олимп")
						{
							$bukmekerId = 5;
						}

						if($bukmekersList[$i]["title"] == "bet365")
						{
							$bukmekerId = 6;
						}

						if($bukmekersList[$i]["title"] == "ПариМатч")
						{
							$bukmekerId = 7;
						}

						$singleResult["id"] = $bukmekerId;
						$singleResult["link"] = $bukmekersList[$i]["url"];

						$result[] = $singleResult;
					}
				}
			}

			return $result;
		}

		private function extractTimeFromString($string)
		{
			// extract time string
			$regEx = "/\d{2}:\d{2}( )\d{2}.\d{2}.\d{4}/i";

			preg_match_all($regEx , $string, $matches);

			if(count($matches[0]) > 0)
			{
				return $matches[0][0];
			}

			return "";
		}

		private function getHtml($singleMirrorLink)
		{
			$html = $this->httpHelper->getHtml($singleMirrorLink);

			if($html == false)
			{
				return $this->getHtml($singleMirrorLink);
			}

			return $html;
		}

		private function parseMirrorUrl($mirrorPageHtml)
		{
			$html = str_get_html($mirrorPageHtml);
			$mirrorUrl = $html->find(".callout > div > p > a.green", 0)->href;

			return $mirrorUrl;
		}

		/*
			Parse mirrors by html code
		*/
		public function parseBukmekersList($bukmekersMirrorPageHtml)
		{
			$bukmekersList = array();
			$html = str_get_html($bukmekersMirrorPageHtml);

			foreach($html->find("#zerkala_bk > tbody > tr") as $singleMirrorObject)
			{
				$singleBukmekerItem = array();
				$title = $singleMirrorObject->find(".title a", 0)->innertext;

				if($title)
				{
					$singleBukmekerItem["title"] = $this->getBookmakerNameFromString($title);
				}
				else
				{
					continue;
				}

				$singleBukmekerItem["time"] = $this->extractTimeFromString(@$singleMirrorObject->find("td span", -1)->innertext);
				$singleBukmekerItem["url"] = $singleMirrorObject->find(".title a", 0)->href;

				$bukmekersList[] = $singleBukmekerItem;
			}

			return $bukmekersList;
		}

		public function __construct()
		{
			$this->httpHelper = HttpHelper::getHelper();
		}
	}


?>