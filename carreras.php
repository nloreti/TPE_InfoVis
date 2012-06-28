<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<script type='text/javascript' src='http://www.google.com/jsapi'></script>
<script type="text/javascript" src="./bootstrap/js/jquery.js"></script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript" src="./bootstrap/js/bootstrap.js"></script>
<script type="text/javascript" src="http://d3js.org/d3.v2.js"></script>

<link rel="stylesheet" type="text/css" href="./bootstrap/css/bootstrap.css">

<title>Visualización de la Información - ITBA - 2012</title>
</head>

<body>
<?php
/*Creando el link a la conexion */
$link =  mysql_connect('localhost', '', '');
if (!$link) {
    die('No pudo conectarse: ' . mysql_error());
}

/* Conexion en Produccion
$link =  mysql_connect('localhost', 'v0010067', 'momaTAgu92');
if (!$link) {
    die('No pudo conectarse: ' . mysql_error());
}*/

/* POST DETAILS */
//print("<pre>");
//print_r($_POST);
//print("</pre>");

/* Totales de la pregunta 1 */
$SQL_COUNT = "SELECT count(*) as TOTAL FROM nota";
$SQL_MAX = "SELECT max(YEAR(fecha)) as MAX from nota";
$SQL_MIN = "SELECT min(YEAR(fecha)) as MIN from nota";

/*	Nombre de la Base de Datos	*/
$dbname = "test";
//$dbname = "v0010067_oleoyarte";

/*	Conectandose con Base	*/
mysql_select_db($dbname, $link);

/*	Queries	*/
	
$max=mysql_query($SQL_MAX);
$min=mysql_query($SQL_MIN);

$array_materias;
$array_carreras;
$minyear;
$maxyear;
$cantmaterias = 0;
$cantcarreras = 0;

/* Cargo rango de Años en base al formulario */
while ($row = mysql_fetch_assoc($min)){$minSQL = $row["MIN"];};	
while ($row = mysql_fetch_assoc($max)){$maxSQL = $row["MAX"];};	
$minyear =  $_POST["fromyear"];
//echo $minyear."<br/>";
if( $minyear == null || $minyear < $minSQL ){
	//echo "ENTRO";
	$minyear = $minSQL;
}
$maxyear = $_POST["toyear"]; 
//echo $maxyear."<br/>";
if ($maxyear == null){
	$maxyear = $maxSQL;
}

if(strcmp($_POST["select"],"Nombre") == 0){
	$SQL_Materias = "SELECT DISTINCT carrera from Alumno ORDER BY carrera ASC";
}else if(strcmp($_POST["select"],"Promedio") == 0){
$SQL_Materias = "SELECT DISTINCT carrera, AVG(Nota) from Alumno INNER JOIN Nota WHERE idAlumno = id AND YEAR(Fecha) >=".$minyear." AND YEAR(Fecha) <= ".$maxyear." GROUP BY carrera ORDER BY AVG(nota) DESC";
}else{
	$SQL_Materias = "SELECT DISTINCT carrera, COUNT(*) from Alumno INNER JOIN Nota WHERE idAlumno = id AND YEAR(Fecha) >=".$minyear." AND YEAR(Fecha) <= ".$maxyear." GROUP BY carrera ORDER BY COUNT(*) DESC";
}

//echo $SQL_Materias;

$materias_origin=mysql_query($SQL_Materias);
//$carreras_origin=mysql_query($SQL_Carreras);

while ($row = mysql_fetch_assoc($materias_origin)){	
						$array_materias[] = $row["carrera"];
						$cantmaterias++;
};

while ($row = mysql_fetch_assoc($carreras_origin)){	
						$array_carreras[] = $row["Carrera"];
						$cantcarreras++;
};

//print_r($array_materias);
$map[$maxyear-$minyear][$cantmaterias];

/* Traigo si hay materias seleccionadas */
$cantMateriasConsulta = 0;
$materias_para_consulta;
for($i=0; $i < $cantmaterias; $i++){
	$search = str_replace(" ","_",$array_materias[$i]);
	//echo $_POST["$search"];
	$aux = $_POST["$search"];
	//echo "Materia:".$array_materias[$i]."<br/>";
	if($aux != null){
		$materias_para_consulta[$cantMateriasConsulta] = $aux;
		$cantMateriasConsulta++;
		
	}
}
//echo "CANT: ".$cantMateriasConsulta."<br/>";
//print_r($materias_para_consulta);
//for($i = 0; $i < $maxyear-$minyear; $i++){
//	for($j = 0; $j<$cantmaterias;$j++){
//		$map[$i][$j] = "0";
//	}
//}


/* Realizo la consulta final */
$SQL = "SELECT Carrera as Materia ,YEAR(fecha) as Anio,AVG(nota) as Promedio FROM Alumno INNER JOIN nota AS tbl1 WHERE Alumno.id = tbl1.idAlumno AND year(FECHA) >= ".$minyear." AND YEAR(fecha) <= ".$maxyear; 
for($i = 0; $i < $cantMateriasConsulta;$i++){
	if ( $i == 0){
		$SQL = $SQL . " AND ( Carrera = " ."'$materias_para_consulta[$i]'". " ";
	}else{
		$SQL = $SQL . "OR Carrera = " ."'$materias_para_consulta[$i]'". " ";
	}
}
if($i>0){
$SQL = $SQL . " ) ";
}
$SQL = $SQL . " GROUP BY Carrera, YEAR(fecha) ORDER BY Promedio DESC";
//echo $SQL;
$materias=mysql_query($SQL);

while ($row = mysql_fetch_assoc($materias)){	
						$sqlmateria = $row["Materia"];
						$sqlanio = $row["Anio"];
						$sqlpromedio = $row["Promedio"];
						//print "<br/>";
						$column = array_search($sqlmateria,$array_materias);
						$year = $sqlanio-$minyear;
						$map[$year][$column] = $sqlpromedio;
					//	print $map[$maxyear-$sqlanio][$column];
					//	print "<br/>";
};
$resp;
for($i = 0; $i < $maxyear-$minyear; $i++){
	for($j = 0; $j<$cantmaterias;$j++){
		$cant = $j + 1;
		if ( $cant < $cantmaterias ){
			$resp[$i] = $resp[$i] . $map[$i][$j] . ",";
		}else{
			$resp[$i] = $resp[$i] . $map[$i][$j];
		}
	}
//	echo "CANT: " . $j . "<br/>";
}


//echo $resp[10];

//echo $materias_promedio;
echo "<script type='text/javascript'>\n";
echo "var j_array_fecha = new Array();";
echo "var j_array_materias = new Array();";
echo "var j_array_final = new Array();";
echo "var options2 = new Array();";

for($i = 0; $i < $cantmaterias; $i++){
		echo "j_array_materias.push({'val1':'$array_materias[$i]'});";
		
}

for($i = 0; $i < $maxyear-$minyear; $i++){
						//$materia = $row["Materia"];
						//$year = $row["Anio"];
						//$total = $row["Promedio"];
						$anio = $minyear + $i;
					//	var myvar = echo json_encode($myVarValue);
						
						echo "j_array_fecha.push({	'val1':'$anio','val2':'$resp[$i]'});";
};

echo "</script>\n";


//echo "SALIO";
?>

<script type="text/javascript">
    $(document).ready(function () {
        $('.dropdown-toggle').dropdown();
    var nextButton = document.getElementById('b2');
});
</script>
<script type="text/javascript">
	
      // Load the Visualization API and the piechart package.
google.load('visualization', '1', {'packages':['annotatedtimeline']});    
  google.load('visualization', '1.0', {'packages':['corechart']});
	
      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {

        // Create the data table.
	var data9 = new google.visualization.DataTable();
	
	data9.addColumn('string', 'Date');
	for( k = 0; k<j_array_materias.length;k++){
		data9.addColumn('number', j_array_materias[k].val1);
	//	data9.addColumn('string', 'title1');
	 //  data9.addColumn('string', 'text1');
	}
	
	for( k = 0; k<j_array_fecha.length;k++){
			//var myvar = <?php echo json_encode($myVarValue); ?>;
			var vector = new Array();
		//	vector.push(new Date(j_array_fecha[k].val1,0,1));
			vector.push(j_array_fecha[k].val1);
			var values = j_array_fecha[k].val2.split(",");
	//		console.log(values);	
			for( j = 0; j < values.length; j++){
				//if(j%2 == 0){
					if(values[j] != "undefined"){
						vector.push(parseFloat(values[j]));						
				}else{
					vector.push(values[j]);
			//	}
			}
					
			}
			
			//getSortedRows(3)
			//vector.push(0.0);
			j_array_final = vector;
		//	console.log(vector);
			data9.addRow(vector);
		
	}
	
    // Set chart options
    var options = {'width':800,
                       'height':500,
						'backgroundColor.strokeWidth':1,
						'backgroundColor.stroke':'#666',
						'pointSize':'2'};
						
	options2 = { 					'width':800,
					                       'height':500,
											'backgroundColor.strokeWidth':1,
											'backgroundColor.stroke':'#666',
											'pointSize':'2',

										};

									 
		var datechart = new google.visualization.LineChart(document.getElementById('fechachart_div'));
		datechart.draw(data9, options);
		
		var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
		chart.draw(data9, options2);
	//	var datechart = new google.visualization.AnnotatedTimeLine(document.getElementById('fechachart_div'));
	  //  datechart.draw(data9, {displayAnnotations: true});
	};
	
	 nextButton.onclick = function() {
	      options.hAxis.viewWindow.minValue += 1;
	      options.hAxis.viewWindow.maxValue += 1;
	      drawChart();
	    }
    </script>
	<div class="navbar">
	  <div class="navbar-inner">
	    <div class="container">
	      	<a class="brand" href="#">
			  ITBA Visualizations
			</a>
			<ul class="nav">
			  <li>
			    <a href="./index.php">Home</a>
			  </li>
			  <li><a href="./alumnos.php">Alumnos</a></li>
			  <li><a href="./materias.php">Materias</a></li>
			<li><a class="active" href="./carreras.php">Carreras</a></li>
		
			</ul>
		</div>
	  </div>
	</div>
	<div class="row" style="margin-left:80px;">
	  <div class="span4">
		<h1>Form</h1>
	  	<form class="well" action="./carreras.php" method="POST">
				<label>Fechas</label>
				<p><input type="text" name="fromyear" placeholder="Fecha inicio"/></p>
				<p><input type="text" name="toyear" placeholder="Fecha limite"/></p>
				<label>Ordenamiento</label>
				<select id="select" name="select">
				                <option>Nombre</option>
				                <option>Promedio</option>
								<option>Cantidad de Alumnos</option>
				</select><label></label>
				<button type="submit" class="btn">Submit</button>
				<label></label>
				<label>Alumnos</label>
				<label class="checkbox">
			<?php $i;
			for( $i = 0; $i < $cantmaterias; $i++){
				echo "<input type='checkbox' name='$array_materias[$i]' value='$array_materias[$i]' />".$array_materias[$i]."<br/>";
			}?>
			</label>
		</form>
	  </div>
	  <div class="span8">
			<h1>Graficas</h1>
				<h3>Carrreras y promedios en el tiempo</h3>
			<div id='fechachart_div' class='chart' style='width: 750px; height: 450px; float:left;'></div><br/>
			<h3>Carreras y Promedios por a&ntilde;io</h3>
			<div id="chart_div" class='chart' style="width: 750px; height: 450px; float:left;"></div><br/>
			
	  </div>
	</div>


</body>