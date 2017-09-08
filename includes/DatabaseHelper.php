<?php 

	class DatabaseHelper
	{
		private $db;

		public static function getInstance()
		{
			$instance = null;
			if($instance == null)
			{
				$instance = new DatabaseHelper();
			}  
			return $instance;
		}

		public function updateBukmekersList($bukmekersList)
		{
			foreach($bukmekersList as $singleBukmekerItem)
			{
				/*
				   make select request by name
				   if no result then add 
				   if exist result then 
				   check time
				   if time more than time from result then update 
				   if not more 
				   than dont update
				*/

				$bukmeker = $this->getBukmekerByName($singleBukmekerItem["title"]);

				if($bukmeker->num_rows < 1)
				{
					$this->addBukmeker($singleBukmekerItem);


					if($singleBukmekerItem["title"] == "Фонбет")
					{
						return 1;
					}
					
					if($singleBukmekerItem["title"] == "Марафон")
					{
						return 2;
					}

					if($singleBukmekerItem["title"] == "William Hill")
					{
						return 3;
					}

					if($singleBukmekerItem["title"] == "Pinnacle")
					{
						return 4;
					}

					if($singleBukmekerItem["title"] == "Олимп")
					{
						return 5;
					}

					if($singleBukmekerItem["title"] == "bet365")
					{
						return 6;
					}


					if($singleBukmekerItem["title"] == "ПариМатч")
					{
						return 7;
					}

					return $singleBukmekerItem["title"];
				}
				else
				{
					$existingBukmekerRow = $bukmeker->fetch_assoc();

					$existingBukmekerTime = $existingBukmekerRow["time"];
					$parsedTime = $singleBukmekerItem["time"];

					if($existingBukmekerTime != $parsedTime)
					{
						$this->updateBukmeker($singleBukmekerItem);
					}

					return $existingBukmekerRow["id"];
				}
			}
		}

		public function __construct()
		{
			$this->db = new mysqli("localhost", "root", "", "bukmekers_database");

			/* Проверка соединения */
			if (mysqli_connect_errno()) {
				printf("Подключение не удалось: %s\n", mysqli_connect_error());
				exit();
			}
		}

		private function getBukmekerByName($bukmekerName)
		{
			return $this->db->query("select * from bukmekers WHERE name='" . $bukmekerName . "'");

		}

		public function addBukmeker($bukmeker)
		{
			$sql = "insert into bukmekers(name,url,time) VALUES('" . $bukmeker["title"] . "','" . $bukmeker["url"] . "','" . $bukmeker["time"] . "')";

			$result = $this->db->query($sql);

			return $this->db->insert_id;
		}

		public function updateBukmeker($bukmeker)
		{
			$sql = "update bukmekers set url='".$bukmeker["url"]. "',time='" . $bukmeker["time"] . "' WHERE name='". $bukmeker["title"] ."'";

			$this->db->query($sql);
		}
	}
?>