<?php

namespace Http\Controllers;

use DOMDocument;
use DOMXPath;

libxml_use_internal_errors(true);

class UnivalePegarLinhasHorariosController {
    public function create($request, $_local) {
        $local = '';

        switch ($_local) {
            case 'ba':
                $local = '2';
                break;
            case 'mg':
                $local = '3';
                break;
            default:
                echo 'Local incorreto';
                return false;
                break;
        }

        try {
            $link_linhas = "https://www.univale.com/horarios?meulocal={$local}";
            $query_linhas = ".//li[@class=\"collection-item\"]/a";
            $query_ida_ss = ".//div[@id=\"ida_ss\"]//b";
            $query_ida_s = ".//div[@id=\"ida_s\"]//b";
            $query_ida_df = ".//div[@id=\"ida_df\"]//b";
            $query_volta_ss = ".//div[@id=\"volta_ss\"]//b";
            $query_volta_s = ".//div[@id=\"volta_s\"]//b";
            $query_volta_df = ".//div[@id=\"volta_df\"]//b";

            // Padrão para retirar espaços desnessários do nome da linha
            $pattern = "/[A-Z\dáãç\'\´\`\(\)\.]+(?:\s{0,3}-?\s{0,2}[A-Z\dáãç\'\´\`\(\)\.])*/iu";

            $html_linhas = file_get_contents($link_linhas);

            $doc_linhas = new DOMDocument();
            $doc_linhas->loadHTML($html_linhas);

            $xpath_linhas = new DOMXPath($doc_linhas);

            $node_list_linhas = $xpath_linhas->query($query_linhas);

            $linhas = [];
            foreach($node_list_linhas as $key_linhas => $node_linhas) {

                // Retira palavras que vem junto com o nome da linha
                $linha = explode('departure_board', $node_linhas->textContent)[1];

                // Retira espaços desnessários
                preg_match($pattern, $linha, $matches);
            
                $nome_linha = $matches[0];
                $link_horarios = $node_linhas->attributes[0]->value;
                $link_horarios .= "&meulocal={$local}";

                $linhas[$key_linhas] = [
                    'nome' => $nome_linha,
                    'link-horarios' => $link_horarios
                ];

                $html_horarios = file_get_contents($link_horarios);
                
                $doc_horarios = new DOMDocument();
                $doc_horarios->loadHTML($html_horarios);
                
                $xpath_horarios = new DOMXPath($doc_horarios);
                
                $node_list_horarios_ida_ss = $xpath_horarios->query($query_ida_ss);
                $node_list_horarios_ida_s = $xpath_horarios->query($query_ida_s);
                $node_list_horarios_ida_df = $xpath_horarios->query($query_ida_df);

                $node_list_horarios_volta_ss = $xpath_horarios->query($query_volta_ss);
                $node_list_horarios_volta_s = $xpath_horarios->query($query_volta_s);
                $node_list_horarios_volta_df = $xpath_horarios->query($query_volta_df);

                foreach($node_list_horarios_ida_ss as $node_horarios) {
                    $linhas[$key_linhas]['horarios']['ida']['segunda-sexta'][] = $node_horarios->textContent;
                }

                foreach($node_list_horarios_ida_s as $node_horarios) {
                    $linhas[$key_linhas]['horarios']['ida']['sabado'][] = $node_horarios->textContent;
                }

                foreach($node_list_horarios_ida_df as $node_horarios) {
                    $linhas[$key_linhas]['horarios']['ida']['domingo-feriado'][] = $node_horarios->textContent;
                }
                
                foreach($node_list_horarios_volta_ss as $node_horarios) {
                    $linhas[$key_linhas]['horarios']['volta']['segunda-sexta'][] = $node_horarios->textContent;
                }

                foreach($node_list_horarios_volta_s as $node_horarios) {
                    $linhas[$key_linhas]['horarios']['volta']['sabado'][] = $node_horarios->textContent;
                }

                foreach($node_list_horarios_volta_df as $node_horarios) {
                    $linhas[$key_linhas]['horarios']['volta']['domingo-feriado'][] = $node_horarios->textContent;
                }
            }

            $linhas_json = json_encode($linhas, JSON_PRETTY_PRINT);

            $file_name = $local === '2' ? '/linhas_e_horarios_ba.json' : '/linhas_e_horarios_mg.json';

            $handle = fopen(DATABASE_PATH . $file_name, 'a');
            
            fwrite($handle, $linhas_json);

            fclose($handle);

            echo 'Sucesso';
        } catch(\Exception $e) {
            echo 'Erro ao pegar linhas e horarios';
        }
        
    }
}