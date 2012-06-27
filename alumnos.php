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
/*print("<pre>");
print_r($_POST);
print("</pre>");*/

/* Totales de la pregunta 1 */
$SQL_COUNT = "SELECT count(*) as TOTAL FROM alumno";
$SQL_MAX = "SELECT max(YEAR(fecha)) as MAX from nota";
$SQL_MIN = "SELECT min(YEAR(fecha)) as MIN from nota";
$SQL_alumnos = "SELECT DISTINCT nombre from alumno ORDER BY nombre ASC";
$SQL_Carreras = "SELECT DISTINCT carrera from Alumnos ORDER BY carrera ASC";


/*	Nombre de la Base de Datos	*/
$dbname = "test";
//$dbname = "v0010067_oleoyarte";

/*	Conectandose con Base	*/
mysql_select_db($dbname, $link);

/*	Queries	*/
	
$max=mysql_query($SQL_MAX);
$min=mysql_query($SQL_MIN);
$alumnos_origin=mysql_query($SQL_alumnos);
$carreras_origin=mysql_query($SQL_Carreras);

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

while ($row = mysql_fetch_assoc($alumnos_origin)){	
						$array_materias[] = $row["nombre"];
						$cantmaterias++;
};

//while ($row = mysql_fetch_assoc($carreras_origin)){	
//						$array_carreras[] = $row["carreras"];
//						$cantcarreras++;
//};
$map[$maxyear-$minyear][$cantmaterias];
$mappromedios[$maxyear-$minyear][$cantmaterias];

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
print_r($materias_para_consulta);

//$SQL = "SELECT nombre, Anio, Promedio FROM Alumno INNER JOIN (SELECT idAlumno, YEAR(Fecha) AS Anio, AVG(Nota) AS Promedio FROM nota WHERE //YEAR(Fecha) >= 1987 AND YEAR(Fecha) <= 2012 GROUP BY YEAR(Fecha),idAlumno) as tbl1 WHERE Alumno.id = tbl1.idAlumno";

$SQL = "SELECT nombre, Anio, Promedio FROM Alumno INNER JOIN (SELECT idAlumno, YEAR(Fecha) AS Anio, AVG(Nota) AS Promedio FROM nota WHERE YEAR(Fecha) >= ".$minyear." AND YEAR(Fecha) <= ".$maxyear." GROUP BY YEAR(Fecha),idAlumno) as tbl1 WHERE Alumno.id = tbl1.idAlumno";
for($i = 0; $i < $cantMateriasConsulta;$i++){
	if ( $i == 0){
		$SQL = $SQL . " AND ( Nombre = " ."'$materias_para_consulta[$i]'". " ";
	}else{
		$SQL = $SQL . "OR Nombre = " ."'$materias_para_consulta[$i]'". " ";
	}
}
if($i>0){
	$SQL = $SQL . ")";
}

$SQL1 = "SELECT nombre, Anio, Promedio
FROM Alumno
INNER JOIN (
SELECT idAlumno, MAX(YEAR( Fecha )) AS Anio, AVG( Nota ) AS Promedio
FROM nota
WHERE YEAR( Fecha ) >=1987
AND YEAR( Fecha ) <=2012
GROUP BY idAlumno
) AS tbl1
WHERE Alumno.id = tbl1.idAlumno AND Anio >= ".$minyear." AND Anio <= ".$maxyear;

print $SQL1;
//echo $SQL;
$materias=mysql_query($SQL);
$promedios=mysql_query($SQL1);//echo "SALI";
//echo "MAXYEAR".$maxyear;

while ($row = mysql_fetch_assoc($materias)){	
						$sqlmateria = $row["nombre"];
						$sqlanio = $row["Anio"];
						$sqlpromedio = $row["Promedio"];
						//print "<br/>";
						print $sqlmaterias;
						$column = array_search($sqlmateria,$array_materias);
						$year = $sqlanio-$minyear;
						$map[$year][$column] = $sqlpromedio;
					//	print $map[$maxyear-$sqlanio][$column];
					//	print "<br/>";
};

while ($row = mysql_fetch_assoc($promedios)){	
						$sqlmateria = $row["nombre"];
						$sqlanio = $row["Anio"];
						$sqlpromedio = $row["Promedio"];
						//print "<br/>";
						print $sqlmaterias;
						$column = array_search($sqlmateria,$array_materias);
						$year = $sqlanio-$minyear;
						$mappromedios[$year][$column] = $sqlpromedio;
					//	print $map[$maxyear-$sqlanio][$column];
					//	print "<br/>";
};
//echo "SALI2";
$resp;
$resppromedios;
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

for($i = 0; $i < $maxyear-$minyear; $i++){
	for($j = 0; $j<$cantmaterias;$j++){
		$cant = $j + 1;
		if ( $cant < $cantmaterias ){
			$resppromedios[$i] = $resppromedios[$i] . $mappromedios[$i][$j] . ",";
		}else{
			$resppromedios[$i] = $resppromedios[$i] . $mappromedios[$i][$j];
		}
	}
//	echo "CANT: " . $j . "<br/>";
}
echo "<script type='text/javascript'>\n";
echo "var j_array_fecha = new Array();";
echo "var j_array_promedios = new Array();";
echo "var j_array_materias = new Array();";
echo "var j_array_final = new Array();";


for($i = 0; $i < $cantmaterias; $i++){
		echo "j_array_materias.push({'val1':'$array_materias[$i]'});";
		
}

for($i = 0; $i < $maxyear-$minyear; $i++){
						$anio = $minyear + $i;	
						echo "j_array_fecha.push({	'val1':'$anio','val2':'$resp[$i]'});";
};


for($i = 0; $i < $maxyear-$minyear; $i++){
						$anio = $minyear + $i;	
						echo "j_array_promedios.push({	'val1':'$anio','val2':'$resppromedios[$i]'});";
};

echo "</script>\n";


//echo "SALIO";
?>

<script type="text/javascript">
    $(document).ready(function () {
        $('.dropdown-toggle').dropdown();
    });
</script>
<script type="text/javascript">
	
      // Load the Visualization API and the piechart package.
google.load('visualization', '1', {'packages':['annotatedtimeline']});    
  google.load('visualization', '1.0', {'packages':['corechart']});
	
      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawChart);
	 var prevButton = document.getElementById('b1');
	    var nextButton = document.getElementById('b2');
	    var changeZoomButton = document.getElementById('b3');
	    
	
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
			console.log(values);	
			for( j = 0; j < values.length; j++){
				//if(j%2 == 0){
					if(values[j] != "undefined"){
						vector.push(parseFloat(values[j]));						
				}else{
					vector.push(values[j]);
			//	}
			}
					
			}
			
			
			//vector.push(0.0);
			j_array_final = vector;
			console.log(vector);
			data9.addRow(vector);
	}
    
	// Create the data table.
	var data = new google.visualization.DataTable();
	
	data.addColumn('string', 'Date');
	for( k = 0; k<j_array_materias.length;k++){
		data.addColumn('number', j_array_materias[k].val1);
	//	data9.addColumn('string', 'title1');
	 //  data9.addColumn('string', 'text1');
	}
	
	for( k = 0; k<j_array_promedios.length;k++){
			//var myvar = <?php echo json_encode($myVarValue); ?>;
			var vector = new Array();
		//	vector.push(new Date(j_array_fecha[k].val1,0,1));
			vector.push(j_array_promedios[k].val1);
			var values = j_array_promedios[k].val2.split(",");
			console.log(values);	
			for( j = 0; j < values.length; j++){
				//if(j%2 == 0){
					if(values[j] != "undefined"){
						vector.push(parseFloat(values[j]));						
				}else{
					vector.push(values[j]);
				}	
			}
			console.log(vector);
			data.addRow(vector);
	}
	
    // Set chart options
    var options = {'width':750,
                       'height':450,
						'backgroundColor.strokeWidth':1,
						'backgroundColor.stroke':'#666',
						'title': 'Grafico de Alumnos y Promedios',
						'pointSize':'2'};
						
	var options2 = { 	'width':750,
					    'height':450,
						'backgroundColor.strokeWidth':1,
						'backgroundColor.stroke':'#666',
						'title': 'Grafico de Alumnos y Promedios',
						'pointSize':'2'};

		var datechart = new google.visualization.LineChart(document.getElementById('fechachart_div'));
		datechart.draw(data9, options);
		
		var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
		chart.draw(data, options2);
	//	var datechart = new google.visualization.AnnotatedTimeLine(document.getElementById('fechachart_div'));
	  //  datechart.draw(data9, {displayAnnotations: true});
	};
	
	prevButton.onclick = function() {
	      options.hAxis.viewWindow.min -= 1;
	      options.hAxis.viewWindow.max -= 1;
	      drawChart();
	    }
	    nextButton.onclick = function() {
	      options.hAxis.viewWindow.min += 1;
	      options.hAxis.viewWindow.max += 1;
	      drawChart();
	    }
	    var zoomed = false;
	    changeZoomButton.onclick = function() {
	      if (zoomed) {
	        options.hAxis.viewWindow.min = 0;
	        options.hAxis.viewWindow.max = 5;
	      } else {
	        options.hAxis.viewWindow.min = 0;
	        options.hAxis.viewWindow.max = MAX;
	      }
	      zoomed = !zoomed;
	      drawChart();
	    }
	 drawChart();
	
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
			  <li class="active"><a href="./alumnos.php">Alumnos</a></li>
			  <li><a href="./materias.php">Materias</a></li>
			</ul>
		</div>
	  </div>
	</div>
	<div class="row" style="margin-left:80px;">
	  <div class="span4">
	  		<h1>Form</h1>
			<form class="well" action="./alumnos.php" method="POST">
			<label>Fechas</label>
			<p><input type="text" name="fromyear" placeholder="Fecha inicio"/></p>
			<p><input type="text" name="toyear" placeholder="Fecha limite"/></p>
			<button type="submit" class="btn">Submit</button>
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
			<h3>Alumnos y promedios en el tiempo</h3>
			<div id='fechachart_div' class='chart' style='width: 750px; height: 450px; float:left;'></div><br/>	 
			<h3>Alumnos y Promedios por a&ntilde;io</h3>
			<div id="chart_div" class='chart' style="width: 750px; height: 450px; float:left;"></div>
			<form>
			  <input id="example5-b1" type="button" value="Previous">
			  <input id="example5-b2" type="button" value="Next">
			  <input id="example5-b3" type="button" value="Change Zoom">
			</form>
	  </div>
	</div>

</body>
</html>