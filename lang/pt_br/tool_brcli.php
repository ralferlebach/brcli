<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language strings for tool_brcli (Brazilian Portuguese).
 *
 * @package    tool_brcli
 * @copyright  2019 Paulo Júnior <pauloa.junior@ufla.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Interface de linha de comando para backup e restauração';
$string['unknowoption'] = 'Opção inválida: {$a}';
$string['noadminaccount'] = 'Erro: Não há uma conta de administrador cadastrada!';
$string['directoryerror'] = 'Erro: O diretório de destino informado não existe ou não pode ser escrito!';
$string['nocategory'] = 'Erro: A categoria informada não existe!';
$string['performingbck'] = 'Iniciando backup do curso {$a}...';
$string['performingres'] = 'Restaurando backup do curso {$a}...';
$string['operationdone'] = 'Finalizado!';
$string['invalidbackupfile'] = 'Arquivo de backup inválido: {$a}';
$string['invalidpreset'] = 'Preset inválido: {$a}. Valores suportados: full, contentonly.';
$string['helpoptionbck'] = 'Realiza o backup de todos os cursos de uma categoria.

Opções:
--categoryid=INTEGER        ID da categoria cujo backup será feito.
--destination=STRING        Caminho onde serão armazenados os arquivos de backup.
--preset=STRING             Preset do backup. full (padrão) ou contentonly.
--users=0|1                 Override: incluir dados de usuários.
--questionbank=0|1          Override: incluir banco de questões.
--calendarevents=0|1        Override: incluir eventos do calendário.
--competencies=0|1          Override: incluir competências.
--histories=0|1             Override: incluir histórico de notas.
--logs=0|1                  Override: incluir logs.
-h, --help                  Exibe a ajuda.

Exemplo:
    sudo -u www-data /usr/bin/php admin/tool/brcli/backup.php --categoryid=1 --destination=/moodle/backup/

    # Backup apenas do conteúdo (sem usuários, banco de questões, calendário, competências, logs, históricos, etc.)
    sudo -u www-data /usr/bin/php admin/tool/brcli/backup.php --categoryid=1 --destination=/moodle/backup/ --preset=contentonly
';
$string['helpoptionres'] = 'Restaura todos os arquivos de backup contidos em um diretório.

Opções:
--categoryid=INTEGER        ID da categoria onde os backup serão restaurados.
--source=STRING             Caminho onde os arquivos de backup (.mbz) estão armazenados.
--preset=STRING             Preset da restauração. full (padrão) ou contentonly.
--users=0|1                 Override: restaurar dados de usuários.
--questionbank=0|1          Override: restaurar banco de questões.
--calendarevents=0|1        Override: restaurar eventos do calendário.
--competencies=0|1          Override: restaurar competências.
--histories=0|1             Override: restaurar histórico de notas.
--logs=0|1                  Override: restaurar logs.
-h, --help                  Exibe a ajuda.

Exemplo:
    sudo -u www-data /usr/bin/php admin/tool/brcli/restore.php --categoryid=1 --source=/moodle/backup/

    # Restaura apenas o conteúdo (ignora usuários, banco de questões, calendário, competências, logs, históricos, etc.)
    sudo -u www-data /usr/bin/php admin/tool/brcli/restore.php --categoryid=1 --source=/moodle/backup/ --preset=contentonly
';
