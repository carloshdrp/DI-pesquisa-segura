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
    | Resultados por página e quantidade mínima de resultados. Abaixo do mínimo,
    | a busca dispara uma nova coleta em segundo plano (RF13), no máximo uma vez
    | por termo dentro do intervalo de recoleta.
    */
    'por_pagina' => (int) env('BUSCA_POR_PAGINA', 10),
    'minimo_resultados' => (int) env('BUSCA_MINIMO_RESULTADOS', 3),
    'recoleta_intervalo_horas' => (int) env('BUSCA_RECOLETA_INTERVALO_HORAS', 6),

    /*
    | Tópicos mais pesquisados (RF12): período considerado e quantidade guardada.
    | O comando busca:topicos recalcula a lista a cada hora.
    */
    'topicos' => [
        'dias' => (int) env('BUSCA_TOPICOS_DIAS', 7),
        'limite' => (int) env('BUSCA_TOPICOS_LIMITE', 10),
    ],

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

    /*
    | Coleta de páginas das fontes cadastradas (crawler). Roda em fila própria,
    | com baixa prioridade, para não competir com as buscas dos usuários.
    */
    'coleta' => [
        'fila' => env('BUSCA_COLETA_FILA', 'coleta'),
        'user_agent' => env('BUSCA_COLETA_USER_AGENT', 'PesquisaSeguraBot/1.0 (projeto academico)'),
        'max_paginas' => (int) env('BUSCA_COLETA_MAX_PAGINAS', 50),
        'profundidade' => (int) env('BUSCA_COLETA_PROFUNDIDADE', 2),
        'atraso_ms' => (int) env('BUSCA_COLETA_ATRASO_MS', 1000),
        'tamanho_max_bytes' => (int) env('BUSCA_COLETA_TAMANHO_MAX_BYTES', 2 * 1024 * 1024),
        'conteudo_max_caracteres' => (int) env('BUSCA_COLETA_CONTEUDO_MAX', 20000),
    ],

];
