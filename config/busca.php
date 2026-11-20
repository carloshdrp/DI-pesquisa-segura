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

];
