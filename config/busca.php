<?php

return [

    /*
    | Quantidade diária de buscas permitida a usuários autenticados.
    */
    'limite_autenticado' => (int) env('BUSCA_LIMITE_AUTENTICADO', 50),

    /*
    | Quantidade diária de buscas permitida a visitantes (sessão anônima).
    */
    'limite_anonimo' => (int) env('BUSCA_LIMITE_ANONIMO', 10),

    /*
    | Termos e expressões que não podem ser buscados. A comparação ignora
    | maiúsculas, acentos e pontuação, e só considera palavras inteiras.
    | Lista inicial conservadora: a equipe deve revisar e ampliar.
    */
    'termos_bloqueados' => [
        'pornografia',
        'porn',
        'porno',
        'xxx',
        'nudes',
        'hentai',
        'sexo explicito',
        'conteudo adulto',
        'como fazer bomba',
        'como fazer explosivo',
        'como fazer veneno',
        'comprar droga',
        'comprar arma',
    ],

];
