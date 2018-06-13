<?php

include_once("../db_config.php");
require_once "../common/data_access.php";

/**
 * API Slim routes.
 */
require '../lib/slim/Slim.php';
$app = new Slim();

require_once "actions/commons.php";
require_once "actions/syncTripsActions.php";
require_once "actions/TripsQueryActions.php";
require_once "actions/saveItineraryActions.php";
require_once "actions/ItineraryQueryActions.php";


// Sync trips
$app->post('/sync/trips', 'syncTrips');

function syncTrips() {
    $action = new SyncTripsAction();
    $action->execute();
}

// Trip list
$app->get('/trip', 'searchAllTrips');

function searchAllTrips() {
    $action = new SearchAllTrips();
    $action->execute();
}

// Trip list
$app->get('/trip-log', 'searchTripLogs');

function searchTripLogs() {
    $action = new SearchTripLogs();
    $action->execute();
}


// Save Itinerary
$app->post('/itinerary', 'syncItinerary');

function syncItinerary() {
    $action = new SaveItineraryActions();
    $action->execute();
}

// Update Itinerary
$app->put('/itinerary/:id', 'updateItinerary');

function updateItinerary() {
    $action = new UpdateItineraryActions();
    $action->execute();
}


// Itinerary list
$app->get('/itinerary', 'searchItineraries');

function searchItineraries() {
    $action = new SearchAllItineraries();
    $action->execute();
}

// Itinerary list
$app->get('/anchor', 'searchAnchors');

function searchAnchors() {
    $action = new SearchItineraryPath();
    $action->execute();
}




// Client Origins
$app->get('/v2/:country_code/client-origins', 'v2_getClientOrigins');

// Client Origins
$app->get('/v2/:country_code/client-origins', 'v2_getClientOrigins');

// Pathologies
$app->get('/v2/pathologies', 'v2_getPathologies');

// Languages
$app->get('/v2/languages', 'v2_getLanguages');

// Product groups
$app->get('/v2/:country_code/product-groups', 'v2_getProductGroups');

// Product.
$app->get('/v2/:country_code/products', 'v2_getProducts');

// Distribution groups
$app->get('/v2/:country_code/distribution-groups', 'v2_getDistributionGroups');

// Distribution channels
$app->get('/v2/:country_code/distribution-channels', 'v2_getDistributionChannels');

// Clients.
$app->get('/v2/:country_code/clients/:unique_identifier', 'v2_getClients');
$app->get('/v2/:country_code/clients', 'v2_getClients');

// Pending order
$app->post('/v2/:country_code/pending-orders', 'v2_addPendingOrder');

// Sync products by a specific client.
$app->get('/v2/:country_code/sync/products-by-client', 'v2_syncProductsByClient');

// Init sales app using the salesperson email address.
$app->get('/v2/:country_code/sales-app/init', 'v2_initSalesApp');

/**
 * Get all payment options available for a country.
 * TODO: This call does not take into account the country provided and should be using getCountry_preference.
 */
function v2_getPaymentOptions() {
	$action = new PaymentOptionsApiAction();
	$action->execute();
}

/**
 * Get Clients origins configured for a given country
 */
function v2_getClientOrigins() {
	$action = new ClientOriginApiAction();
	$action->execute();
}

/**
 * Get all pathologies.
 */
function v2_getPathologies() {
	$action = new PathologiesApiAction();
	$action->execute();
}

/**
 * Get all languages.
 */
function v2_getLanguages() {
	$action = new LanguagesApiAction();
	$action->execute();
}

/**
 * Get all product groups defined for a country.
 */
function v2_getProductGroups() {
	$action = new ProductGroupsApiAction();
	$action->execute();
}

/**
 * Get all products defined for a country.
 */
function v2_getProducts() {
	$action = new ProductsApiAction();
	$action->execute();
}

/**
 * Get all distribution groups defined for a country.
 */
function v2_getDistributionGroups() {
	$action = new DistributionGroupsAction();
	$action->execute();
}

/**
 * Get all distribution channels defined for a country that have the online_form flag set to true.
 */
function v2_getDistributionChannels() {
	$action = new DistributionChannelAction();
	$action->execute();
}

/**
 * Search for clients.
 */
function v2_getClients() {
	$action = new ClientAction();
	$action->execute();
}

function v2_addPendingOrder() {
	$action = new PendingOrderCreationAction();
	$action->execute();
}

/**
 * Sync call for products bought by clients
 */
function v2_syncProductsByClient() {
	$action = new ProductsByClientSyncApiAction();
	$action->execute();
}

/**
 * Initialize sales application using the salesperson email address.
 */
function v2_initSalesApp() {
	$action = new InitSalesAppAction();
	$action->execute();
}

// Run slim
$app->run();