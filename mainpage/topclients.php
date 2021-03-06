<?php

/*
*
*	This script will calculate the average speed of a specified interval.
*	Preconditions include: Company Name String to use and the inerval on which to graph
*
*	Edit max_data_points to adjust how many x values to graph
*/


include_once('../config.php');

// Create connection
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$max_data_points = 15;

$xaxis = array();
$yaxis = array();


//Perform query and store data in array
$sql = "SELECT CLIENT_IP FROM tbl_data";
$result = $conn->query($sql);

$data = array();

//manipulate data
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
		$data[$row["CLIENT_IP"]] = (int)$data[$row["CLIENT_IP"]]+1;
    }
} else {
    echo "0 results";
}

//sort data by size
arsort($data);

//iterate only through $maxdatapoints times
$obj = new ArrayObject( $data );
$it = $obj->getIterator();
$counter = $max_data_points;
// Iterate over the values in the ArrayObject:
while( $it->valid()){
	if($counter > 0){
		
		$yaxis[] = $it->current();
		$xaxis[] = $it->key();
		$counter--;
	}
	$it->next();
}
$conn->close();

require_once ('../jpgraph/src/jpgraph.php');
require_once ('../jpgraph/src/jpgraph_bar.php');

$data1y=$yaxis;



// Create the graph. These two calls are always required
$graph = new Graph(700,500,'auto');
$graph->SetScale("textlin");

$theme_class=new UniversalTheme;
$graph->SetTheme($theme_class);


$graph->SetBox(false);

$graph->ygrid->SetFill(false);
$graph->xaxis->SetTickLabels($xaxis);

$graph->Set90AndMargin(200,0,0,0);
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false,false);

// Create the bar plots
$b1plot = new BarPlot($data1y);

// Create the grouped bar plot
$gbplot = new GroupBarPlot(array($b1plot));
// ...and add it to the graPH
$graph->Add($gbplot);


$b1plot->SetColor("white");
$b1plot->SetFillColor("#01b054");

$graph->title->Set("Top ".$max_data_points." IP's Performing Speedtests (Overall)");

// Display the graph
$graph->Stroke();

?>

