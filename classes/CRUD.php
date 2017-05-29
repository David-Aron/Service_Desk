<?php
require_once 'Database.php';

abstract class CRUD extends Database 
{
    protected $table;
    abstract public function insert();
    abstract public function update($id);

    //Algumas funções básicas pra serem herdadas por outras classes

    public function find($field, $value){
        $sql = "SELECT * FROM $this->table WHERE ".$field." = :value";
        $stmt = Database::prepare($sql);
        $stmt -> bindParam(':value', $value, PDO::PARAM_INT);
        $stmt -> execute();
        return $stmt->fetchAll();
    }

    public function findAll(){
        $sql = "SELECT * FROM $this->table";
        $stmt = Database::prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function deleteField($field, $id){
        $sql = "DELETE FROM $this->table WHERE ".$field." = :id";
        $stmt = Database::prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

	public function setDBSingleField($idfield, $id, $field, $value){
		$sql = "UPDATE $this->table SET ".$field." = :value WHERE ".$idfield." = :id";
        $stmt = Database::prepare($sql);
		$stmt->bindParam(':id', $id);
        $stmt->bindParam(':value', $value);

        $stmt->execute();
	}
}