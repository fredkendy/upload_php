<?php

    //Permitir upload APENAS se pessoa estiver logada (brecha de segurança), bem como checar extensão do arquivo
    //Não salva o arquivo no bd: Copia o arquivo no servidor (no caso, na pasta do htdocs) e salva o nome no banco, e através do db, puxa ele de volta
    //1024 bytes = 1 kb
    //1024 kb = 1 mb

    include("conexao.php");

    if(isset($_GET['deletar'])) {

        //Consulta para ver qual o path ddo arquivo que deseja deletar
        $id = intval($_GET['deletar']);
        $sql_query = $mysqli->query("SELECT * FROM arquivos WHERE id = '$id'") or die($mysqli->error);
        $arquivo = $sql_query->fetch_assoc();

        if(unlink($arquivo['path'])) {
            $deu_certo = $mysqli->query("DELETE FROM arquivos WHERE id = '$id'") or die($mysqli->error);
            if ($deu_certo) {
                echo "<p>Arquivo excluído com sucesso!</p>";
            }
        }
        
    }

    function enviarArquivo($error, $size, $name, $tmp_name) {

        //Precisa incluir na função pois são "fechadas"
        include("conexao.php");

        //'error' é propriedade que veio do servidor, visualizado no var_dump
        if ($error) {
            die("Falha ao enviar arquivo");
        }

        //Fazendo a verificação de tamanho ('size' é propriedade que veio do servidor, visualizado no var_dump)
        if ($size > 2097152) {
            die("Arquivo muito grande. Máximo: 2 MB");
        }

        //Definindo onde o arquivo deve ir
        $pasta = "arquivos/";

        //Criando um novo nome único (uniqid) para o arquivo (se o servidor recebe com o nome de origem, aumenta a chance de homonimo e consequentemente sobrescrever algum arquivo com nome igual)
        $nomeDoArquivo = $name;
        $novoNomeDoArquivo = uniqid();

        //Verificando qual a extensão do arquivo
        $extensao = strtolower(pathinfo($nomeDoArquivo, PATHINFO_EXTENSION));
        
        if ($extensao != "jpeg" && $extensao != 'png') {
            die("Tipo de arquivo não aceito");
        }

        $path = $pasta . $novoNomeDoArquivo . "." . $extensao;

        //Essa função retorna true (se deu certo) ou false (deu errado)
        $deu_certo = move_uploaded_file($tmp_name, $path);

        if ($deu_certo) {
            $mysqli->query("INSERT INTO arquivos (nome, path) VALUES ('$nomeDoArquivo', '$path')") or die($mysqli->error);
            return true;
        } else {
            return false;
        }
    }

    if(isset($_FILES['arquivos'])) {
        //Armazenando o arquivo em uma variável
        $arquivos = $_FILES['arquivos'];

        //Por padrão, já estará tudo certo
        $tudo_certo = true;

        //Para cada arquivo, fazer uma verificação com a função
        foreach($arquivos['name'] as $index => $arq) {
            $deu_certo = enviarArquivo($arquivos['error'][$index], $arquivos['size'][$index], $arquivos['name'][$index], $arquivos["tmp_name"][$index]);
            if (!$deu_certo) {
                $tudo_certo = false;
            }   
        }

        if ($tudo_certo) {
            echo "<p>Todos os arquivos foram enviados com sucesso!</p>";
        } else {
            echo "<p>Falha ao enviar um ou mais arquivos</p>";
        }

    }

    $sql_query = $mysqli->query("SELECT * FROM arquivos") or die($mysqli->error);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload de Arquivo</title>
</head>
<body>
    <h2>Upload de Arquivo</h2>

    <form enctype="multipart/form-data" action="" method="post">
        <p>
            <label for="">Selecione o arquivo:</label>
            <input multiple type="file" name="arquivos[]"> <!-- Permite que selecione multiplos arquivos -->
        </p>
        <button type="submit" name="upload">Enviar arquivo</button>
    </form>

    <h1>Lista de Arquivos</h1>

    <table border="1" cellpadding="10">
        <thead>
            <th>Preview</th>
            <th>Arquivo</th>
            <th>Data de Envio</th>
            <th></th>
        </thead>

        <tbody>
            <?php
            while ($arquivo = $sql_query->fetch_assoc()) {
            ?>
            <tr>
                <td><img height="50" src="<?php echo $arquivo['path']; ?>"></td>
                <td><a target="_blank" href="<?php echo $arquivo['path']; ?>"> <?php echo $arquivo['nome']; ?></td>
                <td><?php echo date("d/m/Y H:i", strtotime($arquivo['data_upload'])); ?></td>
                <th><a href="index.php?deletar=<?php echo $arquivo['id']; ?>">Deletar</a></th>
            </tr>
            <?php
            }
            ?>
        </tbody>
    </table>

    
    
</body>
</html>