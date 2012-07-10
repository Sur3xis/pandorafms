<?php

// Pandora FMS - http://pandorafms.com
// ==================================================
// Copyright (c) 2005-2011 Artica Soluciones Tecnologicas
// Please see http://pandorafms.org for full contribution list

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.

global $config;

include_once("include/functions_ui.php");
include_once("include/functions_html.php");
include_once("include/functions_db.php");
include_once("include/functions_netflow.php");

check_login ();

if (! check_acl ($config["id_user"], 0, "IW")) {
	db_pandora_audit("ACL Violation",
		"Trying to access event viewer");
	require ("general/noaccess.php");
	return;
}

$id = (int)get_parameter('id');
$update = (string)get_parameter('update', 0);
$create = (string)get_parameter('create', 0);

$buttons['report_list']['active'] = false;
$buttons['report_list'] = '<a href="index.php?sec=netf&sec2=godmode/netflow/nf_report">'
	. html_print_image ("images/edit.png", true, array ("title" => __('Report list')))
	. '</a>';

$buttons['report_items']['active'] = false;
$buttons['report_items']['text'] = '<a href="index.php?sec=netf&sec2=godmode/netflow/nf_item_list&id='.$id.'">'
	. html_print_image ("images/god6.png", true, array ("title" => __('Report items')))
	. '</a>';

$buttons['edit_report']['active'] = true;
$buttons['edit_report']['text'] = '<a href="index.php?sec=netf&sec2=godmode/netflow/nf_report_form&id='.$id.'">'
	. html_print_image ("images/config.png", true, array ("title" => __('Edit report')))
	. '</a>';

//Header
ui_print_page_header (__('Netflow Report'), "images/networkmap/so_cisco_new.png", false, "", true, $buttons);

if ($id) {
	$permission = netflow_check_report_group ($id, false);
	if (!$permission) { //no tiene permisos para acceder a un informe
		require ("general/noaccess.php");
		return;
	}
}

if ($id) {
	$report = netflow_reports_get_reports ($id);
	$name = $report['id_name'];
	$description = $report['description'];
	$group = $report['id_group'];

}
else {
	$name = '';
	$group = '';
	$description = '';
}

if ($update) {
	$id = get_parameter('id');
	$name = (string) get_parameter ('name');
	$description = get_parameter ('description');
	$group = get_parameter('id_group');
	
	if ($name == '') {
		ui_print_error_message (__('Not updated. Blank name'));
	}
	else {
		$result = db_process_sql_update ('tnetflow_report',
			array (
				'id_name' => $name,
				'id_group' => $group,
				'description' => $description,
				),
			array ('id_report' => $id));
				ui_print_result_message ($result, __('Report updated successfully'), __('Error updating report'));
	}
}

if ($create){
	$name = (string) get_parameter ('name');
	$group = (int) get_parameter ('id_group');
	$description = get_parameter('description','');
	
	$values = array (
		'id_name' => $name,
		'id_group' => $group,
		'description' => $description,
	);
	
	$id = db_process_sql_insert('tnetflow_report', $values);
	if ($id === false) {
		ui_print_error_message ('Error creating report');
	}
	else {
		ui_print_success_message ('Report created successfully');
	}
}

$table->width = '80%';
$table->border = 0;
$table->cellspacing = 3;
$table->cellpadding = 5;
$table->class = "databox_color";
$table->style[0] = 'vertical-align: top;';

$table->data = array ();

$table->data[0][0] = '<b>'.__('Name').'</b>';
$table->data[0][1] = html_print_input_text ('name', $name, false, 30, 80, true);

$own_info = get_user_info ($config['id_user']);
$table->data[1][0] = '<b>'.__('Group').'</b>';
$table->data[1][1] = html_print_select_groups($config['id_user'], "IW",
	$own_info['is_admin'], 'id_group', $group, '','', -1, true,
	false, false);

$table->data[2][0] = '<b>'.__('Description').'</b>';
$table->data[2][1] = html_print_textarea ('description', 2, 65, $description, '', true);


echo '<form method="post" action="index.php?sec=netf&sec2=godmode/netflow/nf_report_form">';
html_print_table ($table);
echo '<div class="action-buttons" style="width: '.$table->width.'">';

if ($id) {
	html_print_input_hidden ('update', 1);
	html_print_input_hidden ('id', $id);
	html_print_submit_button (__('Update'), 'crt', false, 'class="sub upd"');
}
else {
	html_print_input_hidden ('create', 1);
	html_print_submit_button (__('Create'), 'crt', false, 'class="sub wand"');
}
echo '</div>';
echo '</form>';
?>