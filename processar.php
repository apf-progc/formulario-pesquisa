<!-- Colar este conteúdo no arquivo processar.php -->
 
<?php
    //recebe os dados do formulario.html
    // depois vai validar, depois vai salvar e mostrar
    // o resutlado na tela par o usuário
   
    //vamos começar pelo fuso horário, para ficar com a data registrada correta
    date_default_timezone_set("America/Sao_Paulo");
 
    // só aceitaremos dados vindo do post (lá do index,html)
    // "REQUEST_METHOD" padrão da linguagem
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        header("Location: index.html");
        exit;
    }
 
    // Função de segurança - para exibir o texto com segurança
    // vai impedir que tags ou scripts digitados pelo usuário sejam executados na página 
    // essa função é para bloquear o envio de códigos e outros que podem causar danos ao sistema
    function e($texto) {
        return htmlspecialchars($texto, ENT_QUOTES, "UTF-8");
    }
 
    //Recebendo os dados vindos do formulário
    // ?? devolve um valor padrão se o campo não existir
    // ele vai evitar erros do tipo: undefined index
    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $idade = trim($_POST["idade"] ?? "");
 
    $estado = $_POST["estado"] ?? "";
    $nota = $_POST["nota"] ?? "";
    $origem = $_POST["origem"] ?? "";
 
    $comentario = trim($_POST["comentario"] ?? "");
   
    //vai verificar se o check está marcado - não deixa prosseguir, se não estiver
    $termos = isset($_POST["termos"]);
   
    //vamos garantir que origem, realmente seja um array
    if (!is_array($origem)){
        //vamos fazer com que ele seja um array!
        $origem = [];
    }
 
    // lista de estados permitidos no formulario
    // estes estados vem do index.html, vejas quais vocÊ informou lá no value do campo estado
    // esse tipo de variável é aplicada ao input do tipo para caixa de seleção
    $estadosValidos = ["AP", "CE", "PR", "PE", "RN"];
 
    //vamos validar os campos de origem
    $origensValidas = [
        "indicacao" => "Indicacação de amigos",
        "redes" => "Redes Sociais",
        "busca" => "Pesquisa na Internet",
        "outros" => "Outros"
    ];

    //validações no servidor
    $erros = [];
 
    //validação do nome, para que tenha mais que 3 caracteres
    if (mb_strlen($nome) < 3){
        $erros[] = "O nome deve ter pelo menos três (3) cacateres.";
    } elseif (!preg_match("/^[\p{L}\s']+$/u", $nome)) {
        $erros[] = "Use apenas letras no nome.";  
    }
 
    // validação do e-mail
    // aqui vamos usar a função filter_var, ela já faz tudo, já é um validador pronto
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $erros[] = "Digite um e-mail válido";
    }
 
    //validação da idade, do lado do servidor
    // verificar se é número interiro e está entre 10 e 120
    $idadeInt = filter_var($idade, FILTER_VALIDATE_INT, [
        "options" => ["min_range" => 10, "max_range" => 120]
    ]);
    if ($idadeInt === false) {
        $erros[] = "A idade deve ser um número inteiro entre 10 e 120.";
    }

    // verificando os estados válidos 
    // consultado a variável $estadoVaidos criado anteriormente
    if (!in_array($estado, $estadosValidos, true)){
        $erros[]= "Selecione um estado válido";
    }


    // a variável  $notaint + mais os valores emppregados a ela, é aplicada ao input do tipo rádio
    // Realizando a válidação das notas que tem que ser inteiro de 1 a 5 apenas
    $notaint = filter_var($nota, FILTER_VALIDATE_INT, [
        "options" => ["min_range" => 1, "max_range" => 5]
    ]);

    // condição
    if ($notaint === false) {
        $erros[] = "A idade deve ser um número inteiro entre 10 e 120.";
    }

    if (count($origem) === 0)  {
        $erros[] = "Maeque pelo menos uma opção em 'Como você conheceu o curso',";
    } else {
        // vamos verificar se os campos marcados estão válidos no servidor
        // foreach é um laço de repetição
        foreach($origensValidas as $item) {
            if (!is_string($item) || array_key_exists($item, $origensValidas)){
                $erros [] = "Opção de origem inválida.";
                break;
            }
        }
    }

    // comantário: deixe ele como opcional - validar que tenha de 0 a 300 caracteres e a restrinção de no minimo 10 caracteres
    // se o usuário começar a escrever algo no campo de comentário

    if ($comentario !== "") {
        // vou verificar se tem pelo menos 10 caracteres. função que conta a quantidade de caracteres mb_strlen
        if (mb_strlen($comentario) < 10) {
            $erros[] = "O comentário deve ter pelo menos 10 caracteres ou ficar em branco!";
        // validando que ele não ultrapasse o limite
        } elseif (mb_strlen($comentario) > 300) {
            $erros[] = "O comentário pode ter no máximo 300 caracteres";
        }
    }
        // vamos validar os termos que precisam ser marcados. como é um input é do tipo de chackbox, não pode ser usado $termo === false, diante disto usa ! que refere-se a negativa 
    if (!$termos ) {
        $erros[] = "Você precisa concordar com os termos para enviar.";
    }

    // se tiver erros, mostrar aviso ao usuáro, se não salvar dados em um arquivo CSV
    if (count($erros) > 0) {
        // se entrou nesta condição, quer dizer que erros no formulário
        // criando uma variável
        $titulo = "Corrija os campos abaixo.";
        $tipo = "erro";
    } else {
        // se não tiver erro no formulário, vamos gerar e gravar os dados em um arquivo CSV
        // diretório do local onde será salvo o arquivo. isto é, a pasta onde o arquivo que estamos digitamos. 
        $arquivo = __DIR__ ."/respostas.csv";

        // diretório do local onde será salvo o arquivo. isto é, a pasta onde o arquivo que estamos digitamos, se existir, cria o registro, se não cria o arquivo e o regisro. 
        $novoArquivo = !file_exists ($arquivo);
        // abrindo o arquivo e adicionar as colunas.
        $f = fopen($arquivo, "a"); //"a" siginifica que vou adicionar no final do arquivo.
        
        if($f) { 
            // se for um arquivo novo, criar as colunas
            if($arquivo) {
                //fputcsv é uma função para criar coluna. as colunas podem apresentar todo tipo de caracteres. 
                fputcsv ($f, ["Data", "Nome", "E-mail", "Idade", "Estado", "Nota", "Origem", "Comentário"]); 
            }

            // agora vamos inserir os dados dos campos no arquivo. dentro do [ ] define o tipo de dado 
            fputcsv($f, [
                // date é uma função.
                date("d/,/Y H:i:s"),
                $nome,
                $email,
                $idade,
                $estado,
                $nota,
                // função exclusiva do php usada para quando o campo coleta mais de uma resposta. ele registra na lista com o separador |
                implode(" | ", $origem),                
                $comentario              
            ]);

            // fechando o arquivo
            fclose($f);
        }

        // agradecimento ao usuários pelas respostas
        $titulo = "Obrigada pela sua resposta!";
        $tipo = "sucesso";

    }
?>

<!-- agora vamos construir o HTML  de finalização do noosso projeto de pesquisa -->
<!DOCTYPE html>

 <!-- lang é para a linguagem do país -->
<html lang="pt-br">

<head>

    <!-- faz com que a tela aceite caracteres especiais -->
    <meta charset="UTF-8">

    <!-- responsavel por identificar o tipo de tela que estamos usando, mas não é a responsividade -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--título página -->
    <title>Resultado da Pesquisa</title>
    <!-- link que puxa o estilo para página. (stylesheet - comando que direciona o html realizar a interpretação do css -->
     <link rel="stylesheet" href="style.css">
     
     <!-- style é uma tag do HTML que refere-se ao CSS e tudo que for dentro dela, é CSS -->
    <style>
        /* estilo adicionais, que são só desta página*/
        .resumo {list-style: none; padding: 0; margin: 20px 0; }
        .resumo li {padding: 8px 0; border-bottom: 1px solid #e2e8f0;}
        .lista-erros {color: #e53e3e; padding-left: 20px;}
        .titulo-erro {color: #e53e3e; }
        .titulo-sucesso {color: #38a169;}
        .link-voltar {display: block; text-decoration: none; text-align: center; }
    </style>

</head>

<body>

    <!-- inserindo uma caixa com mensagem de erro se os requisitos obrihatórios não forem atendidos-->
    <main class= "container">
        <!-- chamando uma variável do php -->
        <?php if ($tipo === "erro"): ?>
            <h1 class= "titulo-erro"> <?= e ($titulo) ?> <h1>
            <!-- lista dos erros para o usuarios corrigir -->
            
            <!-- listando os erros do vetor de erro $erro. Neste caso chama todo o código juntos criando um laço de repetição-->
            <ul class="lista-erros"> 
                <!-- ; quer dizer faça no PHP e FOREACH é para que os elementos sejam listados um embaixo do outro -->
                <?php foreach($erros as $erro): ?>
                    <!-- código em azul ?= ? é para organizar em linha -->
                    <li> <?= e($erro) ?> </li>
                <?php endforeach; ?>
            </ul>

        <!-- Volta para a tela de pesquisa, mas mantém o que o usuário jé preencheu. insirindo JAVASCIPT no meio do HTML-->
        <!-- botao link-voltar é usado quando pode ter mais de um meio de acesso  ex: botão e link-->
        <a href="javascript:history.back()" class="botao link-voltar">Voltar e corrigir </a>
        <?php else: ?>
            <h1  class="titulo-sucesso"> <?= e($titulo) ?> </h1>
            <p> <?= e($nome) ?> sua resposta foi registrada com sucesso!</p>

            <ul class="resumo">
                <li> <strong>E-mail</strong> <?= e($email) ?> </li>
                <!-- para converter a variável para inteiro, muda-se o e para (int) -->
                <li> <strong>Idade:</strong> <?= (int) $idadeInt ?> anos </li>
                <li> <strong>Estado:</strong> <?= e($estado) ?> </li>
                <li> <strong>Nota para o curso:</strong> <?= (int) $notaint ?> </li>

                <!-- agora vamos tratar a oriem -->
                <li><strong> Como conheceu: </strong>
                    <!-- criando um laço de repetição para registrar vairos campos. arrew de objetos -->
                    <?php
                    $textos = [];
                    
                    foreach ($origem as $item) {
                        $texto[] = $origensValidas[$item];
                    }
                    // organizando os itens coletados em uma linha com separador
                    echo e(implode(",", $texto));
                    ?>
                </li>
                
                <!-- agora vamos trabalhar o comentário -->
                <!--verificando se o usuário digitou algo no comentário-->
                <?php if ($comentario !== ""): ?>
                    <li> <strong> Comentário: </strong><br>
                    <!-- mostrando o comentário conforme digitação do usuário-->
                    <!-- NL2BR é uma função que mantem o comentário com quabre de linha -->
                    <?= nl2br (e($comentario)) ?>
                    </li>
                <?php endif; ?>
            </ul>     

            <!-- Mostrar botão para usuário responder o questionário novamente -->
            <a href="index.html" class="botao link-voltar">Responder Novamente</a>
<!-- PHP sempre finaliza assim-->
<?php endif; ?>

</main>
    
</body> 
</html>


<!-- NL2BR é uma função que mantem o comentário com quabre de linha -->
<!-- ROSA são condicionais -->
<!-- o que está em cinsa é texto que o usuário vai ver -->
<!-- tudo que está em AMARELO é função do php -->
<!-- o que esta em AZUL é string -->
<!-- __DIR__ ."" direciona para pasta, diretório do local onde será salvo o arquivo. isto é, a pasta onde o arquivo que estamos digitamos-->
<!-- caso já exista o arquivo, o "a" garante que o registro será adicionado no final do arquivo. -->
<!-- ! usado para negar algo-->
<!--if = se for; else = se não --> 
<!-- file_exists  arquivo existe e !file_exists arquivo não existe -->
<!-- usa-se $ antes do nome para criar uma variável -->
<!-- existe mais de um tipo de variável, cada uma cabe a um tipo de input-->
<!-- ; vai no comando e quando cria-se uma variável -->
<!-- tem como criar funções para que verificar se o texto é válido-->