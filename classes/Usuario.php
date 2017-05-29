<?php
require_once 'CRUD.php';

class Usuario extends CRUD {
    protected $table = 'usuarios';
    private $idusuario;
	private $nome;
	private $login;
    private $senha;
	private $datacadastro;
    private $tipousuario;
    private $qntchamadas;

    //Construtor
    public function __construct($idusuario="", $nome="", $login="", $senha="", $tipousuario="") //Variáveis são opcionais pra facilitar o acesso dos métodos nessa classe
    {
        $this->idusuario = $idusuario;
        $this->nome = $nome;
        $this->login = $login;
        $this->senha = $senha;
        $this->tipousuario = $tipousuario;
    }

    //Getters e setters
    public function getNome()
    {
        return $this->nome;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function setLogin($login)
    {
        $this->login = $login;
    }

    public function getSenha()
    {
        return $this->senha;
    }

    public function setSenha($senha)
    {
        $this->senha = $senha;
    }

    public function getDatacadastro()
    {
        return $this->datacadastro;
    }

    public function setDatacadastro($datacadastro)
    {
        $this->datacadastro = $datacadastro;
    }

    public function getTipousuario()
    {
        return $this->tipousuario;
    }

    public function setTipousuario($tipousuario)
    {
        $this->tipousuario = $tipousuario;
    }

    public function getIdusuario()
    {
        return $this->idusuario;
    }

    public function setIdusuario($idusuario)
    {
        $this->idusuario = $idusuario;
    }

    public function getQntchamadas()
    {
        return $this->qntchamadas;
    }

    public function setQntchamadas($qntchamadas)
    {
        $this->qntchamadas = $qntchamadas;
    }

  
    //Método de cadastro de usuário
    public function insert()
    {
        $sql = "INSERT INTO $this->table(nome, login, senha, datacadastro, qntchamadas) VALUES(:nome, :login, :senha, NOW(), 0)";
        $stmt = Database::prepare($sql);
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':login', $this->login);
        $stmt->bindParam(':senha', $this->senha);
        $stmt->execute();

        if($stmt->rowCount() == 1) {
            if($this->tipousuario == 1){ //Se é um usuário comum, terminamos o cadastro
                return true; //Retorna que conseguimos cadastrar
            }
            else{ //Inacabado. No caso, aqui seria utilizado pra cadastrar um técnico ou outro administrador
                $id = Database::lastInsertId();
                // echo "Usuário ".$resultado['idusuario']." registrado!";
            }
        }
    }

    //Método de login de usuário
	public function login()
	{
		$sql = "SELECT idusuario FROM $this->table WHERE login = :login AND senha = :senha";
		$stmt = Database::prepare($sql);
        $stmt->bindParam(':login', $this->login);
        $stmt->bindParam(':senha', $this->senha);
        $stmt -> execute();
		
		if($stmt->rowCount() == 1){
			$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['TMW_usuario'] = $usuario['idusuario']; //Cria uma sessão com o ID do usuário. Optei por somente sessão invés de cookie junto, pois achei desnecessário (por enquanto)
			return true; //Retorna que conseguimos realizar login
		}
		else{
			return false; //Retorna que houve algum erro nas informações
		}
	}

	//Método pra pegar no BD as informações do usuário, alterando o próprio objeto usuário e criando um array pra facilitar o uso das informações
    public function usuarioGetData(){
        $sql = "SELECT u.idusuario, u.nome, u.login, u.datacadastro, u.qntchamadas FROM $this->table AS u WHERE u.idusuario = :idusuario"; //Selecionei com atalho u. pra facilitar depois os prováveis joins
        $stmt = Database::prepare($sql);
        $stmt->bindParam(':idusuario', $this->idusuario);
        $stmt->execute();
        if($stmt->rowCount() == 1){
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC); //array
            $usuario['datacadastro'] = date('d-m-Y', strtotime(str_replace('-','/', $usuario['datacadastro']))); //Convertendo a data do bd pro padrão brasileiro

            $usuario['administrador'] = $this->usuarioCheckTipo("administradores"); //Conferindo se é um administrador
            $usuario['tecnico'] = $this->usuarioCheckTipo("tecnicos"); //Conferindo se é um técnico

            //Passando as variáveis pro objeto usuario
            $this->setNome($usuario['nome']);
            $this->setLogin($usuario['login']);
            $this->setQntchamadas($usuario['qntchamadas']);
            $this->setDatacadastro($usuario['datacadastro']);

            return $usuario; //Retornando o array
        }
    }

    //Método para conferir se um usuário é administrador ou técnico
    public function usuarioCheckTipo($table){
        $sql = "SELECT idusuario FROM $table WHERE idusuario = :idusuario";
        $stmt = Database::prepare($sql);
        $stmt->bindParam(':idusuario', $this->idusuario);
        $stmt->execute();
        if($stmt->rowCount() == 1){ //Se encontramos algum resultado
            if($table == "tecnicos"){return $this->tecnicoGetCategoria();} //Se estamos lidando com um técnico, já aproveitamos pra pegar sua categoria
            else{return 1;}
        }
        else{return 0;}
    }

    //Método pra pegar a categoria de um técnico
    public function tecnicoGetCategoria(){
        $sql = "SELECT idcategoria FROM tecnicos WHERE idusuario = :idusuario";
        $stmt = Database::prepare($sql);
        $stmt->bindParam(':idusuario', $this->idusuario);
        $stmt->execute();
        $tecnico = $stmt->fetch();
        $tecnico = json_decode(json_encode($tecnico),true); //Transforma o objeto em array
        return($tecnico['idcategoria']);
    }

    //Métodos pra pegar o número de chamados encaminhados por adm e resolvidos por técnicos. Não seria muito difícil juntar os dois pra eliminar a repetição de código. //Conferir depois
    public function admGetQntEncaminhados(){
        $sql = "SELECT count(idusuario) AS quantidade FROM chamados WHERE idadministrador = :idusuario";
        $stmt = Database::prepare($sql);
        $stmt->bindParam(':idusuario', $this->idusuario);
        $stmt->execute();
        $usuario = $stmt->fetch();
        $usuario = json_decode(json_encode($usuario),true); //Transforma o objeto em array
        return($usuario['quantidade']);
    }

    //Explicação acima
    public function tecGetQntResolvidos(){
        $sql = "SELECT count(idusuario) AS quantidade FROM chamados WHERE idtecnico = :idusuario AND estado = 2";
        $stmt = Database::prepare($sql);
        $stmt->bindParam(':idusuario', $this->idusuario);
        $stmt->execute();
        $usuario = $stmt->fetch();
        $usuario = json_decode(json_encode($usuario),true); //Transforma o objeto em array
        return($usuario['quantidade']);
    }

    //Método para 'catar' os técnicos em uma lista simples ou SM (small)
    public function fetchTecnicosSM(){
        $sql = "SELECT u.idusuario, u.nome FROM $this->table AS u INNER JOIN tecnicos AS t ON u.idusuario=t.idusuario";
        $stmt = Database::prepare($sql);
        $stmt->execute();
        $tecnicos = $stmt->fetchAll();
        $tecnicos = json_decode(json_encode($tecnicos), true); //Transforma o objeto em array
        return $tecnicos;
    }

    //Placeholder pro método de atualizar os dados de usuário. Seria usado pra edição de perfil e possivelmente atualização de categoria de técnico
    public function update($id){
        //Não inclusa pois ainda não tem necessidade
    }
}