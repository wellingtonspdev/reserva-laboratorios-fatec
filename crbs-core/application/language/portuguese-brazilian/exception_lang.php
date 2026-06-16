<?php

$lang['exception.DateException.invalidDate'] = 'Nenhuma data selecionada ou data invalida (%s).';
$lang['exception.DateException.forSessionRange'] = 'A data selecionada (%s) nao pertence ao semestre atual.';
$lang['exception.AgentException.forInvalidType'] = 'Tipo de reserva nao reconhecido. Deve ser um de: %s';
$lang['exception.AgentException.forNoSession'] = 'A data solicitada nao pertence a um semestre.';
$lang['exception.AgentException.forNoPeriod'] = 'O periodo solicitado nao foi encontrado.';
$lang['exception.AgentException.forNoRoom'] = 'A sala solicitada nao foi encontrada ou nao pode ser reservada.';
$lang['exception.AgentException.forInvalidDate'] = 'A data solicitada nao foi reconhecida ou nao pode ser reservada.';
$lang['exception.AgentException.forNoWeek'] = 'A data solicitada nao esta associada a uma semana do calendario.';
$lang['exception.AgentException.forNoBooking'] = 'A reserva solicitada nao foi encontrada.';
$lang['exception.AgentException.forAccessDenied'] = 'Voce nao tem permissao para modificar a reserva solicitada.';
$lang['exception.AvailabilityException.forNoWeek'] = 'A data selecionada nao esta atribuida a uma semana do calendario.';
$lang['exception.AvailabilityException.forNoPeriods'] = 'Nao ha periodos disponiveis para a data selecionada.';
$lang['exception.AvailabilityException.forHoliday.unknown'] = 'A data selecionada esta em um feriado.';
$lang['exception.AvailabilityException.forHoliday'] = 'A data selecionada esta em um feriado: %s: %s - %s';
$lang['exception.BookingValidationException.forExistingBooking'] = 'Ja existe outra reserva.';
$lang['exception.BookingValidationException.forHoliday'] = 'A reserva nao pode ser criada em um feriado.';
$lang['exception.SessionException.notSelected'] = 'Nenhum semestre ativo encontrado.';
$lang['exception.SettingsException.forDisplayType'] = 'A configuracao \'Tipo de visualizacao\' nao foi definida.';
$lang['exception.SettingsException.forColumns'] = 'A configuracao \'Colunas de exibicao\' nao foi definida.';
$lang['exception.SettingsException.forNoRooms'] = 'Nao ha salas disponiveis.';
$lang['exception.SettingsException.forNoSchedule'] = 'Este grupo de salas nao tem horario configurado para este semestre.';
