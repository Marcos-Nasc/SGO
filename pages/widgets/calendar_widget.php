<?php
// Configurações de Data (usa o que foi definido no dashboard ou pega o atual)
$mes_num = isset($mes_atual) ? $mes_atual : date('m');
$ano_num = isset($ano_atual) ? $ano_atual : date('Y');

// Nomes dos meses em Português
$nomes_meses = [
    '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril',
    '05' => 'Maio',    '06' => 'Junho',     '07' => 'Julho', '08' => 'Agosto',
    '09' => 'Setembro','10' => 'Outubro',   '11' => 'Novembro', '12' => 'Dezembro'
];

$nome_mes = $nomes_meses[$mes_num];

// Lógica do Calendário
$dia_atual = date('j');
$dias_no_mes = cal_days_in_month(CAL_GREGORIAN, $mes_num, $ano_num);

// Descobre qual dia da semana cai o dia 1 (0 = Dom, 6 = Sab)
$dia_semana_inicio = date('w', strtotime("$ano_num-$mes_num-01"));

// Garante que $dias_com_eventos seja um array (caso o dashboard não tenha criado)
if (!isset($dias_com_eventos)) {
    $dias_com_eventos = [];
}
?>

<div class="calendar-widget">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <h3><?php echo "$nome_mes $ano_num"; ?></h3>
    </div>
    
    <div class="calendar-header">
        <div>Dom</div><div>Seg</div><div>Ter</div><div>Qua</div><div>Qui</div><div>Sex</div><div>Sáb</div>
    </div>
    
    <div class="calendar-grid">
        <?php
        // Células vazias antes do dia 1
        for ($i = 0; $i < $dia_semana_inicio; $i++) {
            echo '<div class="calendar-day empty"></div>';
        }

        // Dias do mês
        for ($dia = 1; $dia <= $dias_no_mes; $dia++) {
            // Verifica se é hoje
            $is_today = ($dia == $dia_atual && $mes_num == date('m') && $ano_num == date('Y')) ? 'today' : '';
            
            // Verifica se tem evento (agendamento) neste dia
            $has_event = in_array($dia, $dias_com_eventos);
            
            // --- MUDANÇA AQUI ---
            // Formata a data como YYYY-MM-DD para o atributo data-date
            $data_completa = $ano_num . '-' . str_pad($mes_num, 2, '0', STR_PAD_LEFT) . '-' . str_pad($dia, 2, '0', STR_PAD_LEFT);
            
            // Adiciona o atributo data-date
            echo "<div class='calendar-day $is_today' data-date='$data_completa'>";
            echo $dia;
            
            if ($has_event) {
                echo '<span class="calendar-event-dot"></span>';
            }
            echo "</div>";
        }
        ?>
    </div>
</div>