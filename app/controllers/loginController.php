
<?php

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../helpers/usuario.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ?pagina=login');
    exit;
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';
$perfil = (($_POST['perfil'] ?? 'aluno') === 'professor') ? 'professor' : 'aluno';

if ($email === '' || $senha === '') {
    $_SESSION['erro_login'] = 'Informe seu e-mail e sua senha.';
    header('Location: ?pagina=login');
    exit;
}

try {
    garantir_coluna_tipo($conexao);

    $consulta = $conexao->prepare(
        'SELECT id_aluno, nome, email, senha, tipo
         FROM aluno
         WHERE email = :email AND ativo = 1
         LIMIT 1'
    );

    $consulta->execute(['email' => $email]);
    $aluno = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$aluno || !password_verify($senha, $aluno['senha'])) {
        $_SESSION['erro_login'] = 'E-mail ou senha inválidos.';
        header('Location: ?pagina=login');
        exit;
    }

    // O perfil escolhido na tela precisa ser o da conta.
    if ($aluno['tipo'] !== $perfil) {
        $_SESSION['erro_login'] = ($aluno['tipo'] === 'professor')
            ? 'Esta conta é de professor. Escolha "Professor" para entrar.'
            : 'Esta conta é de aluno. Escolha "Aluno" para entrar.';
        header('Location: ?pagina=login');
        exit;
    }

    session_regenerate_id(true);

    $_SESSION['aluno_id'] = $aluno['id_aluno'];
    $_SESSION['aluno_nome'] = $aluno['nome'];
    $_SESSION['aluno_tipo'] = $aluno['tipo'];

    unset($_SESSION['erro_login']);

    header('Location: ?pagina=' . ($aluno['tipo'] === 'professor' ? 'professor' : 'home'));
    exit;

} catch (PDOException $e) {
    $_SESSION['erro_login'] = 'Não foi possível realizar o login. Tente novamente.';
    header('Location: ?pagina=login');
    exit;
}