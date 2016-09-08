<?php

class Blob
{

	protected $db;

	public function __construct()
	{
		$db = new Pdo('mysql:host=localhost; dbname=jira_notification', 'root', '');
		//$db->exec('CREATE TABLE IF NOT EXISTS blob (id int unsigned, updated_at datetime, primary key name)');
		//create table notification (id int unsigned, updated_at datetime, primary key(id)) engine = innodb;
		$this->db = $db;
	}

	public function get($name)
	{
		$stmt = $this->db->prepare('SELECT * FROM notification WHERE id = :id');
		$stmt->bindValue(':id', $name);
		$stmt->execute();

		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function insert($id, $status)
	{
		$stmt = $this->db->prepare('INSERT IGNORE INTO notification(id,updated_at) VALUES(:id, :status)');
		$stmt->bindValue(':id', $id);
		$stmt->bindValue(':status', $status);
		$stmt->execute();
	}

	public function update($name, $status)
	{
		$stmt = $this->db->prepare('UPDATE notification SET updated_at = :status WHERE id = :id');
		$stmt->bindValue(':id', $name);
		$stmt->bindValue(':status', $status);
		$stmt->execute();
	}

}
