<?php
defined('JPATH_BASE') or die;
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>

	<p>Relatório dos disparos efetuados até o momento:</p>

	<table width="100%">

		<tr>
	    <th>Formulário</th>
	    <th>Status</th> 
	    <th>data</th>
	    <th>Campos</th>
	  </tr>

	<?php foreach($displayData['forms'] as $row =>$form): ?>
		<tr>
		    <td><?=$form['form_name']?></td>
		    <td><?=$form['status_custom']?></td> 
		    <td><?php 

		    	$date = new DateTime($form['dt_send']);

		    	echo $date->format('d/m/Y H:i');

		    ?></td> 
		    <td><?php 
		    		$explode_fields = json_decode($form['fileds'],true);

		    		unset($explode_fields['droide']);
		    		unset($explode_fields['option']);
		    		unset($explode_fields['module']);
		    		unset($explode_fields['format']);
		    		unset($explode_fields['id_ext']);

		    		foreach ($explode_fields as $field => $val): ?>

		    			<strong> <?=strtoupper($field) ?>:</strong> <?=strtoupper($val) ?> || 
		    		<?php endforeach ?>
		    </td> 
		 </tr>

	<?php endforeach;?>
</table>

</body>
</html>