// Referencias aos elementos
const form = document.getElementById("formPesquisa");
const contador = document.getElementById("contador");
 
// Atalho para pegar um campo do formulario pelo NAME
const campo = (nome) => form.elements[nome];
 
// Regras de Validação
// Cada função devolve uma mensagem de erro ou "" se estiver tudo certo
const validadores = {
    nome() {
        const valor = campo("nome").value.trim();
        // verifica se o campo nome foi preenchido
        if (valor === "") return "Informe seu nome.";
 
        // verifica se o campo nome tem mais de 3 caracteres
        if (valor.length < 3) return "O nome deve ter pelo menos 3 caracteres.";
       
        //se o campo estiver preenchido corretamente, retornará ""
        return "";
    },
   
    // validando o campo de e-mail
    email() {
        const valor = campo("email").value.trim();
        if (valor === "") return "Informe seu e-mail.";
        if (!campo("email").validity.valid) {
            return "Informe um e-mail válido.";
        }
        return "";
    },
 
    //validando o campo idade
    idade(){
        const valor = campo("idade").value.trim();
        if (valor === "") return "Informe a idade";
        const numero = Number(valor);
        if (!Number.isInteger(numero)) {
            return "A idade deve ser um número inteiro";
        }
        if (numero < 10 || numero > 120) {
            return "Idade deve estar entre 10 e 120 anos";
        }
        return "";
    },
 
    //validando se o estado foi selecionado
    // lembrando que o estado é uma caixa de seleção - select
    estado(){
        if (campo("estado").value === "") return "Selecione seu estado.";
        return "";
    },
 
    //validando a nota, que é uma tag de input do tipo radio
    nota(){
        //para o type radio, o .value devolverá o valor marcado, caso nehum tenha sido marcado ele devolve ""
        if (campo("nota").value === "") return "Escolha uma nota de 1 a 5.";
        return "";
    },
 
    // validando a oriegem
    origem(){
        const marcados = form.querySelectorAll ('input[name="origem[]"]:checked');
        //vamos fazer a validação do campo
        if (marcados.length === 0) return "Marque pelo menos uma opção."
        return "";
    },
 
    comentario() {
        const valor = campo("comentario").value.trim();
        // como ele é um campo de preenchimento opcional, só vou validar ele, caso tenha sido preenchido
        // além de que são necessários pelo menos 10 caracteres neste campo e no máximo 300
        if (valor !== "" && valor.length < 10) return "Escreva pelo menos 10 caracteres ou deixe em branco."
        return "";
    },
 
    termos(){
        if (!campo("termos").checked) return "Você precisa concordar para enviar";
        return "";
    }
};
 
// Função que valida um campo e atualiza a tela
function validarCampo(nome){
    const mensagem = validadores[nome]();
   
    const grupo = document.querySelector(`[data-campo="${nome}"]`);
 
    const erro = document.getElementById(`erro-${nome}`);
   
    erro.textContent = mensagem;
    grupo.classList.toggle("invalido", mensagem !== "");
    grupo.classList.toggle("valido", mensagem === "");
 
    return mensagem;
}
 
// Função que remove o [] do name origem[]
function nomeDoCampo(elemento){
    return elemento.name ? elemento.name.replace("[]", "") : "";
}
 
// validação ao sair do campo (campos de texto e select)
form.addEventListener("focusout", (evento) => {
    const tipo = evento.target.type;
    if (tipo === "radio" || tipo === "checkbox") return;
 
    const nome = nomeDoCampo(evento.target);
    if (validadores[nome]) validarCampo(nome);
});
 
// Revalidação enquanto o usuário digita, se o campo já estiver com erro
form.addEventListener("input", (evento) => {
    const nome = nomeDoCampo(evento.target);
    if (!validadores[nome]) return;
    const grupo = document.querySelector(`[data-campo="${nome}"]`);
    if (grupo.classList.contains("invalido")) {
        validarCampo(nome);
    }
});
 
// contador
campo("comentario").addEventListener("input", () => {
    contador.textContent = campo("comentario").value.length;
});
 
// VAMOS TRABALHAR O ENVIO DO FORMULÁRIO
form.addEventListener("submit", (evento) => {
    //impredir o envio automático do formulário, sem validar os campos antes
    evento.preventDefault();
 
    let primeiroInvalido = null;
 
    //validar todos os campos de uma vez só
    Object.keys(validadores).forEach((nome) => {
        const mensagem = validarCampo(nome);
        if (mensagem !== "" && primeiroInvalido === null){
            primeiroInvalido = nome;
        }
    });
 
    // se houver erro, levar o foco paraa o primeiro campo invalido
    if (primeiroInvalido !== null){
        const grupo = document.querySelector(`[data-campo="${primeiroInvalido}"]`);
        grupo.querySelector("input, select, textarea").focus();
        return;
    }
 
    //se estiver tudo certo, sem erros no formulario, vamos clicar no botao submit que chamara nosso aruivo processar.php
    //este commando nao chamara o arquivo php, caso o formulario permaneca com erros
 
    form.submit();
 
});