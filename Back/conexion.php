<?php

require_once 'vendor/autoload.php';

use GraphAware\Neo4j\Client\ClientBuilder;

$client = ClientBuilder::create()
    ->addConnection('default', 'http://neo4j:12345@localhost:7474') // Example for HTTP connection configuration (port is optional)
    ->build();

$pathArr = explode("/", $_SERVER['PATH_INFO']);
array_shift($pathArr);
$archivo = $pathArr[0];

if(isset($_REQUEST['action'])){
	if($_REQUEST['action'] != NULL){
		$archivo = $_REQUEST['action'];
	}
}


switch ($archivo) {
	case 'registerProduct':
		registerProduct($client,$pathArr[1],$pathArr[2],$pathArr[3],$pathArr[4]);
		break;
	case 'getProductos':
		getProducts($client);
		break;
	default:
		echo "No action provided";
		break;
}

/*$result = $client->run('MATCH (n:Categoria) RETURN n');

foreach ($result->records() as $record) {
    print_r($record->get('n')->get('name')); // nodes returned are automatically hydrated to Node objects
	echo "<br></br>";
    //echo sprintf('Category name is : %s', $record);
}*/


function registerProduct($client,$categoria,$nombreMateriaPrima,$unidad,$precioUnidad){
	$query = "MATCH (cat:Categoria) WHERE cat.name = '" . $categoria . "' CREATE (new: mprima {name: '" . $nombreMateriaPrima . "', Unidad: '" . $unidad . "', PrecioUnidad: '" . $precioUnidad . "'}), (new)-[:IS]->(cat)";
	
	$result = $client->run($query);
	var_dump($result);
}

function getProducts($client){
	$query = "MATCH (mPrima:mprima)-[:IS]-(cat) RETURN mPrima, cat";
	
	$result = $client->run($query);
	$res = array('success' => true,"records"=> count($result->records()), "root"=> array());
	$root = array();
	foreach ($result->records() as $record) {
		$root[] = array('Nombre' => $record->get('mPrima')->get("name"), "Unidad"=>$record->get('mPrima')->get("Unidad"), "PrecioUnidad" => $record->get('mPrima')->get("PrecioUnidad"), "Categoria" =>$record->get("cat")->get("name"));
	}
	$res["root"] = $root;
	echo json_encode($res);
}


function registerRecipe($client,$titulo,$productos){
	$query = "' CREATE (new: receta {name: '" . $titulo . "', productos: '" . $productos . "'})";
	
	$result = $client->run($query);
	var_dump($result);
}

function getProducts($client){
	$query = "MATCH (recipe:receta) RETURN recipe";
	
	$result = $client->run($query);
	$res = array('success' => true,"records"=> count($result->records()), "root"=> array());
	$root = array();
	foreach ($result->records() as $record) {
		$root[] = array('Receta' => $record->get('recipe')->get("name"), "Productos"=>$record->get('recipe')->get("Productos"), "Precio"=>count($record->get('recipe')->get("Productos")));
	}
	$res["root"] = $root;
	echo json_encode($res);
}

function registerElaborado($client,$titulo,$receta){
	$query = "' CREATE (new: elaborado {name: '" . $titulo . "', receta: '" . $receta . "'})";
	
	$result = $client->run($query);
	var_dump($result);
}

function getElaborados($client){
	$query = "MATCH (elaborados:elaborado) RETURN elaborados";
	
	$result = $client->run($query);
	$res = array('success' => true,"records"=> count($result->records()), "root"=> array());
	$root = array();
	foreach ($result->records() as $record) {
		$root[] = array('Elaborado' => $record->get('elaborados')->get("name"), "Receta"=>$record->get('elaborados')->get("receta"));
	}
	$res["root"] = $root;
	echo json_encode($res);
}

?>