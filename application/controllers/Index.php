<?php

namespace application\controllers;

use application\models\User;

use Framework\Registry;

use Framework\Controller;

class Index extends Controller {
	
/**
	 * @once
	 * @protected
	 */
	public function init()
	{
		echo "<p>init</p>";
	}
	
	/**
	 * @protected
	 */
	public function authenticate()
	{
		echo "<p>authenticate</p>";
	}
	
	/**
	 */
	public function index()
	{
		$db = Registry::get("database");
		
		$all = $db->query()
			->from("users", array(
					"first_name",
					"last_name" => "surname"
					))
					->join("points", "points.id=users.id", array(
							"points" => "rewards"
					))
			->all();
		
		var_dump($all);
		
		/**
		/*$id = $db->query()
			->from("users")
			->save(array(
					"first_name" => "Philip",
					"last_name" => "Pitt"
					));
			
		echo "insert de id : $id";
		*/
		
		
		$affected = $db->query()
		->from("users")
		->where("first_name=?", "Liz")
		->delete();
		
		echo "suppression de $affected rows ";
		
		
		
		$db->query()
		->from("users")
		->where("first_name=?", "Patrick")
		->save(array(
				"modified" => date("Y-m-d H:i:s")
				));
		
		
		$count = $db->query()
		->from("users")
		->count();
		
		echo "There are $count rows ";
		
		
		$user = new User(array(
				"connector" => $db
				));
	
		$db->sync($user);
		
		echo "<p>here x)</p>";	
	}
	
	/**
	 * @protected
	 */
	public function notify()
	{
		echo "<p>notify</p>";
	}
}
